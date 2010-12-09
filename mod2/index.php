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
/**
 * @author Xavier Perseguers <typo3@perseguers.ch>
  */

$BE_USER->modAccess($MCONF, 1);

class tx_dbal_module2 extends t3lib_SCbase implements tx_dbal_sql_Tokens {

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

		$global = t3lib_div::makeInstance('tx_dbal_sql_Global');
		/* @var tx_dbal_sql_Global $global */
		$inputStream = t3lib_div::makeInstance('tx_dbal_System_Io_StringReader', $sql);
		/* @var tx_dbal_System_Io_StringReader $inputStream */
		$scanner = t3lib_div::makeInstance('tx_dbal_sql_Scanner', $global, $inputStream);
		/* @var tx_dbal_sql_Scanner $scanner */

		$content = '';
		$i = 0;
		while ($scanner->token != self::EOF && $i++ < 30) {
			$content .= $scanner->representation() . "<br />\n";
			$scanner->nextToken();
		}

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

		$inputStream = t3lib_div::makeInstance('tx_dbal_System_Io_StringReader', $sql);
		/* @var tx_dbal_System_Io_StringReader $inputStream */
		$parser = t3lib_div::makeInstance('tx_dbal_sql_Parser', $global, $inputStream);
		/* @var tx_dbal_sql_Parser $parser */
		$printer = t3lib_div::makeInstance('tx_dbal_sql_Printer');
		/* @var tx_dbal_sql_Printer $printer */

		$content = $printer->outputStatements($parser->parse())->flush();

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