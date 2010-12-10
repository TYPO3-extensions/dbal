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
 * Abstract class for the tree nodes of the abstract grammar.
 *
 * The whole parser is based on compilation course (LAMP) I attended at
 * Swiss Federal Institute of Technology. Nice to use that again ;-)
 * @see http://lamp.epfl.ch/teaching/archive/compilation/2002/project/assignments/1/instructions_header_web.shtml
 *
 * @category    Parser
 * @package     SQL
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
abstract class Sql_AbstractTree {

	/**
	 * @var integer
	 */
	public $pos;

	/**
	 * @var integer
	 */
	public $depth;

	/**
	 * @var Sql_Type
	 */
	public $type;

	/**
	 * @var Sql_Symbol
	 */
	public $symbol;

	/**
	 * Default constructor.
	 *
	 * @param integer $pos
	 */
	public function __construct($pos) {
		$this->pos = $pos;
		$this->depth = 1;
	}

	/**
	 * Gets the maximal depth of an array of {@see Sql_AbstractTree}.
	 *
	 * @param Sql_AbstractTree[] $trees
	 * @return integer
	 */
	public function getDepth(array $trees) {
		$depth = 0;
		foreach ($trees as $tree) {
			if ($tree->depth > $depth) {
				$depth = $tree->depth;
			}
		}
		return $depth;
	}

	/**
	 * Applies the visitor.
	 *
	 * @param Sql_Interfaces_Visitor $visitor
	 * @return void
	 * @abstract
	 */
	public abstract function apply(Sql_Interfaces_Visitor $visitor);

}

?>