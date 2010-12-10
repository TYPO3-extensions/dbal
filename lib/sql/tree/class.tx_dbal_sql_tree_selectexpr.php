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
 * An select_expr tree.
 *
 * @category    Tree
 * @package     TYPO3
 * @subpackage  tx_dbal\sql\tree
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class tx_dbal_sql_tree_SelectExpr extends tx_dbal_sql_AbstractTree {

	/**
	 * @var tx_dbal_sql_tree_Identifier
	 */
	public $table;

	/**
	 * @var tx_dbal_sql_tree_Identifier|tx_dbal_sql_tree_Star
	 */
	public $field;

	/**
	 * @var tx_dbal_sql_tree_Identifier
	 */
	public $alias;

	/**
	 * Default constructor.
	 *
	 * @param integer $pos
	 * @param tx_dbal_sql_tree_Identifier $table
	 * @param tx_dbal_sql_tree_Identifier|tx_dbal_sql_tree_Star $field
	 * @param tx_dbal_sql_tree_Identifier $alias
	 */
	public function __construct($pos, /* tx_dbal_sql_tree_Identifier */ $table, $field, /* tx_dbal_sql_tree_Identifier */ $alias = null) {
		parent::__construct($pos);

		$this->table = $table;
		$this->field = $field;
		$this->alias = $alias;
	}

	/**
	 * Applies the visitor onto this class.
	 *
	 * @param VisitorInterface $visitor
	 * @return void
	 */
	public function apply(VisitorInterface $visitor) {
		$visitor->caseSelectExpr($this);
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/tree/class.tx_dbal_sql_tree_selectexpr.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/sql/tree/class.tx_dbal_sql_tree_selectexpr.php']);
}

?>