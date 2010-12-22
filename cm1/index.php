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

// DEFAULT initialization of a module [BEGIN]
unset ( $MCONF );
require ('conf.php');
require ($BACK_PATH . 'init.php');

$LANG->includeLLFile ( 'EXT:fdfx_be_image/cm1/locallang.xml' );

class tx_fdfxbeimage_cm1 extends t3lib_SCbase {
	
	protected $imgObj;
	protected $extKey = 'fdfx_be_image';
	
	public function menuConfig() {
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_function1' ), 
				'2' => $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_function2' ) 
				) 
			);
		if (self::isRotationDisabled()) {
			unset($this->MOD_MENU['function']['2']);
		}
		parent::menuConfig ();
	}
	
	static protected function isRotationDisabled() {
		static $userConf = null;
		if (is_null ($userConf)) {
			$userConf = $GLOBALS ['BE_USER']->getTSConfig ( strtoupper ( 'fdfx_be_image' ) );
		}
		$isDisabled = false;
		if (isset($userConf['properties']) && isset($userConf['properties']['disableRotation'])) {
			$isDisabled = $userConf['properties']['disableRotation'] == true;
		}
		return $isDisabled;
	}
		
	protected function _init() {
		if ($id = t3lib_div::_GP ( 'id' )) {
			$this->fileName = $id;
			$array = array ('fileName' => $this->fileName );
			$GLOBALS ['BE_USER']->setAndSaveSessionData ( $this->extKey, $array );
		} else {
			$array = $GLOBALS ['BE_USER']->getSessionData ( $this->extKey );
			if (is_array ( $array )) {
				$this->fileName = $array ['fileName'];
			}
		}
	}
	
	protected function _initImageObject($class = 'Crop') {
		$this->imgObj = t3lib_div::makeInstance ( 'tx_fdfxbeimage_Image' . $class );
		$this->imgObj->_init ( $this->extKey, $this->fileName );
	}
	
	/**
	 * Main function of the module. Write the content to
	 */
	public function main() {

		$this->_init ();
		// Draw the header.
		$this->doc = t3lib_div::makeInstance ( 'bigDoc' );
		$this->doc->backPath = $GLOBALS['BACK_PATH'];
		
		// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';
		switch (( string ) $this->MOD_SETTINGS ['function']) {
			case '1' :
				$this->doc->form = '<form action="" method="POST">';
				$this->_initImageObject ( 'Crop' );
				$this->doc->JScode .= $this->imgObj->_getHeader ();
				break;
			case '2' :
				$this->_initImageObject ( 'Rotate' );
				$this->doc->JScode .= $this->imgObj->_getHeader ();
				break;
		}
		// Creating file management object:
		$this->basicff = t3lib_div::makeInstance ( 't3lib_basicFileFunctions' );
		$this->basicff->init ( $GLOBALS ['FILEMOUNTS'], $GLOBALS['TYPO3_CONF_VARS']['BE'] ['fileExtensions'] );
		if (@file_exists ( $this->fileName )) {
			$this->target = $this->basicff->cleanDirectoryName ( $this->fileName );
		}
		$key = $this->basicff->checkPathAgainstMounts ( $this->target . '/' );
		if ($GLOBALS['BE_USER']->user ['admin'] || $key) {
			$this->pageinfo = array ('_thePath' => '/' );
			
			$headerSection = $this->doc->getHeader ( 'pages', $this->pageinfo, $this->pageinfo ['_thePath'] ) . '<br>' . $GLOBALS ['LANG']->sL ( 'LLL:EXT:lang/locallang_core.xml:labels.path' ) . ': ' . t3lib_div::fixed_lgd_pre ( $this->pageinfo ['_thePath'], 50 );
			
			$this->content .= $this->doc->startPage ( $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_title' ) );
			$this->content .= $this->doc->header ( $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_title' ) );
			$this->content .= $this->doc->spacer ( 5 );
			$this->content .= $this->doc->section ( '', $this->doc->funcMenu ( $headerSection, t3lib_BEfunc::getFuncMenu ( $this->id, 'SET[function]', $this->MOD_SETTINGS ['function'], $this->MOD_MENU ['function'] ) ) );
			$this->content .= $this->doc->divider ( 5 );
			
			// Render content:
			$this->moduleContent ();
			
			// ShortCut
			if ($GLOBALS['BE_USER']->mayMakeShortcut ()) {
				$this->content .= $this->doc->spacer ( 20 ) . $this->doc->section ( '', $this->doc->makeShortcutIcon ( 'id', implode ( ',', array_keys ( $this->MOD_MENU ) ), $this->MCONF ['name'] ) );
			}
		}
		$this->content .= $this->doc->spacer ( 10 );
	}
	
	public function printContent() {
		$this->content .= $this->doc->endPage ();
		echo $this->content;
	}
	
	public function moduleContent() {
		switch (( string ) $this->MOD_SETTINGS ['function']) {
			case 1 :
				$content = $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_crop_text' );
				$content .= $this->imgObj->_getContent ();
				$this->content .= $this->doc->section ( $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_crop_section_header' ), $content, 0, 1 );
				break;
			case 2 :
				$content = $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_rotate_text' );
				$content .= $this->imgObj->_getContent ();
				$this->content .= $this->doc->section ( $GLOBALS ['LANG']->getLL ( 'tx_fdfxbeimage_rotate_section_header' ), $content, 0, 1 );
				break;
			case 3 :
				$content = '<div align=center><strong>Menu item #3...</strong></div>';
				$this->content .= $this->doc->section ( 'Message #3:', $content, 0, 1 );
				break;
		}
	}
}

if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/cm1/index.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/cm1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance ( 'tx_fdfxbeimage_cm1' );
$SOBE->init ();

$SOBE->main ();
$SOBE->printContent ();

?>