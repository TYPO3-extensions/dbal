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
 * @see http://lamp.epfl.ch/teaching/archive/compilation/2002/project/assignments/1/instructions_header_web.shtml
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
			$exception = t3lib_div::makeInstance('tx_dbal_sql_error_TokenExpected', $this->tokenClass($expected));
			/**
			 * @var tx_dbal_sql_error_TokenExpected $exception
			 */
			throw $exception;
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
	 * @see http://dev.mysql.com/doc/refman/5.5/en/select.html
	 */
	protected function parseSelect() {
		$selectExpressions = array();
		$tableReferences = array();

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

		return t3lib_div::makeInstance('tx_dbal_sql_tree_Select', $this->start, $selectExpressions, $tableReferences, null);
	}

	/**
	 * Parses a select_expr.
	 *
	 * select_expr := [identifier "."] {identifier ["AS"] [identifier] | "*"}
	 *
	 * @return tx_dbal_sql_tree_SelectExpr
	 * @see http://dev.mysql.com/doc/refman/5.5/en/select.html
	 */
	protected function parseSelectExpr() {
		$table = null;
		if ($this->token == self::T_IDENTIFIER) {
			$name = $this->chars;
			$this->accept(self::T_IDENTIFIER);
			$tableOrField = t3lib_div::makeInstance('tx_dbal_sql_tree_Identifier', $this->start, $name);

			if ($this->token == self::T_DOT) {
				$table = $tableOrField;
				$this->accept(self::T_DOT);

				if ($this->token == self::T_IDENTIFIER) {
					$name = $this->chars;
					$this->accept(self::T_IDENTIFIER);
					$field = t3lib_div::makeInstance('tx_dbal_sql_tree_Identifier', $this->start, $name);
				} else {
					$this->accept(self::T_STAR);
					$field = t3lib_div::makeInstance('tx_dbal_sql_tree_Star', $this->start);
				}
			} else {
				$field = $tableOrField;
			}
		} else {
			$this->accept(self::T_STAR);
			$field = t3lib_div::makeInstance('tx_dbal_sql_tree_Star', $this->start);
		}

		$alias = null;
		if ($this->token == self::T_AS || $this->token == self::T_IDENTIFIER) {
			$this->acceptIf(self::T_AS);
			$name = $this->chars;
			$this->accept(self::T_IDENTIFIER);
			$alias = t3lib_div::makeInstance('tx_dbal_sql_tree_Identifier', $this->start, $name);
		}

		return t3lib_div::makeInstance('tx_dbal_sql_tree_SelectExpr', $this->start, $table, $field, $alias);
	}

	/**
	 * Parses a table_reference.
	 *
	 * table_reference := table_factor | join_table
	 *
	 * @return tx_dbal_sql_tree_TableReference
	 * @see http://dev.mysql.com/doc/refman/5.5/en/join.html
	 */
	protected function parseTableReference() {
		return null;
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/class.tx_dbal_sql_parser.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/class.tx_dbal_sql_parser.php']);
}

?>