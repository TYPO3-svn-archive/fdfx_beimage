<?php
/**
*  Copyright notice
*
*  (c)  2006 -2011 Peter Russ (peter.russ@uon.li)  All rights reserved

* License:
*    This program is free software; you can redistribute it and/or
*    modify it under the terms of the MPL Mozilla Public License
*    as published by the Free Software Foundation; either version 1.1
*    of the License, or (at your option) any later version.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    MPL Mozilla Public License for more details.
*
*    You may have received a copy of the MPL Mozilla Public License
*    along with this program.
*
*    An on-line copy of the MPL Mozilla Public License can be found
*    http://www.mozilla.org/MPL/MPL-1.1.html
*
*   @author: 		Peter Russ <peter.russ@uon.li>
*   @copyright:		(c) Peter Russ (peter.russ@uon.li), 2006 -2011
*   @version:		$Rev$
*   @package:		TYPO3
*   @subpackage:	fdfx_be_image
*   
*/

/**
 * Image File action
 *
 * @author	Peter Russ<peter.russ@uon.li>
 * @package fdfx_be_image
 * @see tx_dam_actionbase
 */
require_once t3lib_extMgm::extPath('dam') . 'lib/class.tx_dam_actionbase.php';

class tx_fdfxbeimage_rotateFile extends tx_dam_actionbase {

	protected $cmd = 'tx_fdfxbeimage_modrotate';

	/**
	 * Defines the types that the object can render
	 * @var array
	 */
	public $typesAvailable = array('icon', 'control');


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
	public function isPossiblyValid ($type, $itemInfo=NULL, $env=NULL) {
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
	public function isValid ($type, $itemInfo=NULL, $env=NULL) {
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
	public function getIcon ($addAttribute='') {
		global $BACK_PATH;

		if ($this->disabled) {
			$iconFile = $BACK_PATH.t3lib_extMgm::extRelPath('fdfx_be_image').'res/cm_icon_rotate.i.png';
		} else {
			$iconFile = $BACK_PATH.t3lib_extMgm::extRelPath('fdfx_be_image').'res/cm_icon_rotate.png';
		}
		$icon = '<img src="'.$iconFile.'" width="16px" height="16px"'.$this->_cleanAttribute($addAttribute).'alt="'. $this->getDescription(). '" />';

		return $icon;
	}


	/**
	 * Returns a short description for tooltips for example like: Delete folder recursivley
	 *
	 * @return	string
	 */
	public function getDescription () {
		return $GLOBALS['LANG']->sL('LLL:EXT:fdfx_be_image/cm1/locallang.xml:tx_fdfxbeimage_function2');
	}


	/**
	 * Returns a command array for the current type
	 *
	 * @return	array		Command array
	 * @access private
	 */
	public function _getCommand() {

		$filepath = $this->itemInfo['file_path_absolute'].$this->itemInfo['file_name'];

		$script = $GLOBALS['BACK_PATH'].PATH_txdam_rel.'mod_cmd/index.php';
		$script .= '?CMD='.$this->cmd;
		$script .= '&file='.rawurlencode($filepath);
		$script .= '&returnUrl='.rawurlencode($this->env['returnUrl']);

		$commands['href'] = $script;

		return $commands;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/action/class.tx_fdfxbeimage_rotateFile.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/action/class.tx_fdfxbeimage_rotateFile.php']);
}

?>