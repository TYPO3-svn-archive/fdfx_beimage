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
#   Date:       10.12.2006
#   Filename:   class.tx_fdfxbeimage_modfunc1.php
#
#   Project:    fdfx_be_image
#
##################################################

require_once(PATH_t3lib.'class.t3lib_basicfilefunc.php');
require_once(PATH_txdam.'lib/class.tx_dam_guifunc.php');

require_once(PATH_t3lib.'class.t3lib_extobjbase.php');

require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');
require_once(t3lib_div::getFileAbsFileName('EXT:fdfx_be_image/cm1/class.fdfx_image.php'));
require_once(t3lib_div::getFileAbsFileName('EXT:fdfx_be_image/lib/class.fdfx_image_basic.php'));

class tx_fdfxbeimage_modfunc1 extends t3lib_extobjbase 
{
	var $imgObj;
	var $extKey='fdfx_be_image';

	function _init()
	{
		$this->imgObj=t3lib_div::makeInstance('fdfx_Image_Basic');
		$this->fileName=t3lib_div::_GP('file');	
		$this->imgObj->_init($this->extKey,$this->fileName);
	}
	function accessCheck()
	{
		return tx_dam::access_checkAction('editFile');
	}
	function head() 
	{
		$GLOBALS['SOBE']->pageTitle = $GLOBALS['LANG']->getLL('tx_fdfxbeimage_title');
	}
	function main()	{
		$this->_init();
		$this->pObj->doc->JScode .= $this->imgObj->_getCropHeader();
			
		$content=$GLOBALS['LANG']->getLL('tx_fdfxbeimage_crop_text');
		$content .= $this->imgObj->_getCropContent();
		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/cm1/class.tx_fdfxbeimage_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/cm1/class.tx_fdfxbeimage_modfunc1.php']);
} 
?>
