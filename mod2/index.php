<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2004-2009 Kasper Skårhøj (kasperYYYY@typo3.com)
 *  (c) 2004-2009 Karsten Dambekalns (karsten@typo3.org)
 *  (c) 2009-2011 Xavier Perseguers (typo3@perseguers.ch)
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

require_once(t3lib_extMgm::extPath('dbal') . 'lib/Sql/Core.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/Sql/Printer.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/Drivers/MySql/Writer.php');
require_once(t3lib_extMgm::extPath('dbal') . 'lib/Drivers/Oracle/Writer.php');

class tx_dbal_module2 extends t3lib_SCbase implements Sql_Interfaces_Tokens {

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

		//$this->testSpeed();
		$this->testQuery(
			'SELECT tt_content.uid, tt_content.pid, tt_content.header
			FROM tt_content
			WHERE bodytext LIKE \'%hello world!%\''
		);

		// ShortCut
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$this->content .= $this->doc->spacer(20) . $this->doc->section('', $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)), $this->MCONF['name']));
		}

		$this->content .= $this->doc->spacer(10);
	}

	/**
	 * Parses and outputs a SQL query.
	 *
	 * @param string $sql
	 * @return void
	 */
	protected function testQuery($sql) {
		$this->content .= $this->doc->section('Query', $sql);

		$scanner = new Sql_Scanner($sql);
		/* @var Sql_Scanner $scanner */

		$content = '<div class="scanner">';
		while ($scanner->token != self::EOF) {
			$content .= $scanner->representation() . "<br />\n";
			$scanner->nextToken();
		}

		$content .= '</div>';

		$this->content .= $this->doc->section('Scanner', $content);

		$parser = new Sql_Parser($sql);
		/* @var Sql_Parser $parser */
		$printer = new Sql_Printer();
		/* @var Sql_Printer $printer */

		$content = '<div class="printer">';
		$content .= $printer->outputStatements($parser->parse())->flush();
		$content .= '</div>';

		$this->content .= $this->doc->section('Printer', $content);

		$parser = new Sql_Parser($sql);
		/* @var Sql_Parser $parser */
		$writer = new Drivers_MySql_Writer();
		/* @var Sql_Interfaces_Writer $writer */

		$content = '<div class="writer">';
		$content .= $writer->rewrite($parser->parse());
		$content .= '</div>';

		$this->content .= $this->doc->section('Writer MySQL', $content);

		$parser = new Sql_Parser($sql);
		/* @var Sql_Parser $parser */
		$writer = new Drivers_Oracle_Writer();
		/* @var Sql_Interfaces_Writer $writer */

		$content = '<div class="writer">';
		$content .= $writer->rewrite($parser->parse());
		$content .= '</div>';

		$this->content .= $this->doc->section('Writer Oracle', $content);
	}

	/**
	 * Tests the speed of the parser.
	 *
	 * @return void
	 */
	protected function testSpeed() {
		$sql = 'SELECT sys_refindex.*, tx_dam_file_tracking.* FROM sys_refindex, tx_dam_file_tracking WHERE sys_refindex.tablename = \'tx_dam_file_tracking\''
			. ' AND sys_refindex.ref_string = CONCAT(file_path, file_name)';

		//$sql = 'SELECT * FROM tt_content WHERE pid = 32';

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

		$start = microtime(true);
		/* @var tx_dbal_sql_Global $global */

		for ($i = 0; $i < $loops; $i++) {
			$scanner = new Sql_Scanner($sql);
			/* @var Sql_Scanner $scanner */
			while ($scanner->token != self::EOF) {
				$scanner->nextToken();
			}
		}
		$end = microtime(true);

		$content .= '<p>' . $loops . ' loops: ' . (($end - $start) * 1000) . ' ms</p>';

		$scanner = new Sql_Scanner($sql);
		/* @var Sql_Scanner $scanner */

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

		$parser = new Sql_Parser($sql);
		/* @var Sql_Parser $parser */
		$printer = new Sql_Printer();
		/* @var Sql_Printer $printer */

		$start = microtime(true);

		$content = '<div class="printer">';
		$content .= $printer->outputStatements($parser->parse())->flush();
		$content .= '</div><p>Execution time: ' . ((microtime(true) - $start) * 1000) . ' ms</p>';

		$this->content .= $this->doc->section('Printer', $content);
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