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
 * A boolean_primary tree.
 *
 * @category    Tree
 * @package     SQL
 * @subpackage  Tree
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Sql_Tree_BooleanPrimary extends Sql_Tree_AbstractExpr {

	/**
	 * @var integer
	 */
	public $comparisonOperator;

	/**
	 * Default constructor.
	 *
	 * @param integer $pos
	 * @param Sql_AbstractTree $left
	 * @param integer $comparisonOperator
	 * @param Sql_AbstractTree $right
	 *
	 */
	public function __construct($pos, Sql_AbstractTree $left, $comparisonOperator, Sql_AbstractTree $right) {
		parent::__construct($pos, $left, $right);

		$this->comparisonOperator = $comparisonOperator;
	}

	/**
	 * Applies the visitor onto this class.
	 *
	 * @param Sql_Interfaces_Visitor $visitor
	 * @return void
	 */
	public function apply(Sql_Interfaces_Visitor $visitor) {
		$visitor->caseBooleanPrimary($this);
	}

}

?>