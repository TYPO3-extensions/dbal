<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Xavier Perseguers <typo3@perseguers.ch>
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

require_once(dirname(__FILE__) . '/../../Sql/AbstractWriter.php');

/**
 * SQL query writer for Oracle database server.
 *
 * @category	Query Writer
 * @subcategory Oracle
 * @package	    Sql
 * @author	    Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2011
 * @license	    http://www.gnu.org/copyleft/gpl.html
 * @version	    SVN: $Id$
 */
class Drivers_Oracle_Writer extends Sql_AbstractWriter {

	/**
	 * @param Sql_Tree_Identifier $tree
	 * @return void
	 */
	public function caseIdentifier(Sql_Tree_Identifier $tree) {
		$this->append('"')->append($tree->name)->append('"');
	}

	/**
	 * @param Sql_Tree_Operation $tree
	 * @return void
	 */
	public function caseOperation(Sql_Tree_Operation $tree) {
		switch ($tree->operator) {
			case 'LIKE':
				$this->append('dbms_lob.instr(LOWER(');
				$this->append($tree->left);
				$this->append('), ');

				if (is_a($tree->right, Sql_Tree_StringLiteral)) {
					// TODO: beware with character sets
					$this->append(strtolower($tree->right->value));
				} else {
					$this->append('LOWER(')->append($tree->right)->append(')');
				}

				$this->append(',1,1) > 0');
				break;

			default:
				$this->append($tree->left);
				$this->append($tree->operator);
				$this->append($tree->right);
		}
	}

	/**
	 * @param Sql_Tree_SelectExpr $tree
	 * @return void
	 */
	public function caseSelectExpr(Sql_Tree_SelectExpr $tree) {
		if ($tree->table) {
			$this->append($tree->table)->append('.');
		}
		$this->append($tree->field);
		if ($tree->alias) {
			$this->append(' AS ')->append($tree->alias);
		}
	}

	/**
	 * @param Sql_Tree_TableFactor $tree
	 * @return void
	 */
	public function caseTableFactor(Sql_Tree_TableFactor $tree) {
		$this->append($tree->tableName);
		if ($tree->alias) {
			$this->append(' AS ')->append($tree->alias);
		}
	}

}

?>