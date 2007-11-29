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

class tx_fdfxbeimage_cm1 extends t3lib_SCbase {
	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 */
	var $fileName='';
	function menuConfig()	{
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'1' => $LANG->getLL('function1'),
/*
				'2' => $LANG->getLL('function2'),
				'3' => $LANG->getLL('function3'),
*/				
			)
		);
		parent::menuConfig();
	}

	/**
	 * Main function of the module. Write the content to 
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
			// Draw the header.
		$this->doc = t3lib_div::makeInstance('mediumDoc');
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
				$this->fileName=t3lib_div::_GP('id');
				$this->doc->JScode .=$this->_getCropHeader();
				break;
		}				

		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{
			if ($BE_USER->user['admin'] && !$this->id)	{
				$this->pageinfo=array('title' => '[root-level]','uid'=>0,'pid'=>0);
			}

			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
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
				$content='<div align=center><strong>Hello World!</strong></div><BR>
					The "Kickstarter" has made this module automatically, it contains a default framework for a backend module but apart from it does nothing useful until you open the script "'.substr(t3lib_extMgm::extPath('fdfx_be_image'),strlen(PATH_site)).'cm1/index.php" and edit it!
					<HR>
					<BR>This is the GET/POST vars sent to the script:<BR>'.
					'GET:'.t3lib_div::view_array($_GET).'<BR>'.
					'POST:'.t3lib_div::view_array($_POST).'<BR>'.
					'';
				$content .= $this->_getCropContent();
				$this->content.=$this->doc->section('Message #1:',$content,0,1);
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
	function _getCropContent()
	{
		$content='';
		$fI = t3lib_div::split_fileref($this->fileName);
		$fileName=t3lib_div::isFirstPartOfStr($this->fileName,PATH_site)?'../../../../'.substr($this->fileName,strlen(PATH_site)):$fI['file'];
$content='
<div id="pageContent">
<div id="dhtmlgoodies_xpPane">  
	<div class="dhtmlgoodies_panel">
		<div>
			<!-- Start content of pane -->
			<form>
			<input type="hidden" id="input_image_ref" value="'.$fileName.'">
			<table>
				<tr>
					<td>X:</td><td><input type="text" class="textInput" name="crop_x" id="input_crop_x"></td>
				</tr>
				<tr>
					<td>Y:</td><td><input type="text" class="textInput" name="crop_y" id="input_crop_y"></td>
				</tr>
				<tr>
					<td>Width:</td><td><input type="text" class="textInput" name="crop_width" id="input_crop_width"></td>
				</tr>
				<tr>
					<td>Height:</td><td><input type="text" class="textInput" name="crop_height" id="input_crop_height"></td>
				</tr>
				<tr>
					<td>Percent size:</td><td><input type="text" class="textInput" name="crop_percent_size" id="crop_percent_size" value="100"></td>
				</TR>					
				<tr>
					<td>Convert to:</td>
					<td>
						<select class="textInput" id="input_convert_to">
							<option value="gif">Gif</option>
							<option value="jpg" selected>Jpg</option>
							<option value="png">Png</option>
						</select>
					</td>
				</tr>
				<tr>
					<td></td>
					<td id="cropButtonCell"><input type="button" onclick="cropScript_executeCrop(this)" value="Crop">

					</td>
				</tr>
			</table>
			<div id="crop_progressBar">
			
			</div>		
			</form>
			<!-- End content -->
		</div>	
	</div>
	<div class="dhtmlgoodies_panel">
		<div>
			<!-- Start content of pane -->
			<table>
				<tr>
					<td><b>'.$fI['fileName'].'</b></td>
				</tr>
				<tr>
					<td>Dimension: <span id="label_dimension"></span></td>
				</tr>
			</table>
			<!-- End content -->
		</div>		
	</div>
	<div class="dhtmlgoodies_panel">
		<div>
			<!-- Start content of pane -->
			
			To select crop area, drag and move the dotted rectangle or type in values directly into the form.
			
			<!-- End of content -->
		</div>		
	</div>
</div>

<div class="crop_content">
<div id="imageContainer">
<img src="'.$fileName.'">
</div>
</div>
</div>

<script type="text/javascript">
initDhtmlgoodies_xpPane(Array(\'Crop inspector\',\'Image details\',\'Instructions\'),Array(true,true),Array(\'pane1\',\'pane2\',\'pane3\'));
init_imageCrop();
</script>
'; 
		
		return $content;
	}
	function _getCropHeader()
	{
		$extPath='../res/crop-image/';
		$imgObj = t3lib_div::makeInstance('t3lib_stdGraphic');
		$imgObj->init();
		$imgObj->mayScaleUp = 0;
		$imgObj->absPrefix = PATH_site;

			// Read Image Dimensions (returns false if file was not an image type, otherwise dimensions in an array)
		$imgInfo = '';
		$imgInfo = $imgObj->getImageDimensions($this->fileName);
		$content='
	<link rel="stylesheet" href="'.$extPath.'css/xp-info-pane.css">
	<link rel="stylesheet" href="'.$extPath.'css/image-crop.css">
	<script type="text/javascript" src="'.$extPath.'js/xp-info-pane.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/ajax.js"></script>
	<script type="text/javascript">
	var crop_script_server_file =\''.$extPath.'crop_image.php\';
	
	var cropToolBorderWidth = 1;	// Width of dotted border around crop rectangle
	var smallSquareWidth = 7;	// Size of small squares used to resize crop rectangle
	
	// Size of image shown in crop tool
	var crop_imageWidth = 600;
	var crop_imageHeight = 450;
	
	// Size of original image
	var crop_originalImageWidth = '.$imgInfo[1].';
	var crop_originalImageHeight ='.$imgInfo[0].';
	
	var crop_minimumPercent = 10;	// Minimum percent - resize
	var crop_maximumPercent = 200;	// Maximum percent -resize
	
	
	var crop_minimumWidthHeight = 15;	// Minimum width and height of crop area
	
	var updateFormValuesAsYouDrag = true;	// This variable indicates if form values should be updated as we drag. This process could make the script work a little bit slow. That is why this option is set as a variable.
	if(!document.all)updateFormValuesAsYouDrag = false;	// Enable this feature only in IE
	
	/* End of variables you could modify */
	</script>
	<script type="text/javascript" src="'.$extPath.'js/image-crop.js"></script>
';
		return $content;		
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