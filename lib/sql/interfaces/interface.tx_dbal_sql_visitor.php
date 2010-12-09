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
 * This interface provides method for the visitor design pattern.
 *
 * @category    Interfaces
 * @package     TYPO3
 * @subpackage  tx_dbal\sql
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
interface tx_dbal_sql_Visitor {

	public function caseBad(tx_dbal_sql_tree_Bad $tree);
	public function caseBooleanPrimary(tx_dbal_sql_tree_BooleanPrimary $tree);
	public function caseCombinedIdentifier(tx_dbal_sql_tree_CombinedIdentifier $tree);
	public function caseIdentifier(tx_dbal_sql_tree_Identifier $tree);
	public function caseIntLiteral(tx_dbal_sql_tree_IntLiteral $tree);
	public function caseOperation(tx_dbal_sql_tree_Operation $tree);
	public function caseSelect(tx_dbal_sql_tree_Select $tree);
	public function caseSelectExpr(tx_dbal_sql_tree_SelectExpr $tree);
	public function caseSimpleExpr(tx_dbal_sql_tree_SimpleExpr $tree);
	public function caseStar(tx_dbal_sql_tree_Star $tree);
	public function caseStringLiteral(tx_dbal_sql_tree_StringLiteral $tree);
	public function caseTableFactor(tx_dbal_sql_tree_TableFactor $tree);

}

?>