<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Xavier Perseguers <typo3@perseguers.ch>
 *  All rights reserved
 *
 *  Partially based on work on project http://phing.info.
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
 * Dummy class for reading from string of characters.
 *
 * @category    System
 * @package     TYPO3
 * @subpackage  tx_dbal\system\io
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class tx_dbal_System_Io_StringReader extends tx_dbal_System_Io_Reader {

	/**
	 * @var string
	 */
	private $_string;

	/**
	 * @var int
	 */
	private $mark = 0;

	/**
	 * @var int
	 */
	private $currPos = 0;

	/**
	 * Default constructor.
	 *
	 * @param string $string
	 */
	public function __construct($string) {
		$this->_string = $string;
	}

	/**
	 * Moves stream position relative to current position.
	 *
	 * @param integer $n
	 * @return void
	 */
	public function skip($n) {
		$this->currPos += $n;
	}

	/**
	 * Reads data from source.
	 *
	 * If length is specified, then only that number of chars is read,
	 * otherwise stream is read until EOF.
	 *
	 * @param integer $len
	 * @return string
	 */
	public function read($len = null) {
		if ($len === null) {
			return $this->_string;
		} else {
			if ($this->currPos >= strlen($this->_string)) {
				return -1;	// EOF
			}
			$out = substr($this->_string, $this->currPos, $len);
			$this->currPos += $len;
			return $out;
		}
	}

	/**
	 * Returns the string that is being read from.
	 *
	 * @return string
	 */
	public function getResource() {
		return '(string) "' . $this->_string . '"';
	}

	/**
	 * Places a "marker" (like a bookmark) at current stream position.
	 * A subsequent call to reset() will move stream position back
	 * to last marker.
	 *
	 * @return void
	 */
	public function mark() {
		$this->mark = $this->currPos;
	}

	/**
	 * Resets the current position in stream to beginning or last mark (if supported).
	 *
	 * @return void
	 */
	public function reset() {
		$this->currPos = $this->mark;
	}

	/**
	 * Returns whether marking is supported.
	 *
	 * @return boolean
	 */
	public function markSupported() {
		return TRUE;
	}

	/**
	 * Closes stream.
	 *
	 * @return void
	 * @throws IOException if there is an error closing stream
	 */
	public function close() {
	}

	/**
	 * Is stream ready for reading.
	 *
	 * @return boolean
	 */
	public function ready() {
		return TRUE;
	}

}


if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/system/io/class.tx_dbal_system_io_stringreader.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dbal/lib/system/io/class.tx_dbal_system_io_stringreader.php']);
}

?>