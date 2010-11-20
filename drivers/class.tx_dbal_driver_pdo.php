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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * PDO driver for DBAL.
 *
 * @category    Plugin
 * @package     TYPO3
 * @subpackage  tx_dbal
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class tx_dbal_driver_pdo {

	/**
	 * @var PDO
	 */
	protected $handle;

	/**
	 * Default constructor.
	 *
	 * @param string $driver
	 * @param string $server use <server>:<port> if you need to specify a specific port to connect to your server
	 * @param string $database
	 * @param string $username
	 * @param string $password
	 * @param boolean $persistent
	 * @param string $initCommands Supported only for MySQL
	 * @throws PDOException
	 */
	public function __construct($driver, $server, $database, $username = '', $password = '', $persistent = FALSE, $initCommands = '') {
		$dsn = $driver . ':';
		$port = '';
		if (($colon = strpos(':', $server)) !== FALSE) {
			$port = substr($server, $colon + 1);
			$server = substr($server, 0, $colon);
		}

		switch ($driver) {
			case 'sqlsrv':
				$dsn .= 'Server=' . $server . ';Database=' . $database;
				break;
			default:
				$dsn .= 'host=' . $server . ';dbname=' . $database;
				break;
		}

		if ($port) {
			$dsn .= ';port=' . $port;
		}

		$options = array();
		if ($persistent) {
			$options[PDO::ATTR_PERSISTENT] = TRUE;
		}
		if ($driver === 'mysql' && $initCommands) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = $initCommands;
		}

		if ($options) {
			$this->handle = new PDO($dsn, $username, $password, $options);
		} else {
			$this->handle = new PDO($dsn, $username, $password);
		}
	}

	/**
	 * Default destructor.
	 *
	 * @return void
	 */
	public function __destruct() {
		$this->handle = null;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/class.tx_dbal_driver_pdo.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/class.tx_dbal_driver_pdo.php']);
}

?>