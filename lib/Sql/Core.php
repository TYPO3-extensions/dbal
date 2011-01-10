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

require_once(dirname(__FILE__) . '/Exceptions/TokenExpected.php');
require_once(dirname(__FILE__) . '/Exceptions/UnknownToken.php');
require_once(dirname(__FILE__) . '/Parser.php');
require_once(dirname(__FILE__) . '/Functions/AbstractFunction.php');

// Register MySQL functions for the parser

require_once(dirname(__FILE__) . '/Functions/Concat.php');
Sql_Scanner::addFunction('CONCAT', 'Sql_Functions_Concat');

require_once(dirname(__FILE__) . '/Functions/Extract.php');
Sql_Scanner::addFunction('EXTRACT', 'Sql_Functions_Extract');

require_once(dirname(__FILE__) . '/Functions/FindInSet.php');
Sql_Scanner::addFunction('FIND_IN_SET', 'Sql_Functions_FindInSet');

?>