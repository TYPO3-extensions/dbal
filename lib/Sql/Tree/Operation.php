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
 * An operation tree.
 *
 * @category    Tree
 * @package     SQL
 * @subpackage  Tree
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Sql_Tree_Operation extends Sql_AbstractTree {

	/**
	 * @var integer
	 */
	public $operator;

	/**
	 * @var Sql_AbstractTree
	 */
	public $left;

	/**
	 * @var Sql_AbstractTree
	 */
	public $right;

	/**
	 * Default constructor.
	 *
	 * @param integer $pos
	 * @param integer $operator
	 * @param Sql_AbstractTree $left
	 * @param Sql_AbstractTree $right
	 */
	public function __construct($pos, $operator, /* Sql_AbstractTree */ $left, /* Sql_AbstractTree */ $right) {
		parent::__construct($pos);

		$this->operator = $operator;
		$this->left = $left;
		$this->right = $right;
		$this->depth += max($left != null ? $left->depth : 0, $right != null ? $right->depth : 0);
	}

	/**
	 * Applies the visitor onto this class.
	 *
	 * @param Sql_Interfaces_Visitor $visitor
	 * @return void
	 */
	public function apply(Sql_Interfaces_Visitor $visitor) {
		$visitor->caseOperation($this);
	}

}

?>