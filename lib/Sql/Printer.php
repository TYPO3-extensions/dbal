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

require_once(dirname(__FILE__) . '/Interfaces/Visitor.php');

/**
 * Implementation of the Visitor design pattern.
 *
 * The whole parser is based on compilation course (LAMP) I attended at
 * Swiss Federal Institute of Technology. Nice to use that again ;-)
 * @see http://lamp.epfl.ch/teaching/archive/compilation/2002/project/assignments/1/instructions_header_web.shtml
 *
 * @category	Parser
 * @package	    Sql
 * @author	    Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license	    http://www.gnu.org/copyleft/gpl.html
 * @version	    SVN: $Id$
 */
class Sql_Printer implements Sql_Interfaces_Visitor {

	/**
	 * @var string
	 */
	private $buffer;

	/**
	 * Indent step
	 * @var string
	 */
	private $step;

	/**
	 * Indent level
	 * @var integer
	 */
	private $level;

	/**
	 * Default constructor
	 */
	public function __construct() {
		$this->buffer = '';
		$this->step = '&nbsp;&nbsp;&nbsp;&nbsp;';
		$this->level = 0;
	}

	/**
	 * Outputs an object (tree, array of trees, string, ...).
	 *
	 * @param mixed $obj
	 * @return VisitorInterface
	 */
	public function output($obj) {
		switch (TRUE) {
			case is_a($obj, Sql_AbstractTree):
				$obj->apply($this);
				break;
			case is_array($obj):
				for ($i = 0; $i < count($obj) - 1; $i++) {
					$this->output($obj[$i])->output(', ');
				}
				$this->output($obj[count($obj) - 1]);
				break;
			default:
				$this->buffer .= (string) $obj;
		}
		return $this;
	}

	/**
	 * Outputs an array of SQL statements.
	 *
	 * @param Sql_AbstractTree[] $trees
	 * @return VisitorInterface
	 */
	public function outputStatements(array $trees) {
		foreach ($trees as $tree) {
			$this->output($tree)->output(';');
		}
		return $this;
	}

	/**
	 * Returns the internal buffer and flushes it.
	 *
	 * @return string
	 */
	public function flush() {
		$content = $this->buffer;
		$this->buffer = '';
		return $content;
	}

	/**
	 * Outputs a new line and indents the next one.
	 *
	 * @return VisitorInterface
	 */
	public function outputNewLine() {
		$this->buffer .= "<br />\n";
		for ($i = 0; $i < $this->level; $i++) { // str_pad does not work as expected
			$this->buffer .= $this->step;
		}

		return $this;
	}

	/**
	 * Increments the indent level.
	 *
	 * @return VisitorInterface
	 */
	public function indent() {
		$this->level++;
		return $this;
	}

	/**
	 * Decrements the indent level.
	 *
	 * @return VisitorInterface
	 */
	public function unindent() {
		$this->level--;
		return $this;
	}

	/************************************
	 *
	 * Implementation of the Visitor methods
	 *
	 ************************************/

	/**
	 * @param Sql_Tree_Bad $tree
	 * @return void
	 */
	public function caseBad(Sql_Tree_Bad $tree) {
		$this->output('<<bad>>');
	}

	/**
	 * @param Sql_Tree_BooleanPrimary $tree
	 * @return void
	 */
	public function caseBooleanPrimary(Sql_Tree_BooleanPrimary $tree) {
		$this->output($tree->left);
		$this->output(' comparisonOperator(')->output($tree->comparisonOperator)->output(') ');
		$this->output($tree->right);
	}

	/**
	 * @param Sql_Tree_CaseExpr $tree
	 * @return void
	 */
	public function caseCaseExpr(Sql_Tree_CaseExpr $tree) {
		$this->output('CASE ');
		$this->output($tree->left)->indent();
		for ($i = 0; $i < count($tree->compareValues); $i++) {
			$this->outputNewLine();
			$this->output('WHEN ')->output($tree->compareValues[$i]);
			$this->output(' THEN ')->output($tree->results[$i]);
		}
		if ($tree->else) {
			$this->outputNewLine();
			$this->output('ELSE ')->output($tree->else);
		}
		$this->unindent()->outputNewLine();
		$this->output('END');
	}

	/**
	 * @param Sql_Tree_CombinedIdentifier $tree
	 * @return void
	 */
	public function caseCombinedIdentifier(Sql_Tree_CombinedIdentifier $tree) {
		$this->output($tree->left)->output('.')->output($tree->right);
	}

	/**
	 * @param Sql_Tree_Function $tree
	 * @return void
	 */
	public function caseFunction(Sql_Tree_Function $tree) {
		$this->output($tree->functionName);
		$this->output('(')->indent()->outputNewLine();
		$this->output($tree->arguments);
		$this->unindent()->outputNewLine();
		$this->output(')');
	}

	/**
	 * @param Sql_Tree_Identifier $obj
	 * @return void
	 */
	public function caseIdentifier(Sql_Tree_Identifier $tree) {
		$this->output($tree->name);
	}

	/**
	 * @param Sql_Tree_IntLiteral $obj
	 * @return void
	 */
	public function caseIntLiteral(Sql_Tree_IntLiteral $tree) {
		$this->output($tree->value);
	}

	/**
	 * @param Sql_Tree_Operation $obj
	 * @return void
	 */
	public function caseOperation(Sql_Tree_Operation $tree) {
		$this->output('(')->indent()->outputNewLine();
		$this->output($tree->left);
		$this->unindent()->outputNewLine();
		$this->output('operator(')->output($tree->operator)->output(')');
		$this->indent()->outputNewLine();
		$this->output($tree->right);
		$this->unindent()->outputNewLine();
		$this->output(')');
	}

	/**
	 * @param Sql_Tree_Select $obj
	 * @return void
	 */
	public function caseSelect(Sql_Tree_Select $tree) {
		$this->output('SELECT')->indent()->outputNewLine();
		$this->output($tree->selectExpr);
		$this->unindent()->outputNewLine();
		$this->output('FROM')->indent()->outputNewLine();
		$this->output($tree->tableReferences);
		$this->unindent()->outputNewLine();

		if ($tree->whereCondition) {
			$this->output('WHERE')->indent()->outputNewLine();
			$this->output($tree->whereCondition);
			$this->unindent()->outputNewLine();
		}
	}

	/**
	 * @param Sql_Tree_SelectExpr $tree
	 * @return void
	 */
	public function caseSelectExpr(Sql_Tree_SelectExpr $tree) {
		if ($tree->table) {
			$this->output($tree->table)->output('.');
		}
		$this->output($tree->field);
		if ($tree->alias) {
			$this->output(' AS ')->output($tree->alias);
		}
	}

	/**
	 * @param Sql_Tree_SimpleExpr $tree
	 * @return void
	 */
	public function caseSimpleExpr(Sql_Tree_SimpleExpr $tree) {
		// TODO
	}

	/**
	 * @param Sql_Tree_Star $tree
	 * @return void
	 */
	public function caseStar(Sql_Tree_Star $tree) {
		$this->output('*');
	}

	/**
	 * @param Sql_Tree_StringLiteral $tree
	 * @return void
	 */
	public function caseStringLiteral(Sql_Tree_StringLiteral $tree) {
		$this->output($tree->value);
	}

	/**
	 * @param Sql_Tree_TableFactor $tree
	 * @return void
	 */
	public function caseTableFactor(Sql_Tree_TableFactor $tree) {
		$this->output($tree->tableName);
		if ($tree->alias) {
			$this->output(' AS ')->output($tree->alias);
		}
	}
}

?>