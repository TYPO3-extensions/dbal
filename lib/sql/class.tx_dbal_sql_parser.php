<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Xavier Perseguers <typo3@perseguers.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Parser ("syntactic analyzer") of the SQL language.
 *
 * The whole parser is based on compilation course (LAMP) I attended at
 * Swiss Federal Institute of Technology. Nice to use that again ;-)
 * @link http://lamp.epfl.ch/teaching/archive/compilation/2002/project/assignments/1/instructions_header_web.shtml
 *
 * @category    Parser
 * @package     TYPO3
 * @subpackage  tx_dbal\sql
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class tx_dbal_sql_Parser extends tx_dbal_sql_Scanner {

	/**
	 * Throws an error.
	 *
	 * @param integer|string $expected
	 * @return void
	 * @throws t3lib_Exception
	 */
	protected function error($expected) {
		if (is_integer($expected)) {
			throw new tx_dbal_sql_error_TokenExpected($this->tokenClass($expected));
		} else {
			$message = 'Invalid syntax. Expected: ' . $expected . ', found: lexeme ' . $this->representation();
			throw new Exception($message);
		}
	}

	/**
	 * If current lexeme's class is as expected, gets next token
	 * and returns TRUE, otherwise returns FALSE.
	 *
	 * @param integer $expected
	 * @return boolean
	 */
	protected function acceptIf($expected) {
		if ($this->token == $expected) {
			$this->nextToken();
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * If current lexeme's class is as expected, gets next token.
	 *
	 * @param integer $expected
	 * @return void
	 * @throws tx_dbal_sql_error_TokenExpected
	 */
	protected function accept($expected) {
		if (!$this->acceptIf($expected)) {
			t3lib_div::debug($this->representation(), 'found');
			$this->error($expected);
		}
	}

	/**
	 * Returns the syntactic tree built from input stream.
	 *
	 * @return tx_dbal_sql_AbstractTree
	 */
	public function parse() {
		return $this->parseSqlScript();
	}

	/**
	 * Returns the syntactic tree built from SQL input stream.
	 *
	 * @return tx_dbal_sql_AbstractTree[]
	 */
	protected function parseSqlScript() {
		$sqlScript = array();
		switch ($this->token) {
			case self::T_SELECT:
				$sqlScript[] = $this->parseSelect();
				break;
			case self::T_UPDATE:
				$sqlScript[] = $this->parseUpdate();
				break;
			case self::T_INSERT:
				$sqlScript[] = $this->parseInsert();
				break;
			case self::T_DELETE:
				$sqlScript[] = $this->parseDelete();
				break;
			case self::T_EXPLAIN:
				$sqlScript[] = $this->parseExplain();
				break;
			case self::T_DROP:
				$sqlScript[] = $this->parseDropTable();
				break;
			case self::T_ALTER:
				$sqlScript[] = $this->parseAlterTable();
				break;
			case self::T_CREATE:
				$this->accept(self::T_CREATE);
				if ($this->token == self::T_TABLE) {
					$sqlScript[] = $this->parseCreateTable();
				} else {
					$sqlScript[] = $this->parseCreateDatabase();
				}
				break;
			case self::T_TRUNCATE:
				$sqlScript[] = $this->parseTruncate();
				break;
			default:
				$this->error('Cannot parse SQL');
		}
		//$this->accept(self::EOF);
		return $sqlScript;
	}

	/**
	 * Parses a SELECT statement.
	 *
	 * SELECT := "SELECT"
	 * 		["ALL" | "DISTINCT"]
	 * 		select_expr ["," select_expr ...]
	 * 		"FROM" table_references
	 * 		["WHERE" where_condition]
	 * 		["GROUP" "BY" {col_name | expr | position} ["ASC" | "DESC"], ...]
	 * 		["HAVING" where_condition]
	 * 		["ORDER" "BY" {col_name | expr | position} ["ASC" | "DESC"], ...]
	 * 		["LIMIT" [offset,] row_count]
	 *
	 *
	 * @return tx_dbal_sql_tree_Select
	 * @link http://dev.mysql.com/doc/refman/5.5/en/select.html
	 */
	protected function parseSelect() {
		$selectExpressions = array();
		$tableReferences = array();
		$whereCondition = null;

		// "SELECT"
		$this->accept(self::T_SELECT);

		// Fields to be selected
		do {
			$selectExpressions[] = $this->parseSelectExpr();
		} while ($this->acceptIf(self::T_COMMA));

		// "FROM"
		$this->accept(self::T_FROM);

		// Tables
		do {
			$tableReferences[] = $this->parseTableReference();
		} while ($this->acceptIf(self::T_COMMA));

		// "WHERE"
		if ($this->token == self::T_WHERE) {
			$this->accept(self::T_WHERE);
			$whereCondition = $this->parseWhereCondition();
		}

		// "GROUP" "BY"
		if ($this->token == self::T_GROUP) {
			$this->accept(self::T_GROUP);
			$this->accept(self::T_BY);
		}

		// "HAVING"
		if ($this->token == self::T_GROUP) {
			$this->accept(self::T_HAVING);
		}

		// "ORDER" "BY"
		if ($this->token == self::T_ORDER) {
			$this->accept(self::T_ORDER);
			$this->accept(self::T_BY);
		}

		// "LIMIT"
		if ($this->token == self::T_LIMIT) {
			$this->accept(self::T_LIMIT);
		}

		return new tx_dbal_sql_tree_Select($this->start, $selectExpressions, $tableReferences, $whereCondition);
	}

	/**
	 * Parses a select_expr.
	 *
	 * select_expr := [identifier "."] {identifier ["AS"] [identifier] | "*"}
	 *
	 * @return tx_dbal_sql_tree_SelectExpr
	 * @link http://dev.mysql.com/doc/refman/5.5/en/select.html
	 */
	protected function parseSelectExpr() {
		$table = null;
		if ($this->token == self::T_IDENTIFIER) {
			$name = $this->chars;
			$this->accept(self::T_IDENTIFIER);
			$tableOrField = new tx_dbal_sql_tree_Identifier($this->start, $name);

			if ($this->token == self::T_DOT) {
				$table = $tableOrField;
				$this->accept(self::T_DOT);

				if ($this->token == self::T_IDENTIFIER) {
					$name = $this->chars;
					$this->accept(self::T_IDENTIFIER);
					$field = new tx_dbal_sql_tree_Identifier($this->start, $name);
				} else {
					$this->accept(self::T_STAR);
					$field = new tx_dbal_sql_tree_Star($this->start);
				}
			} else {
				$field = $tableOrField;
			}
		} else {
			$this->accept(self::T_STAR);
			$field = new tx_dbal_sql_tree_Star($this->start);
		}

		$alias = null;
		if ($this->token == self::T_AS || $this->token == self::T_IDENTIFIER) {
			$this->acceptIf(self::T_AS);
			$name = $this->chars;
			$this->accept(self::T_IDENTIFIER);
			$alias = new tx_dbal_sql_tree_Identifier($this->start, $name);
		}

		return new tx_dbal_sql_tree_SelectExpr($this->start, $table, $field, $alias);
	}

	/**
	 * Parses a table_reference.
	 *
	 * table_reference := table_factor
	 *                    | join_table
	 *
	 * @return tx_dbal_sql_tree_TableReference
	 * @link http://dev.mysql.com/doc/refman/5.5/en/join.html
	 */
	protected function parseTableReference() {
		return $this->parseTableFactor();
	}

	/**
	 * Parses a table_factor.
	 *
	 * table_factor := identifier [["AS"] identifier]
	 *
	 * @return tx_dbal_sql_tree_TableFactor
	 * @link http://dev.mysql.com/doc/refman/5.5/en/join.html
	 */
	protected function parseTableFactor() {
		$name = $this->chars;
		$this->accept(self::T_IDENTIFIER);
		$tableName = new tx_dbal_sql_tree_Identifier($this->start, $name);
		$alias = null;

		if ($this->token == self::T_AS || $this->token == self::T_IDENTIFIER) {
			$this->acceptIf(self::T_AS);
			$name = $this->chars;
			$this->accept(self::T_IDENTIFIER);
			$alias = new tx_dbal_sql_tree_Identifier($this->start, $name);
		}

		return new tx_dbal_sql_tree_TableFactor($this->start, $tableName, $alias);
	}

	/**
	 * Parses a where_condition.
	 *
	 * where_condition := expr
	 *
	 * @return tx_dbal_sql_tree_Expr
	 * @link http://dev.mysql.com/doc/refman/5.5/en/expressions.html
	 */
	protected function parseWhereCondition() {
		return $this->parseExpr();
	}

	/**
	 * Parses an expr.
	 *
	 * expr :=
	 *         expr "OR" expr
	 *         | expr "||" expr
	 *         | expr "XOR" expr
	 *         | expr "AND" expr
	 *         | expr "&&" expr
	 *         | "NOT" expr
	 *         | "!" expr
	 *         | boolean_primary "IS" ["NOT"] {TRUE | FALSE | UNKNOWN}
	 *         | boolean_primary
	 *
	 * @return tx_dbal_sql_tree_AbstractExpr
	 * @link http://dev.mysql.com/doc/refman/5.5/en/expressions.html
	 */
	protected function parseExpr() {
		if ($this->token == self::T_NOT || $this->token == self::T_LOGICNOT) {
			$this->accept($this->token);
			return new tx_dbal_sql_tree_SimpleExpr($this->start, self::T_NOT, $this->parseExpr());
		} else {
			$expr = $this->parseBooleanPrimary();
			/*
			if ($this->token == self::T_IS) {
				$this->accept(self::T_IS);
				$this->acceptIf(self::T_NOT);
				$this->parseExpr();
			}
			*/
			switch ($this->token) {
				case self::T_OR:        // 'OR'
				case self::T_LOGICOR:   // '||'
				case self::T_AND:       // 'AND'
				case self::T_LOGICAND:  // '&&'
				case self::T_XOR:       // 'XOR'
					$operator = $this->token;
					$this->accept($this->token);
					return new tx_dbal_sql_tree_Operation($this->start, $operator, $expr, $this->parseExpr());
			}

			return $expr;
		}
	}

	/**
	 * Parses a boolean_primary.
	 *
	 * boolean_primary :=
	 *                    boolean_primary "IS" ["NOT"] "NULL"
	 *                    | boolean_primary comparison_operator predicate
	 *                    | boolean_primary comparison_operator {"ALL" | "ANY"} (subquery)
	 *                    | predicate
	 *
	 *
	 * comparison_operator := "=" | ">=" | ">" | "<=" | "<" | "<>" | "!="
	 *
	 * @return tx_dbal_sql_AbstractTree
	 * @link http://dev.mysql.com/doc/refman/5.5/en/expressions.html
	 */
	protected function parseBooleanPrimary() {
		$predicate = $this->parsePredicate();
		$comparisonOperators = array(
			self::T_EQUAL,
			self::T_GREATEREQUAL,
			self::T_GREATER,
			self::T_LESSEQUAL,
			self::T_LESS,
			self::T_BOX,
			self::T_NOTEQUAL,
		);
		if (in_array($this->token, $comparisonOperators)) {
			$comparisonOperator = $this->token;
			$this->accept($comparisonOperator);
			return new tx_dbal_sql_tree_BooleanPrimary(
				$this->start,
				$predicate,
				$comparisonOperator,
				$this->parsePredicate()
			);
		}

		return $predicate;
	}

	/**
	 * Parses a predicate.
	 *
	 * predicate :=
	 *              bit_expr ["NOT"] "IN" "(" subquery ")"
	 *              | bit_expr ["NOT"] "IN" "(" expr ["," expr] ... ")"
	 *              | bit_expr ["NOT"] "BETWEEN" bit_expr "AND" bit_expr
	 *              | bit_expr ["NOT"] "LIKE" simple_expr
	 *              | bit_expr
	 *
	 * @return tx_dbal_sql_AbstractTree
	 * @link http://dev.mysql.com/doc/refman/5.5/en/expressions.html
	 */
	protected function parsePredicate() {
		$bitExpr = $this->parseBitExpr();

		// TODO
		return $bitExpr;
	}

	/**
	 * Parses a bit_expr.
	 *
	 * bit_expr :=
	 *              bit_expr "|" bit_expr
	 *              | bit_expr "&" bit_expr
	 *              | bit_expr "+" bit_expr
	 *              | bit_expr "-" bit_expr
	 *              | bit_expr "*" bit_expr
	 *              | bit_expr "/" bit_expr
	 *              | bit_expr "DIV" bit_expr
	 *              | bit_expr "MOD" bit_expr
	 *              | bit_expr "%" bit_expr
	 *              | bit_expr "^" bit_expr
	 *              | simple_expr
	 *
	 * @return tx_dbal_sql_AbstractTree
	 * @link http://dev.mysql.com/doc/refman/5.5/en/expressions.html
	 */
	protected function parseBitExpr() {
		$simpleExpr = $this->parseSimpleExpr();
		$operators = array(
			self::T_BITOR,
			self::T_BITAND,
			self::T_PLUS,
			self::T_MINUS,
			self::T_STAR,
			self::T_DIVIDE,
			self::T_DIV,
			self::T_MOD,
			self::T_MODULO,
			self::T_POW,
		);
		while (in_array($this->token, $operators)) {
			$operator = $this->token;
			// TODO: take priority of operators into account
			$simpleExpr = new tx_dbal_sql_tree_Operation($operator, $simpleExpr, $this->parseBitExpr());
		}

		return $simpleExpr;
	}

	/**
	 * Parses a simple_expr.
	 *
	 * simple_expr :=
	 *                literal
	 *                | identifier
	 *                | function_call
	 *                | "+" simple_expr
	 *                | "-" simple_expr
	 *                | "~" simple_expr
	 *                | "!" simple_expr
	 *                | "BINARY" simple_expr
	 *                | "(" expr ")"
	 *                | "(" subquery ")"
	 *                | "EXISTS" "(" subquery ")"
	 *                | case_expr
	 *
	 * @return tx_dbal_sql_AbstractTree
	 * @link http://dev.mysql.com/doc/refman/5.5/en/expressions.html
	 */
	protected function parseSimpleExpr() {
		switch ($this->token) {
			case self::T_STRING:
				$value = $this->chars;
				$this->accept(self::T_STRING);
				return new tx_dbal_sql_tree_StringLiteral($this->start, $value);
			case self::T_NUMBER:
				$value = $this->chars;
				$this->accept(self::T_NUMBER);
				return new tx_dbal_sql_tree_IntLiteral($this->start, $value);
			case self::T_IDENTIFIER:
				$name = $this->chars;
				$this->accept(self::T_IDENTIFIER);
				$tableOrField = new tx_dbal_sql_tree_Identifier($this->start, $name);
				$table = null;

				if ($this->token == self::T_DOT) {
					$table = $tableOrField;
					$this->accept(self::T_DOT);
					$name = $this->chars;
					$this->accept(self::T_IDENTIFIER);
					$field = new tx_dbal_sql_tree_Identifier($this->start, $name);
				}
				return $table ? new tx_dbal_sql_tree_CombinedIdentifier($this->start, $table, $field) : $tableOrField;
			case self::T_PLUS:
			case self::T_MINUS:
			case self::T_TILDE:
			case self::T_LOGICNOT:
			case self::T_BINARY:
				$unaryOperator = $this->token;
				return new tx_dbal_sql_tree_SimpleExpr($this->start, $unaryOperator, $this->parseSimpleExpr());
			case self::T_LPAREN:
				$subquery = null;
				$expr = null;
				$this->accept(self::T_LPAREN);
				if ($this->token == self::T_SELECT) {
					$subquery = $this->parseSubquery();
				} else {
					$expr = $this->parseExpr();
				}
				$this->accept(self::T_RPAREN);
				return new tx_dbal_sql_tree_SimpleExpr($this->start, '', $expr, $subquery);
			case self::T_EXISTS:
				$this->accept(self::T_EXISTS);
				$subquery = $this->parseSubquery();
				$this->accept(self::T_RPAREN);
				return new tx_dbal_sql_tree_SimpleExpr($this->start, 'EXISTS', null, $subquery);
			case self::T_CASE:
				// TODO
				break;
			// TODO: function call
		}
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/class.tx_dbal_sql_parser.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/class.tx_dbal_sql_parser.php']);
}

?>