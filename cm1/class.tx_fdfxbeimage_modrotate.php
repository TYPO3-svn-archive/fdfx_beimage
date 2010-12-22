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

class tx_fdfxbeimage_modrotate extends t3lib_extobjbase {
	protected $imgObj;
	protected $extKey = 'fdfx_be_image';
	protected $fileName = '';
	
	function _init() {
		$this->imgObj = t3lib_div::makeInstance ( 'tx_fdfxbeimage_Image_Rotate' );
		$this->fileName = t3lib_div::_GP ( 'file' );
		$this->imgObj->_init ( $this->extKey, $this->fileName );
	}
	function accessCheck() {
		$dam = new tx_dam ();
		if (method_exists ( $dam, 'access_checkAction' )) {
			return tx_dam::access_checkAction ( 'editFile' );
		} elseif (method_exists ( $dam, 'checkFileOperation' )) {
			return tx_dam::access_checkFileOperation ( 'editFile' );
		} elseif (method_exists ( $dam, 'access_checkFileOperation' )) {
			return tx_dam::access_checkFileOperation ( 'editFile' );
		
		} else {
			die ( __FILE__ . ':' . __LINE__ . 'Problem with DAM ' );
		}
	
	}
	function head() {
		$GLOBALS ['SOBE']->pageTitle = $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_function2' );
	}
	function main() {
		$this->_init ();
		$this->pObj->doc->JScode .= $this->imgObj->getHeader ();
		
		$content = $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_rotate_text' );
		$content .= $this->imgObj->getContent ();
		if ($this->imgObj->getDocHeaderButtons ()) {
			$this->pObj->docHeaderButtons ['SAVE'] = '';
			$this->pObj->docHeaderButtons ['CLOSE'] = '<a href="#" onclick="jumpBack(); return false;"><img' . t3lib_iconWorks::skinImg ( $this->pObj->doc->backPath, 'gfx/closedok.gif' ) . ' class="c-inputButton" title="' . $GLOBALS ['LANG']->sL ( 'LLL:EXT:lang/locallang_core.xml:labels.cancel', 1 ) . '" alt="" height="16" width="16"></a>';
			$this->pObj->markers ['FOLDER_INFO'] = substr ( $this->fileName, strlen ( PATH_site ) );
			$this->pObj->markers ['CSH'] = '';
		}
		return $content;
	}
}

if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/cm1/class.tx_fdfxbeimage_modrotate.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/cm1/class.tx_fdfxbeimage_modrotate.php']);
}
?>
