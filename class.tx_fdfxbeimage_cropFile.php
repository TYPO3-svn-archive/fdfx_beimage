<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2006 Rene Fritz (r.fritz@colorcube.de)
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
 * Part of the DAM (digital asset management) extension.
 *
 * @author	Rene Fritz <r.fritz@colorcube.de>
 * @author	Peter Russ <peter russ@4many.net>
 * @version: 	$Id$
 * Date:        09.12.2006
 * @package fdfx_be_image
 */



require_once (PATH_txdam.'lib/class.tx_dam_actionbase.php');


/**
 * Image File action
 *
 * @author	Peter Russ<peter.russ@4many.net>
 * @package fdfx_be_image
 * @see tx_dam_actionbase
 */
class tx_fdfxbeimage_cropFile extends tx_dam_actionbase {

	var $cmd = 'tx_fdfxbeimage_modfunc1';

	/**
	 * Defines the types that the object can render
	 * @var array
	 */
	var $typesAvailable = array('icon', 'control');


	/**
	 * Returns true if the action is of the wanted type
	 * This method should return true if the action is possibly true.
	 * This could be the case when a control is wanted for a list of files and in beforhand a check should be done which controls might be work.
	 * In a second step each file is checked with isValid().
	 *
	 * @param	string		$type Action type
	 * @param	array		$itemInfo Item info array. Eg pathInfo, meta data array
	 * @param	array		$env Environment array. Can be set with setEnv() too.
	 * @return	boolean
	 */
	function isPossiblyValid ($type, $itemInfo=NULL, $env=NULL) {
		if ($valid = $this->isTypeValid ($type, $itemInfo, $env)) {
			$valid = ($this->itemInfo['__type'] == 'file');
		}
		return $valid;
	}


	/**
	 * Returns true if the action is of the wanted type
	 *
	 * @param	string		$type Action type
	 * @param	array		$itemInfo Item info array. Eg pathInfo, meta data array
	 * @param	array		$env Environment array. Can be set with setEnv() too.
	 * @return	boolean
	 */
	function isValid ($type, $itemInfo=NULL, $env=NULL) {
		$valid = $this->isTypeValid ($type, $itemInfo, $env);

		if ($valid) {
			$valid = ($this->itemInfo['__type'] == 'file' AND t3lib_div::inList($GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'], $this->itemInfo['file_type']));
		}
		return $valid;
	}


	/**
	 * Returns the icon image tag.
	 * Additional attributes to the image tagcan be added.
	 *
	 * @param	string		$addAttribute Additional attributes
	 * @return	string
	 */
	function getIcon ($addAttribute='') {
		global $BACK_PATH;

		if ($this->disabled) {
			$iconFile = $BACK_PATH.t3lib_extMgm::extRelPath('fdfx_be_image').'res/cm_icon_i.gif';
		} else {
			$iconFile = $BACK_PATH.t3lib_extMgm::extRelPath('fdfx_be_image').'res/cm_icon.gif';
		}
		$icon = '<img src="'.$iconFile.'" width="12px" height="12px"'.$this->_cleanAttribute($addAttribute).' alt="" />';

		return $icon;
	}


	/**
	 * Returns a short description for tooltips for example like: Delete folder recursivley
	 *
	 * @return	string
	 */
	function getDescription () {
		return $GLOBALS['LANG']->sL('LLL:EXT:fdfx_be_image/cm1/locallang.xml:function1');
	}


	/**
	 * Returns a command array for the current type
	 *
	 * @return	array		Command array
	 * @access private
	 */
	function _getCommand() {

		$filepath = $this->itemInfo['file_path_absolute'].$this->itemInfo['file_name'];

		$script = $GLOBALS['BACK_PATH'].PATH_txdam_rel.'mod_cmd/index.php';
		$script .= '?CMD='.$this->cmd;
		$script .= '&file='.rawurlencode($filepath);
		$script .= '&returnUrl='.rawurlencode($this->env['returnUrl']);

		$commands['href'] = $script;

		return $commands;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/class.tx_fdfxbeimage_cropFile.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/class.tx_fdfxbeimage_cropFile.php']);
}

?>