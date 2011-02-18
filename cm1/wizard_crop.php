<?php
/**
 * Copyright notice
 *
 * (c)  2006 -2011 Peter Russ (peter.russ@uon.li)  All rights reserved

 * License:
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the MPL Mozilla Public License
 * as published by the Free Software Foundation; either version 1.1
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * MPL Mozilla Public License for more details.
 *
 * You may have received a copy of the MPL Mozilla Public License
 * along with this program.
 *
 * An on-line copy of the MPL Mozilla Public License can be found
 * http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * @author: 		Peter Russ <peter.russ@uon.li>
 * @copyright:		(c) Peter Russ (peter.russ@uon.li), 2006 -2011
 * @version:		$Rev$
 * @package:		TYPO3
 * @subpackage:	fdfx_be_image
 * 
 */

unset ( $MCONF );
require ('conf.php');
require ($BACK_PATH . 'init.php');

$LANG->includeLLFile ( 'EXT:fdfx_be_image/cm1/locallang.xml' );


/**
 * Script Class for redirecting a backend user to the editing form when an "Edit wizard" link was clicked in TCEforms somewhere
 *
 * @author	Kasper Skaarhoj <kasperYYYY@typo3.com>
 * @package TYPO3
 * @subpackage core
 */
class SC_wizard_crop {

		// Internal, static: GPvars
	protected $P;						// Wizard parameters, coming from TCEforms linking to the wizard.
	protected $doClose;				// Boolean; if set, the window will be closed by JavaScript
	protected $uid;					// integer
	protected $cmd = 'tx_fdfxbeimage_modcrop';
	

	/**
	 * Initialization of the script
	 *
	 * @return	void
	 */
	public function init()	{
		$this->doClose = t3lib_div::_GP('doClose');		// Used for the return URL to alt_doc.php so that we can close the window.
		if (!$this->doClose) {
			$this->P = t3lib_div::_GP('P');
			parse_str($this->P['itemName'], $output);
			$uids = array_keys($output['data']['tt_content']);
			$this->uid = $uids[0];
		}
	}

	/**
	 * Main function
	 * Makes a header-location redirect to an edit form IF POSSIBLE from the passed data - otherwise the window will just close.
	 *
	 * @return	void
	 */
	public function main()	{
		global $TCA;

		if ($this->doClose)	{
			tx_fdfxbeimage_data::sessionReset();
			$this->closeWindow();
		} else {

				// Initialize:
			$table = $this->P['table'];
			$field = $this->P['field'];
			t3lib_div::loadTCA($table);
			$config = $TCA[$table]['columns'][$field]['config'];
			$fTable = $this->P['currentValue']<0 ? $config['neg_foreign_table'] : $config['foreign_table'];

			if (is_array($config) && $this->P['currentSelectedValues'] && (($config['type']=='select' && $config['foreign_table']) || ($config['type']=='group' && $config['internal_type']=='db')))	{	// MULTIPLE VALUES:
					// Init settings:
				$allowedTables = $config['type']=='group' ? $config['allowed'] : $config['foreign_table'].','.$config['neg_foreign_table'];
				$prependName=0;

					// Selecting selected values into an array:
				$dbAnalysis = t3lib_div::makeInstance('t3lib_loadDBGroup');
				$dbAnalysis->start($this->P['currentSelectedValues'],$allowedTables);
				$values = $dbAnalysis->getValueArray($prependName);
			
				$refField = $config['MM_match_fields']['ident'];
				$damFiles = tx_dam_db::getReferencedFiles('tt_content', $this->uid, $refField);

				$sessionData = array(
					  'uid_local' => $values[0]
					, 'uid_foreign' => $this->uid
					, 'isApi' => true
				);
				tx_fdfxbeimage_data::addStoredParamsFromDb($sessionData,$values[0],$this->uid);
				tx_fdfxbeimage_data::sessionSave($sessionData);
				
				$script = '../../../' . PATH_txdam_rel . 'mod_cmd/index.php';
				$script .= '?CMD=' . $this->cmd;
				$script .= '&file=' . rawurlencode ( t3lib_div::getFileAbsFileName($damFiles['files'][$values[0]] ));
				$script .= '&returnUrl=' . rawurlencode ('../../../' .  TYPO3_MOD_PATH . 'wizard_crop.php?doClose=1' );
				t3lib_utility_Http::redirect($script);
					
			} else {
				$this->closeWindow();
			}
		}
	}
	
	/**
	 * Printing a little JavaScript to close the open window.
	 *
	 * @return	void
	 */
	public function closeWindow()	{
		echo '<script language="javascript" type="text/javascript">close();</script>';
		exit;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/cm1//wizard_crop.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/cm1//wizard_crop.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('SC_wizard_crop');
$SOBE->init();
$SOBE->main();
?>