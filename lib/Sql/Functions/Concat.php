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
 * Handles the MySQL function "CONCAT".
 *
 * @category    Functions
 * @package     SQL
 * @subpackage  Functions
 * @author      Xavier Perseguers <typo3@perseguers.ch>
 * @copyright   Copyright 2010
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Sql_Functions_Concat extends Sql_Functions_AbstractFunction {

	/**
	 * Parses the arguments of the function.
	 *
	 * @param Sql_Parser $parser
	 * @param Sql_Tree_Function $function Prepared function object to be populated with arguments
	 * @return void
	 * @static
	 */
	public static function parseArguments(Sql_Parser $parser, Sql_Tree_Function $function) {
		// TODO: Enhance this, only real basic support of CONCAT() is handled here

		$name = $parser->chars;
		$parser->accept(self::T_IDENTIFIER);
		$function->addArgument(new Sql_Tree_Identifier($parser->start, $name));

		$parser->accept(self::T_COMMA);

		$name = $parser->chars;
		$parser->accept(self::T_IDENTIFIER);
		$function->addArgument(new Sql_Tree_Identifier($parser->start, $name));
	}

}

?>