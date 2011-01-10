<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Xavier Perseguers <typo3@perseguers.ch>
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
 * Handles the MySQL function "EXTRACT".
 *
 * @category    Functions
 * @package     SQL
 * @subpackage  Functions
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010-2011
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Sql_Functions_Extract extends Sql_Functions_AbstractFunction {

	/**
	 * Parses the arguments of the function.
	 *
	 * @param Sql_Parser $parser
	 * @param Sql_Tree_Function $function Prepared function object to be populated with arguments
	 * @return void
	 * @static
	 * @link http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_extract
	 */
	public static function parseArguments(Sql_Parser $parser, Sql_Tree_Function $function) {
		$function->addArgument(self::parseUnit($parser));

		$parser->accept($parser::T_FROM);

		list($table, $field, $allowAlias) = $parser->parseIdentifier();
		$function->addArgument(new Sql_Tree_SelectExpr($parser->start, $table, $field, null));
	}

	/**
	 * Parses a date unit.
	 *
	 * @param Sql_Parser $parser
	 * @return Sql_Tree_Identifier
	 * @static
	 * @link http://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-add
	 */
	protected static function parseUnit(Sql_Parser $parser) {
		$unit = $parser->chars;
		$parser->accept($parser::T_IDENTIFIER);

		switch ($unit) {
			case 'MICROSECOND':
			case 'SECOND':
			case 'MINUTE':
			case 'HOUR':
			case 'DAY':
			case 'WEEK':
			case 'MONTH':
			case 'QUARTER':
			case 'YEAR':
			case 'SECOND_MICROSECOND':
			case 'MINUTE_MICROSECOND':
			case 'MINUTE_SECOND':
			case 'HOUR_MICROSECOND':
			case 'HOUR_SECOND':
			case 'HOUR_MINUTE':
			case 'DAY_MICROSECOND':
			case 'DAY_SECOND':
			case 'DAY_MINUTE':
			case 'DAY_HOUR':
			case 'YEAR_MONTH':
				return new Sql_Tree_Identifier($parser->start, $unit);
			default:
				$parser->error('Unit token');
		}
	}

}

?>