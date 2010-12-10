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
 * This class contains methods to encode the position (line / column)
 * using an integer.
 *
 * The whole parser is based on compilation course (LAMP) I attended at
 * Swiss Federal Institute of Technology. Nice to use that again ;-)
 * @see http://lamp.epfl.ch/teaching/archive/compilation/2002/project/assignments/1/instructions_header_web.shtml
 *
 * @category    Parser
 * @package     TYPO3
 * @subpackage  tx_dbal\sql
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class tx_dbal_sql_Position {

	/**
	 * Number of bits reserved for encoding the column.
	 */
	const columnBits = 12;

	/**
	 * Bitmask to encode and decode the column.
	 */
	const columnMask = 4095; // (1 << self::columnBits) - 1

	/**
	 * Unknown position.
	 */
	const UNDEFINED = 0;

	/**
	 * First character of any file/string.
	 */
	const FIRST = 4097; // (1 << self::columnBits) | 1

	/**
	 * Encodes the line/column in a single integer.
	 *
	 * @param integer $line
	 * @param integer $column
	 * @return integer The encoded position
	 * @static
	 */
	public static function encode($line, $column) {
		return ($line << self::columnBits) | ($column & self::columnMask);
	}

	/**
	 * Extracts the line encoded in a position.
	 *
	 * @param integer $position
	 * @return integer
	 * @static
	 */
	public static function line($position) {
		return $position >> self::columnBits;
	}

	/**
	 * Extracts the column encoded in a position.
	 *
	 * @param integer $position
	 * @return integer
	 * @static
	 */
	public static function column($position) {
		return $position & self::columnMask;
	}

}

?>