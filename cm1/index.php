<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Peter Russ (peter.russ@4many.net)
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
 * fdfx_be_image module cm1
 *
 * @author	Peter Russ <peter.russ@4many.net>
 */



	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:fdfx_be_image/cm1/locallang.php');
#include ('locallang.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
	// ....(But no access check here...)
	// DEFAULT initialization of a module [END]

require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');
require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php'); 
require_once(t3lib_div::getFileAbsFileName('EXT:fdfx_be_image/cm1/class.fdfx_image.php'));
require_once(t3lib_div::getFileAbsFileName('EXT:fdfx_be_image/lib/class.fdfx_image_basic.php'));

class tx_fdfxbeimage_cm1 extends t3lib_SCbase {

	var $imgObj;
	var $extKey='fdfx_be_image';
	
	function menuConfig()	{
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $GLOBALS['LANG']->getLL('tx_fdfxbeimage_function1'),
/*
				'2' => $GLOBALS['LANG']->getLL('tx_fdfxbeimage_function2'),
				'3' => $GLOBALS['LANG']->getLL('tx_fdfxbeimage_function3'),
*/				
			)
		);
		parent::menuConfig();
	}

	function _init()
	{
		$this->imgObj=t3lib_div::makeInstance('fdfx_Image_Basic');
		$this->fileName=t3lib_div::_GP('id');	
		$this->imgObj->_init($this->extKey,$this->fileName);
	}

	/**
	 * Main function of the module. Write the content to 
	 */
	function main()	{
		global $BE_USER,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		$this->_init();
		// Draw the header.
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="POST">';

			// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				script_ended = 0;
				function jumpToUrl(URL)	{
					document.location = URL;
				}
			</script>
		';
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$this->doc->JScode .=$this->imgObj->_getCropHeader();
				break;
		}
		// Creating file management object:
		$this->basicff = t3lib_div::makeInstance('t3lib_basicFileFunctions');
		$this->basicff->init($GLOBALS['FILEMOUNTS'],$TYPO3_CONF_VARS['BE']['fileExtensions']);
		if (@file_exists($this->fileName))	
		{
			$this->target=$this->basicff->cleanDirectoryName($this->fileName);
		} 
		$key=$this->basicff->checkPathAgainstMounts($this->target.'/');
		if ($BE_USER->user['admin']  || $key )	
		{
			$this->pageinfo=array('_thePath' => '/');

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($GLOBALS['LANG']->getLL('tx_fdfxbeimage_title'));
			$this->content.=$this->doc->header($GLOBALS['LANG']->getLL('tx_fdfxbeimage_title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);


			// Render content:
			$this->moduleContent();


			// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		}
		$this->content.=$this->doc->spacer(10);
	}
	function printContent()	{

		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	function moduleContent()	{
		switch((string)$this->MOD_SETTINGS['function'])	{
			case 1:
				$content=$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_text');
				$content .= $this->imgObj->_getCropContent();
				$this->content.=$this->doc->section($GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_section_header'),$content,0,1);
			break;
			case 2:
				$content='<div align=center><strong>Menu item #2...</strong></div>';
				$this->content.=$this->doc->section('Message #2:',$content,0,1);
			break;
			case 3:
				$content='<div align=center><strong>Menu item #3...</strong></div>';
				$this->content.=$this->doc->section('Message #3:',$content,0,1);
			break;
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/cm1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_fdfxbeimage_cm1');
$SOBE->init();


$SOBE->main();
$SOBE->printContent();

?>