<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010-2011 Xavier Perseguers <typo3@perseguers.ch>
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
 * A SELECT tree.
 *
 * @category    Tree
 * @package     SQL
 * @subpackage  Tree
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010-2011
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Sql_Tree_Select extends Sql_AbstractTree {

	/**
	 * @var Sql_Tree_SelectExpr[]
	 */
	public $selectExpr;

	/**
	 * @var Sql_Tree_TableReference[]
	 */
	public $tableReferences;

	/**
	 * @var Sql_Tree_Expr
	 */
	public $whereCondition;

	/**
	 * Default constructor.
	 *
	 * @param integer $pos
	 * @param Sql_Tree_SelectExpr[] $fields
	 * @param Sql_Tree_TableReference[] $tableReferences
	 * @param Sql_Tree_Expr $whereCondition
	 */
	public function __construct($pos, array $selectExpr, array $tableReferences, /* Sql_Tree_Expr */ $whereCondition) {
		parent::__construct($pos);

		$this->selectExpr = $selectExpr;
		$this->tableReferences = $tableReferences;
		$this->whereCondition = $whereCondition;
		//$this->depth += max($left != null ? $left->depth : 0, $right != null ? $right->depth : 0);
	}

	/**
	 * Applies the visitor onto this class.
	 *
	 * @param Sql_Interfaces_Visitor $visitor
	 * @return void
	 */
	public function apply(Sql_Interfaces_Visitor $visitor) {
		$visitor->caseSelect($this);
	}

}

?>