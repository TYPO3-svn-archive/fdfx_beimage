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
#   Date:       21.07.2006
#   Filename:   class.fdfx_image.php
#
#   Project:    project_name
#
##################################################

class fdfx_image
{
	var $cmd = '';
	var $params = array ();
	var $content = '';
	var $conf = array ();
	var $preview = false;
	var $store = false;
	var $continueIt = false;
	var $encryptionKey = '';
	var $backPath='../../../';
	var $errorMsg='';
	var $error='';
	function _init($extKey='fdfx_be_image')
	{
		$this->extKey=$extKey;
		$this->cmd = strtolower(t3lib_div :: _GP('cmd'));
		$this->preview = (t3lib_div :: _GP('preview') == 1);
		$this->store = (!$this->preview && (t3lib_div :: _GP('store') == 2));
		if (!$this->store)
		{
			$this->preview=true;
		}
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
		$this->encryptionKey = $GLOBALS['TYPO3_CONF_VARS']["SYS"]["encryptionKey"];
		$userConf = $GLOBALS['BE_USER']->getTSConfig(strtoupper($extKey));
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

			if (isset ($userConf['properties']['samePath']))
			{
				$this->conf['SAME_PATH'] = 1 && $userConf['properties']['samePath'];
			}
			if (isset ($userConf['properties']['isAbsolute']))
			{
				$this->conf['IS_ABSOLUTE'] = 1 && $userConf['properties']['isAbsolute'];
			}
			if (isset ($userConf['properties']['newPath']) && $userConf['properties']['newPath'] !='')
			{
				$this->conf['NEW_PATH'] = $userConf['properties']['newPath'];
			}

			if (isset($userConf['properties']['fixedSize']) && $userConf['properties']['fixedSize']!='')
			{
				$this->conf['FIXED_SIZE'] = $userConf['properties']['fixedSize'];
			}
			if (isset($userConf['properties']['fixedSizeDefault']) && $userConf['properties']['fixedSizeDefault']!='')
			{
				$ar=explode(',',$this->conf['FIXED_SIZE']);
				if (intval($userConf['properties']['fixedSizeDefault'])<=count($ar) && $userConf['properties']['fixedSizeDefault'])
				{
					$this->conf['FIXED_SIZE_DEFAULT'] = $userConf['properties']['fixedSizeDefault'];
				}
			}

		}
	}
	function _checkMd5($checkArr = array ())
	{
		$isOk = false;
		if ($md5 = t3lib_div :: _GP('chash'))
		{
			$ar = array ();
			foreach ($checkArr as $key)
			{
				$ar[] = $this->params[$key];
			}
			$md5Check=fdfx_image :: getEncryptionMd5($this->encryptionKey, $ar);
			$isOk = ($md5 == $md5Check);
		}
		$this->error=($isOk)?'':'MD5 error in hash! No valid access. MD5 was '.$md5.' and should be '.$md5Check;
		return $isOk;
	}
	/*
	 * Return false if md5 hash is not correct, i.e. values are send by "hand
	 * hack"
	 */
	function _getParams($para = array (), $checkArr = array ())
	{
		$this->params = array ();
		foreach ($para as $par)
		{
			$this->params[$par] = t3lib_div :: _GP($par);
		}
		return $this->_checkMd5($checkArr);
	}
	function init()
	{
		$this->_init();
		switch ($this->cmd)
		{
			case 'crop' :
				$params = array ('cmd', 'x', 'y', 'width', 'height', 'image_ref', 'percentSize', 'convertTo');
				$checkArray = array ('cmd', 'image_ref');
				$this->continueIt = $this->_getParams($params, $checkArray);
				break;
			case 'rotate':
				$params = array ('cmd', 'angle', 'image_ref');
				$checkArray = array ('cmd', 'image_ref');
				$this->continueIt = $this->_getParams($params, $checkArray);
				break;
			default :
				break;
		}
	}
	function _initExtFileFunc()
	{
		global $FILEMOUNTS,$TYPO3_CONF_VARS,$BE_USER;

		$fileProcessor = t3lib_div::makeInstance('t3lib_extFileFunctions');
		$fileProcessor->init($FILEMOUNTS, $TYPO3_CONF_VARS['BE']['fileExtensions']);
		$fileProcessor->init_actionPerms($BE_USER->user['fileoper_perms']);
		return $fileProcessor;
	}
	function _storeImage($fileNamePrefix='',$dir,$imgInfo=array(),&$basicFF=null)
	{
		if ($basicFF==null)
		{
			$basicFF=t3lib_div::makeInstance('t3lib_basicFileFunctions');
		}
		$newFileName=$fileNamePrefix.$imgInfo[0].'x'.$imgInfo[1].'.'.$imgInfo[2];
		$newFilePath=$basicFF->getUniqueName($newFileName,$dir);
		t3lib_div::upload_copy_move($imgInfo[3],$newFilePath);
		unlink($imgInfo[3]);
		$imgInfo[3] = substr($newFilePath,strlen(PATH_site));
		return $imgInfo;
	}
	function _getDirname($file='',&$extFF=null)
	{
		$dirname=false;
		if ($extFF==null)
		{
			$extFF=$this->_initExtFileFunc();
		}
		if ($this->conf['SAME_PATH'])
		{
			$dirname=dirname($file);
		}
		elseif ($this->conf['IS_ABSOLUTE'])
		{
			if (t3lib_div::isAllowedAbsPath($this->conf['NEW_PATH']))
			{
				$dirname=$this->conf['NEW_PATH'];
			}
			else
			{
				$this->errorMsg=$this->getMsg('error_absolute_path_notaccessable',array($this->conf['NEW_PATH']));
			}
		}
		else
		{
			$data=array(
				'data' => $this->conf['NEW_PATH'],
				'target'=> dirname($file),
			);
			$dirname=$data['target'].'/'.$data['data'];
			if (!file_exists($dirname))
			{
				$dirname=$extFF->func_newfolder($data);
			}
			if (!$dirname)
			{
				$this->errorMsg=$this->getMsg('error_relative_folder_creation',array($this->conf['NEW_PATH'],$data['target']));
			}
		}
		return $dirname;
	}
	function _sessionSaveFilename($filename)
	{
		$array=$GLOBALS['BE_USER']->getSessionData($this->extKey);
		$array['fileName']=$filename;
		$GLOBALS['BE_USER']->setAndSaveSessionData($this->extKey,$array);
	}
	function _imageRotate()
	{
		$angle=preg_replace("/[^0-9]/si", "", $this->params['angle'])%360;
		if ($angle)
		{
			$angle -=($angle==180)?0:180;
			$imgObj = t3lib_div :: makeInstance('t3lib_stdGraphic');
			$imgObj->init();
			$imgObj->mayScaleUp = 0;
			$imgObj->tempPath = PATH_site.$imgObj->tempPath;
			$file=t3lib_div::getFileAbsFileName($this->params['image_ref']);
			$fI=pathinfo($file);
			$imgObj->filenamePrefix=basename($file,'.'.$fI['extension']).'.';
			$imgInfo = $imgObj->getImageDimensions($file);
			if ($imgInfo)
			{
				$convertParamAdd = " -rotate ".$angle;
				$imgInfoNew = $imgObj->imageMagickConvert($file,'','','',$convertParamAdd,'','',1);
				$extFF=$this->_initExtFileFunc();
				$dirName=$this->_getDirname($file,$extFF);
				if ($dirName)
				{
					// saved
					$imgInfoNew=$this->_storeImage('rotate.'.$angle.'.'.$imgObj->filenamePrefix,$dirName,$imgInfoNew,$extFF);
					if ($imgInfoNew[0]>$this->conf['MAX_WIDTH'] || $imgInfoNew[1]>$this->conf['MAX_HEIGHT'])
					{
						//we scale the image to display it in BE
						$filenameOrg=$imgInfoNew[3];
						$file=t3lib_div::getFileAbsFileName($imgInfoNew[3]);
						$imgInfoNew=$imgObj->imageMagickConvert($file,'web','','',' -'.$this->conf['RESIZE_COMMAND'].' '.$this->conf['MAX_WIDTH'].'x'.$this->conf['MAX_HEIGHT'],'','',1);
						if (is_array($imgInfoNew))
						{
							$this->fileNameLocal=substr($imgInfoNew[3],strlen(PATH_site));
							$width=$imgInfoNew[0];
							$height=$imgInfoNew[1];
						}
						$this->_sessionSaveFilename($file);
					}
				}
				else
				{
					$this->error =$this->getMsg('error').$this->errorMsg;
				}
				$this->content .= "{";
				$this->content .= "error:'".$this->error."'\n";
				$this->content .= ",image_ref:'".$filenameOrg."'\n";
				$this->content .= ",path:'".$this->backPath.'../'.substr($imgInfoNew[3],strlen(PATH_site))."'\n";
				$this->content .= ",width:".$imgInfoNew[0]."\n";
				$this->content .= ",height:".$imgInfoNew[1]."\n";
				$this->content .= ",chash:'".fdfx_image :: getEncryptionMd5($this->encryptionKey, array('rotate',$filenameOrg))."'\n";
				$this->content .= ",history:0\n";
				$this->content .= "}";
			} else {
				$this->content .= "{";
				$this->content .= "error:'".$file."'\n";
				$this->content .= "}";
			}
		}
	}
	function _imageCrop()
	{
		$x = preg_replace("/[^0-9]/si", "", $this->params['x']);
		$y = preg_replace("/[^0-9]/si", "", $this->params['y']);
		$width = preg_replace("/[^0-9]/si", "", $this->params['width']);
		$height = preg_replace("/[^0-9]/si", "", $this->params['height']);
		if (!$this->params['percentSize'])
		{
			$this->params['percentSize']=100;
		}
		$percentSize = preg_replace("/[^0-9.]/si", "", $this->params['percentSize']);
		if ($percentSize > 200)
		{
			$percentSize = 200;
		}
		if (strlen($x) && strlen($y) && $width && $height && $percentSize)
		{
			$convertParamAdd = "";
			if ($percentSize != "100")
			{
				$convertParamAdd = " -resize ".$percentSize."x".$percentSize."%";
				$x = $x * ($percentSize / 100);
				$y = $y * ($percentSize / 100);
				$width = $width * ($percentSize / 100);
				$height = $height * ($percentSize / 100);
			}
			$imgObj = t3lib_div :: makeInstance('t3lib_stdGraphic');
			$imgObj->init();
			$imgObj->mayScaleUp = 0;
			$imgObj->tempPath = PATH_site.$imgObj->tempPath;
			$file=t3lib_div::getFileAbsFileName($this->params['image_ref']);
			$fI=pathinfo($file);
			$imgObj->filenamePrefix=basename($file,'.'.$fI['extension']).'.';
			$imgInfo = $imgObj->getImageDimensions($file);
			if ($imgInfo)
			{
				$convertParamAdd .= ' -crop '.$width.'x'.$height.'+'.$x.'+'.$y;
				$imgInfoNew = $imgObj->imageMagickConvert($file,$this->params['convertTo'],'','',$convertParamAdd,'','',1);
				if ($this->preview)
				{
					$imgInfoNew[3] = $this->backPath.'../'.substr($imgInfoNew[3],strlen(PATH_site));
					$this->content .="var w = window.open('".$imgInfoNew[3]."','imageWin','width=". ($imgInfoNew[0] +30).",height=". ($imgInfoNew[1] +30).",resizable=yes');";
				} elseif ($this->store) {
					$extFF=$this->_initExtFileFunc();
					$dirName=$this->_getDirname($file,$extFF);
					if ($dirName)
					{
						$saveImgInfo=$this->_storeImage('crop.'.$imgObj->filenamePrefix,$dirName,$imgInfoNew,$extFF);
//						$this->content .= "alert('Image saved to ".$saveImgInfo[3]."')";
						$this->content .= "alert('".$this->getMsg('success_image_saved',array($saveImgInfo[3]))."');";
					}
					else
					{
						$this->content .="alert('".$this->getMsg('error').$this->errorMsg."');";
					}
				}
			}
			else
			{
				$this->content .="alert('".$this->getMsg('error_no_image_info')."');";
			}
		}
		else
		{
			$this->content .="alert('".$this->getMsg('error')."');";
		}
	}
	function getMsg($key='',$ar=array())
	{
		$msg=$GLOBALS['LANG']->getLL('tx_fdfxbeimage_'.$key);
		if (count($ar)>0)
		{
			for ($i=0;$i<count($ar);$i++)
			{
				$rep='%'.($i+1);
				$msg=str_replace($rep,$ar[$i],$msg);
			}
		}
		return $msg;
	}
	function main()
	{
		if ($this->continueIt)
		{
			switch ($this->cmd)
			{
				case 'crop' :
					$this->_imageCrop();
					break;
				case 'rotate':
					$this->_imageRotate();
					break;
				default :
					break;
			}
		}
	}
	function printContent()
	{
		echo $this->content;
	}
	/*
	 * STATICS
	 */
	function getEncryptionMd5($key = '', $arr = array ())
	{
		return substr(md5($key.join('', $arr)), 0, 10);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/class.fdfx_image.php'])
{
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/class.fdfx_image.php']);
}
// Make instance:
if (isset($_GET['cmd']))
{
	require_once ('conf.php');
	require_once ($BACK_PATH.'init.php');
	require_once (PATH_t3lib.'class.t3lib_stdgraphic.php');
	require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
	require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');
	require_once(PATH_typo3.'sysext/lang/lang.php');
	$LANG = t3lib_div::makeInstance('language');
	$LANG->init($BE_USER->uc['lang']);
	$LANG->includeLLFile('EXT:fdfx_be_image/cm1/locallang.xml');
	$SOBE = t3lib_div :: makeInstance('fdfx_image');
	$SOBE->init();
	if ($SOBE->continueIt)
	{
		//got valid values, no manual hack attack
		$SOBE->main();
		$SOBE->printContent();
	}
}
if (isset($_POST['cmd']))
{
	require_once ('conf.php');
	require_once ($BACK_PATH.'init.php');
	require_once (PATH_t3lib.'class.t3lib_stdgraphic.php');
	require_once (PATH_t3lib.'class.t3lib_basicfilefunc.php');
	require_once (PATH_t3lib.'class.t3lib_extfilefunc.php');
	require_once(PATH_typo3.'sysext/lang/lang.php');
	$LANG = t3lib_div::makeInstance('language');
	$LANG->init($BE_USER->uc['lang']);
	$LANG->includeLLFile('EXT:fdfx_be_image/cm1/locallang.xml');
	$SOBE = t3lib_div :: makeInstance('fdfx_image');
	$SOBE->init();
	if ($SOBE->continueIt)
	{
		//got valid values, no manual hack attack
		$SOBE->main();
		$SOBE->printContent();
	} else {
		echo "{";
		echo "error:'".$SOBE->error."'\n";
		echo "}";
	}
}
?>