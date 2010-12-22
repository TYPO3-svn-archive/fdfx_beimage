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
class fdfx_Image_Rotate extends fdfx_Image_Basic
{
	function _getContent()
	{
		global $BACK_PATH;

		$content='';
		$options='';
		$optionLines=explode(',',$this->conf['FIXED_SIZE']);
		foreach($optionLines as $line)
		{
			list($value,$option)=explode('=',$line);
			$options .='<option value="'.$value.'">'.$option.'</option>';
		}
		$fI = t3lib_div::split_fileref($this->fileName);
		$fileNameLocal=substr($this->fileName,strlen(PATH_site));
		$fileName=t3lib_div::isFirstPartOfStr($this->fileName,PATH_site)?'../../../../'.(($this->fileNameLocal)?$this->fileNameLocal:$fileNameLocal):$fI['file'];
$content='
<div id="pageContent">
'.$this->btn_back('',$this->returnUrl).'
<div id="controls">
	<fieldset id="modes">
		<legend>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_rotate_modes').'</legend>
		<form name="formAction" id="formAction" method="post" action="ajax_image_undo.php">
			<input type="hidden" name="file_path" id="file_path" value="'.$fileName.'" />

			<p>
			<div style="display:none;"><label>Rotate:</label> <input type="radio" name="mode" value="rotate" class="input" checked="checked" onclick="return changeMode();" />
			<label>Flip:</label> <input type="radio" name="mode" value="flip" class="input" onclick="return changeMode();" /></div>

			<button id="actionRotateLeft" class="disabledButton" onclick="return leftRotate();" disabled>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_rotate_ccw').'</button>
			<button id="actionRotateRight" class="disabledButton" onclick="return rightRotate();" disabled>'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_rotate_cw').'</button>
		</form>
		<form name="formImageInfo" action="'.$BACK_PATH.t3lib_extMgm::extRelPath($this->extKey).'cm1/class.fdfx_image.php" method="post" id="formImageInfo">
			<input type="hidden" name="cmd" id="image_mode" value="rotate" />
			<input type="hidden" name="store" id="image_mode" value="2" />
			<input type="hidden" name="chash" id="image_chash" value="2" />
			<input type="hidden" name="image_ref" id="path" value="'.$fileNameLocal.'"  />
			<input type="hidden" name="angle" id="angle" value="" />
			<button id="actionReset" class="button" onclick="return resetEditor();">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_rotate_reset').'</button>
			<button id="actionSave" class="button"  onclick="return saveImage();">'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_rotate_save').'</button>
		</form>
	</fieldset>
	<img id="loading" style="display:none;" src="'.$BACK_PATH.t3lib_extMgm::extRelPath($this->extKey).'res/crop-image/images/loading.gif" />
</div>
<div id="imageArea">
    <div id="imageContainer">
    	<img src="'.$fileName.'" />
    </div>
</div>
<div id="hiddenImage">
</div>
</div>

<script type="text/javascript">
setCHash("'.fdfx_image :: getEncryptionMd5($GLOBALS['TYPO3_CONF_VARS']["SYS"]["encryptionKey"], array('rotate',$fileNameLocal)).'");
</script>
';
		if (t3lib_extMgm :: isLoaded('dam'))
		{
			//kill dam form
			$content='</form>'.$content."<form>";
		}

		return $content;
	}
	function _getHeader()
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
	<link rel="stylesheet" href="'.$extPath.'css/xp-info-pane.css"/>
	<link rel="stylesheet" href="'.$extPath.'css/image-all.css" />
	<link rel="stylesheet" href="'.$extPath.'css/image-rotate.css" />
	<script type="text/javascript" src="'.$extPath.'js/jquery-1.2.1.pack.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/jq-form.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/jq-rotate.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/jq-iutil.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/jq-iresizable.js"></script>
	<script type="text/javascript" src="'.$extPath.'js/rotate.js"></script>
	<script type="text/javascript">
	var imageHistory = false;
	var warningLostChanges = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningLostChanges').'";
	var warningReset = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningReset').'";
	var warningResetEmpty = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningResetEmpty').'";
	var warningEditorClose = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningEditorClose').'";
	var warningUndoImage = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningUndoImage').'";
	var warningFlipHorizotal = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningFlipHorizotal').'";
	var warningFlipVertical = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_warningFlipVertical').'";
	var numSessionHistory = 0;
	var noChangeMadeBeforeSave = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_noChangeMadeBeforeSave').'";
	var imagesaved = "'.$GLOBALS['LANG']->getLL('tx_fdfxbeimage_imageSaved').'";
	$(document).ready(
		function()
		{
			$("#image_mode").val("");
			$("#angle").val(0);
			$(getImageElement()).clone().appendTo("#hiddenImage");
			changeMode();
			initDisabledButtons(true);
		}
	);
	/* End of variables you could modify */
	function setCHash(chash)
	{
		md5Hash=chash;
		document.getElementById("image_chash").value=chash;
	}
	</script>
	<script type="text/javascript">
	extensionPath="'.$BACK_PATH.t3lib_extMgm::extRelPath($this->extKey).'res/crop-image/";
	</script>
';
		return $content;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/class.fdfx_image_rotate.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/class.fdfx_image_rotate.php']);
}
?>