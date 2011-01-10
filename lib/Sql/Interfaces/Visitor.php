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
 * This interface provides method for the visitor design pattern.
 *
 * @category    Interfaces
 * @package     SQL
 * @subpackage  Interfaces
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010-2011
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
interface Sql_Interfaces_Visitor {

	function caseBad(Sql_Tree_Bad $tree);
	function caseBooleanPrimary(Sql_Tree_BooleanPrimary $tree);
	function caseCaseExpr(Sql_Tree_CaseExpr $tree);
	function caseCombinedIdentifier(Sql_Tree_CombinedIdentifier $tree);
	function caseFunction(Sql_Tree_Function $tree);
	function caseIdentifier(Sql_Tree_Identifier $tree);
	function caseIntLiteral(Sql_Tree_IntLiteral $tree);
	function caseOperation(Sql_Tree_Operation $tree);
	function caseSelect(Sql_Tree_Select $tree);
	function caseSelectExpr(Sql_Tree_SelectExpr $tree);
	function caseSimpleExpr(Sql_Tree_SimpleExpr $tree);
	function caseStar(Sql_Tree_Star $tree);
	function caseStringLiteral(Sql_Tree_StringLiteral $tree);
	function caseTableFactor(Sql_Tree_TableFactor $tree);

}

?>