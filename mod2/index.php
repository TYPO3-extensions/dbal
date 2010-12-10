<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2004-2009 Kasper Skårhøj (kasperYYYY@typo3.com)
*  (c) 2004-2009 Karsten Dambekalns (karsten@typo3.org)
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

$BE_USER->modAccess($MCONF, 1);

require_once(t3lib_extMgm::extPath('dbal') . 'lib/Sql/Interfaces/TokensInterface.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/Sql/class.tx_dbal_sql_global.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/System/Io/StringReader.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/Sql/class.tx_dbal_sql_parser.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/Sql/class.tx_dbal_sql_printer.php');

class tx_dbal_module2 extends t3lib_SCbase implements TokensInterface {

	/**
	 * @var string
	 */
	protected $thisScript;

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	void
	 */
	public function main() {

		$this->thisScript = 'mod.php?M=' . $this->MCONF['name'];

		// Clean up settings:
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);

		// Draw the header
		$this->doc = t3lib_div::makeInstance('noDoc');
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		$this->doc->form = '<form action="" method="post">';

		// JavaScript
		$this->doc->JScode = $this->doc->wrapScriptTags('
				script_ended = 0;
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
				}
			');

		// DBAL page title:
		$this->content .= $this->doc->startPage('SQL Parser');
		$this->content .= $this->doc->header('SQL Parser');
		$this->content .= $this->doc->spacer(5);

		$sql = 'SELECT sys_refindex.*, tx_dam_file_tracking.* FROM sys_refindex, tx_dam_file_tracking WHERE sys_refindex.tablename = \'tx_dam_file_tracking\''
			. ' AND sys_refindex.ref_string LIKE CONCAT(tx_dam_file_tracking.file_path, tx_dam_file_tracking.file_name)';

		$sql = 'SELECT * FROM tt_content WHERE pid = 32';

		$loops = 200;

		/************************************
		 *
		 * Query (t3lib_DB)
		 *
		 ************************************/

		$db = t3lib_div::makeInstance('ux_t3lib_DB');
		$start = microtime(true);
		for ($i = 0; $i < $loops; $i++) $db->SELECTquery('*', 'tt_content', 'pid=32');
		$this->content .= $this->doc->section('t3lib_DB', $loops . ' loops: ' . ((microtime(true) - $start) * 1000) . ' ms');

		/************************************
		 *
		 * Query
		 *
		 ************************************/

		$this->content .= $this->doc->section('Query', $sql);

		/************************************
		 *
		 * Scanner
		 *
		 ************************************/

		$global = new tx_dbal_sql_Global();

		$start = microtime(true);
		/* @var tx_dbal_sql_Global $global */

		for ($i = 0; $i < $loops; $i++) {
			$inputStream = new System_Io_StringReader($sql);
			/* @var tx_dbal_System_Io_StringReader $inputStream */
			$scanner = new tx_dbal_sql_Scanner($global, $inputStream);
			/* @var tx_dbal_sql_Scanner $scanner */
			while ($scanner->token != self::EOF) {
				$scanner->nextToken();
			}
		}
		$end = microtime(true);

		$content .= '<p>' . $loops . ' loops: ' . (($end - $start) * 1000) . ' ms</p>';

		/* @var tx_dbal_sql_Global $global */
		$inputStream = new System_Io_StringReader($sql);
		/* @var tx_dbal_System_Io_StringReader $inputStream */
		$scanner = new tx_dbal_sql_Scanner($global, $inputStream);
		/* @var tx_dbal_sql_Scanner $scanner */

		$content .= '<div class="scanner">';
		while ($scanner->token != self::EOF) {
			$content .= $scanner->representation() . "<br />\n";
			$scanner->nextToken();
		}

		$content .= '</div>';

		$this->content .= $this->doc->section('Scanner', $content);

		/************************************
		 *
		 * Parser
		 *
		 ************************************/

		// TODO?

		/************************************
		 *
		 * Printer
		 *
		 ************************************/

		$start = microtime(true);

		$inputStream = new System_Io_StringReader($sql);
		/* @var tx_dbal_System_Io_StringReader $inputStream */
		$parser = new tx_dbal_sql_Parser($global, $inputStream);
		/* @var tx_dbal_sql_Parser $parser */
		$printer = new tx_dbal_sql_Printer();
		/* @var tx_dbal_sql_Printer $printer */

		$start = microtime(true);

		$content = '<div class="printer">';
		$content .= $printer->outputStatements($parser->parse())->flush();
		$content .= '</div><p>Execution time: ' . ((microtime(true) - $start) * 1000) . ' ms</p>';

		$this->content .= $this->doc->section('Printer', $content);

		// ShortCut
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
		}

		$this->content .= $this->doc->spacer(10);
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return	string HTML output
	 */
	public function printContent()	{
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/mod2/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dbal/mod2/index.php']);
}




	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_dbal_module2');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();

?>