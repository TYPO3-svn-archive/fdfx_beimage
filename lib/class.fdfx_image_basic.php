<?php
#########################################################################
#
# License:
#    This program is free software; you can redistribute it and/or
#    modify it under the terms of the MPL Mozilla Public License
#    as published by the Free Software Foundation; either version 1.1
#    of the License, or (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    MPL Mozilla Public License for more details.
#
#    You may have received a copy of the MPL Mozilla Public License
#    along with this program.
#
#    An on-line copy of the MPL Mozilla Public License can be found
#    http://www.mozilla.org/MPL/MPL-1.1.html
#
# 	Copyright (c) 2006 by 4Many Services
#   @author: 	Peter Russ <peter.russ@4many.net>
#   @version:	$Id$
#
#   Date:       09.12.2006
#   Filename:   class.fdfx_image_basic.php
#
#   Project:    fdfx_be_image
#
##################################################
class fdfx_Image_Basic
{
	var $fileName = '';
	var $fileNameLocal='';
	var $conf = array ();
	var $extKey='';
	function _init($extKey='fdfx_be_image',$fName='')
	{
		$this->extKey=$extKey;
		if ($fName!='')
		{
			$this->setFileName($fName);
		}
		$userConf = $GLOBALS['BE_USER']->getTSConfig(strtoupper($extKey));
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
		if (isset ($userConf['properties']) && is_array($userConf['properties']))
		{
			if (isset ($userConf['properties']['maxWidth']) && $userConf['properties']['maxWidth'] > 0)
			{
				$this->conf['MAX_WIDTH'] = $userConf['properties']['maxWidth'];
			}
			if (isset ($userConf['properties']['maxHeight']) && $userConf['properties']['maxHeight'] > 0)
			{
				$this->conf['MAX_HEIGHT'] = $userConf['properties']['maxHeight'];
			}
		}
	}
	
	function setFileName($fName)
	{
		if (file_exists($fName))
		{
			$this->fileName=$fName;
		}
	}
	function getFileName()
	{
		return $this->fileName;
	}
	function _getCropContent()
	{
		$content='';
		$fI = t3lib_div::split_fileref($this->fileName);
		$fileNameLocal=substr($this->fileName,strlen(PATH_site));
		$fileName=t3lib_div::isFirstPartOfStr($this->fileName,PATH_site)?'../../../../'.(($this->fileNameLocal)?$this->fileNameLocal:$fileNameLocal):$fI['file'];
$content='
<div id="pageContent">
<div class="crop_content">
<div id="imageContainer">
<img src="'.$fileName.'">
</div>
</div>
<form>
<input type="hidden" id="input_image_ref" value="'.$fileNameLocal.'">
<fieldset id="fdfx-be-image-crop-offset">
	<legend>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_legend_offset').'</legend>
    <div class="item item-crop-x">
    	<label for=""input_crop_x">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_x').'</label>
        <input type="text" class="textInput" name="crop_x" id="input_crop_x" />
    </div>
    <div class="item item-crop-y">
    	<label for=""input_crop_y">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_y').'</label>
        <input type="text" class="textInput" name="crop_y" id="input_crop_y" />
    </div>
	<div class="item item-dimension">
		Dimension: <span id="label_dimension"></span>
	</div>
</fieldset>
<fieldset id="fdfx-be-image-crop-dimension">
	<legend>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_legend_dimension').'</legend>
    <div class="item item-crop-width">
    	<label for=""input_crop_width">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_width').'</label>
        <input type="text" class="textInput" name="crop_width" id="input_crop_width" />
    </div>
    <div class="item item-crop-height">
    	<label for=""input_crop_height">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_height').'</label>
        <input type="text" class="textInput" name="crop_height" id="input_crop_height" />
    </div>
</fieldset>
<fieldset id="fdfx-be-image-crop-output">
	<legend>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_legend_output').'</legend>
    <div class="item item-crop-percent-size">
    	<label for=""input_crop__percent_size">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_percent').'</label>
        <input type="text" class="textInput" name="crop_percent_size" id="crop_percent_size" />
    </div>
    <div class="item item-convert-to">
    	<label for=""input_convert_to">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_convert').'</label>
		<select class="textInput" id="input_convert_to">
			<option value="gif">GIF</option>
			<option value="jpg" selected>JPG</option>
			<option value="png">PNG</option>
		</select>
    </div>
</fieldset>
<fieldset id="fdfx-be-image-crop-process">
	<legend>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_legend_process').'</legend>
    <div class="item item-preview">
    	<label for=""input_preview">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_preview').'</label>
		<input type="radio" class="textInput" name="store" id="input_preview" value="1" checked="checked" />
    </div>
    <div class="item item-store">
    	<label for=""input_store">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_label_store').'</label>
		<input type="radio" class="textInput" name="store" id="input_store" value="2" />
    </div>
    <div class="item crop-botton">
           <input type="button" class="button" onclick="cropScript_executeCrop(this)" value="'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_submit').'" />
    </div>
    <div id="crop_progressBar"></div>
</fieldset>
</form>
</div>

<script type="text/javascript">
setCHash("'.fdfx_image :: getEncryptionMd5($GLOBALS['TYPO3_CONF_VARS']["SYS"]["encryptionKey"], array('crop',$fileNameLocal)).'");
init_imageCrop();
</script>
'; 
		return $content;
	}
	function _getCropHeader()
	{
		global $BACK_PATH;
		
		$extPath = $BACK_PATH.t3lib_extMgm::extRelPath($this->extKey).'res/crop-image/';
		$imgObj = t3lib_div::makeInstance('t3lib_stdGraphic');
		$imgObj->init();
		$imgObj->mayScaleUp = 0;

			// Read Image Dimensions (returns false if file was not an image type, otherwise dimensions in an array)
		$imgInfo = '';
		$imgInfo = $imgObj->getImageDimensions($this->fileName);
		$width=$imgInfo[0];
		$height=$imgInfo[1];
		$content='';
		if ($width>$this->conf['MAX_WIDTH'] || $height>$this->conf['MAX_HEIGHT'])
		{
			//we scale the image to display it in BE
			$imgObj->tempPath = PATH_site.$imgObj->tempPath;
			$file=t3lib_div::getFileAbsFileName($this->fileName);
			$imgInfoNew=$imgObj->imageMagickConvert($file,'web','','',' -'.$this->conf['RESIZE_COMMAND'].' '.$this->conf['MAX_WIDTH'].'x'.$this->conf['MAX_HEIGHT'],'','',1);
			if (is_array($imgInfoNew))
			{
				$this->fileNameLocal=substr($imgInfoNew[3],strlen(PATH_site));
				$width=$imgInfoNew[0];
				$height=$imgInfoNew[1];
			}else{
				$content .='<script type="text/javascript">alert("'.$this->conf['RESIZE_COMMAND'].' is not accepted by ImageMagick. Please adjust!");</script>';
			}
		}
		$content .='
	<link rel="stylesheet" href="'.$extPath.'css/xp-info-pane.css">
	<link rel="stylesheet" href="'.$extPath.'css/image-crop.css">
	<script type="text/javascript" src="'.$extPath.'js/xp-info-pane.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/ajax.js"></script>
	<script type="text/javascript">
	var crop_script_server_file ="'.$BACK_PATH.t3lib_extMgm::extRelPath($this->extKey).'cm1/class.fdfx_image.php";
	
	var cropToolBorderWidth = 1;	// Width of dotted border around crop rectangle
	var smallSquareWidth = 7;	// Size of small squares used to resize crop rectangle
	
	// Size of image shown in crop tool
	var crop_imageWidth = '.$width.';
	var crop_imageHeight = '.$height.';
	
	// Size of original image
	var crop_originalImageWidth = '.$imgInfo[0].';
	var crop_originalImageHeight ='.$imgInfo[1].';
	
	var crop_minimumPercent = 10;	// Minimum percent - resize
	var crop_maximumPercent = 200;	// Maximum percent -resize
	
	
	var crop_minimumWidthHeight = 15;	// Minimum width and height of crop area
	
	var updateFormValuesAsYouDrag = true;	// This variable indicates if form values should be updated as we drag. This process could make the script work a little bit slow. That is why this option is set as a variable.
	if(!document.all)updateFormValuesAsYouDrag = false;	// Enable this feature only in IE
	
	/* End of variables you could modify */
	function setCHash(chash)
	{
		md5Hash=chash;
	}
	</script>
	<script type="text/javascript" src="'.$extPath.'js/image-crop.js"></script>
	<script type="text/javascript">
	extensionPath="'.$BACK_PATH.t3lib_extMgm::extRelPath($this->extKey).'res/crop-image/";
	</script>
';
		return $content;		
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/class.fdfx_image_basic.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/class.fdfx_image_basic.php']);
}
?>