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
			throw t3lib_div::makeInstance('tx_dbal_sql_error_TokenExpected', $this->tokenClass($expected));
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
	private function acceptIf($expected) {
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
	private function accept($expected) {
		if (!$this->acceptIf($expected)) {
			t3lib_div::debug($this->representation(), 'found');
			$this->error($expected);
		}
	}

	/**
	 * Returns the syntactic tree built from input stream.
	 *
	 * @return tx_dbal_sql_Tree
	 */
	public function parse() {
		return $this->parseSql();
	}

	/**
	 * Returns the syntactic tree built from SQL input stream.
	 *
	 * @return tx_dbal_sql_Tree
	 */
	private function parseSql() {
		switch ($this->token) {
			case self::T_SELECT:
				$this->parseSelect();
				break;
			case self::T_UPDATE:
				$this->parseUpdate();
				break;
			case self::T_INSERT:
				$this->parseInsert();
				break;
			case self::T_DELETE:
				$this->parseDelete();
				break;
			case self::T_EXPLAIN:
				$this->parseExplain();
				break;
			case self::T_DROP:
				$this->parseDropTable();
				break;
			case self::T_ALTER:
				$this->parseAlterTable();
				break;
			case self::T_CREATE:
				$this->accept(self::T_CREATE);
				if ($this->token == self::T_TABLE) {
					$this->parseCreateTable();
				} else {
					$this->parseCreateDatabase();
				}
				break;
			case self::T_TRUNCATE:
				$this->parseTruncate();
				break;
			default:
				$this->error('Cannot parse SQL');
		}
		$this->accept(self::EOF);
		return null;
	}

	/**
	 * Parses a SELECT statement.
	 *
	 * "SELECT"
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
	 * @return tx_dbal_sql_Tree
	 * @see http://dev.mysql.com/doc/refman/5.5/en/select.html
	 */
	private function parseSelect() {
		$this->accept(self::T_SELECT);
		do {
			if ($this->token == self::T_IDENT || $this->token == self::T_STAR) {
				$this->accept($this->token);
			}
		} while ($this->acceptIf(self::T_COMMA));
		$this->accept(self::T_FROM);
		$this->accept(self::T_IDENT);
		$this->accept(self::T_WHERE);
	}
}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/class.tx_dbal_sql_parser.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/class.tx_dbal_sql_parser.php']);
}

?>