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
 * Abstract class for reading character streams.
 *
 * @category    System
 * @package     System
 * @subpackage  IO
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
abstract class System_Io_Reader {

	/**
	 * Reads data from source.
	 *
	 * If length is specified, then only that number of chars is read,
	 * otherwise stream is read until EOF.
	 *
	 * @param integer $len
	 * @return string
	 */
	public abstract function read($len = null);

	/**
	 * Returns the filename, url, etc. that is being read from.
	 *
	 * @return string
	 */
	public abstract function getResource();

	/**
	 * Moves stream position relative to current position.
	 *
	 * @param integer $n
	 * @return void
	 */
	public function skip($n) {
	}

	/**
	 * If supported, places a "marker" (like a bookmark) at current stream position.
	 * A subsequent call to reset() will move stream position back
	 * to last marker (if supported).
	 *
	 * @return void
	 */
	public function mark() {
	}

	/**
	 * Resets the current position in stream to beginning or last mark (if supported).
	 *
	 * @return void
	 */
	public function reset() {
	}

	/**
	 * Returns whether marking is supported.
	 *
	 * @return boolean
	 */
	public function markSupported() {
		return false;
	}

	/**
	 * Closes stream.
	 *
	 * @return void
	 * @throws IOException if there is an error closing stream
	 */
	public abstract function close();

	/**
	 * Is stream ready for reading.
	 *
	 * @return boolean
	 */
	public function ready() {
		return TRUE;
	}

}

?>