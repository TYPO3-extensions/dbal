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

require_once(dirname(__FILE__) . '/Interfaces/Visitor.php');
require_once(dirname(__FILE__) . '/Interfaces/Writer.php');
require_once(dirname(__FILE__) . '/Interfaces/Tokens.php');

/**
 * Abstract SQL query writer.
 *
 * @category	Query Writer
 * @package	    Sql
 * @author	    Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2011
 * @license	    http://www.gnu.org/copyleft/gpl.html
 * @version	    SVN: $Id$
 */
abstract class Sql_AbstractWriter implements Sql_Interfaces_Visitor, Sql_Interfaces_Writer, Sql_Interfaces_Tokens {

	/**
	 * @var string
	 */
	protected $buffer;

	/**
	 * Default constructor.
	 */
	public function __construct() {
		$this->buffer = '';
	}

	/**
	 * @param array $trees
	 * @return string
	 */
	public function rewrite(array $trees) {
		foreach ($trees as $tree) {
			$this->append($tree);
			$this->buffer .= ';';
		}
		return $this->buffer;
	}

	/**
	 * Appends the SQL representation of an object (tree, array of trees, string, ...)
	 * to the internal buffer.
	 *
	 * @param mixed $obj
	 * @return Sql_Interfaces_Visitor
	 */
	protected function append($obj) {
		switch (TRUE) {
			case is_a($obj, Sql_AbstractTree):
				/* Sql_AbstractTree $obj */
				$obj->apply($this);
				break;
			case is_array($obj):
				for ($i = 0; $i < count($obj) - 1; $i++) {
					$this->append($obj[$i]);
					$this->buffer .= ', ';
				}
				$this->append($obj[count($obj) - 1]);
				break;
			default:
				$this->buffer .= (string) $obj;
		}
		return $this;
	}

	/**
	 * @param Sql_Tree_Bad $tree
	 * @return void
	 * @throws InvalidArgumentException
	 */
	public function caseBad(Sql_Tree_Bad $tree) {
		throw new InvalidArgumentException('Invalid SQL query', 1295941914);
	}

	public function caseBooleanPrimary(Sql_Tree_BooleanPrimary $tree) {
		// TODO: Implement caseBooleanPrimary() method.
	}

	/**
	 * @param Sql_Tree_CaseExpr $tree
	 * @return void
	 */
	public function caseCaseExpr(Sql_Tree_CaseExpr $tree) {
		$this->append(' CASE ');

		for ($i = 0; $i < count($tree->compareValues); $i++) {
			$this->append(' WHEN ')->append($tree->compareValues[$i]);
			$this->append(' THEN ')->append($tree->results[$i]);
		}
		if ($tree->else) {
			$this->append(' ELSE ')->append($tree->else);
		}

		$this->append(' END');
	}

	/**
	 * @param Sql_Tree_CombinedIdentifier $tree
	 * @return void
	 */
	public function caseCombinedIdentifier(Sql_Tree_CombinedIdentifier $tree) {
		$this->append($tree->left)->append('.')->append($tree->right);
	}

	public function caseFunction(Sql_Tree_Function $tree) {
		// TODO: Implement caseFunction() method.
	}

	/**
	 * @param Sql_Tree_IntLiteral $tree
	 * @return void
	 */
	public function caseIntLiteral(Sql_Tree_IntLiteral $tree) {
		$this->append($tree->value);
	}

	/**
	 * @param Sql_Tree_Select $tree
	 * @return void
	 */
	public function caseSelect(Sql_Tree_Select $tree) {
		$this->append('SELECT ')->append($tree->selectExpr);
		$this->append(' FROM ')->append($tree->tableReferences);

		if ($tree->whereCondition) {
			$this->append(' WHERE ')->append($tree->whereCondition);
		}
	}

	public function caseSimpleExpr(Sql_Tree_SimpleExpr $tree) {
		// TODO: Implement caseSimpleExpr() method.
	}

	/**
	 * @param Sql_Tree_Star $tree
	 * @return void
	 */
	public function caseStar(Sql_Tree_Star $tree) {
		$this->append('*');
	}

	/**
	 * @param Sql_Tree_StringLiteral $tree
	 * @return void
	 */
	public function caseStringLiteral(Sql_Tree_StringLiteral $tree) {
		$this->append($tree->value);
	}

}

?>