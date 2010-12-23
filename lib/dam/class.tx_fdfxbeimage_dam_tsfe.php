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
 * @subpackage:		fdfx_be_image
 * 
 */

require_once t3lib_extMgm::extPath('dam') . 'lib/class.tx_dam_db.php';

class tx_fdfxbeimage_dam_tsfe{




	/**********************************************************
	 *
	 * TypoScript functions
	 *
	 **********************************************************/


	/**
	 * Used to fetch a file list for TypoScript cObjects
	 *
	 *	tt_content.textpic.20.imgList >
	 *	tt_content.textpic.20.imgList.cObject = USER
	 *	tt_content.textpic.20.imgList.cObject {
	 *		userFunc = tx_dam_divFe->fetchFileList
	 *
	 * @param	mixed		$content: ...
	 * @param	array		$conf: ...
	 * @return	string		comma list of files with path
	 * @see dam_ttcontent extension
	 */
	function fetchFileList($content, $conf) {
		$files = array();

		$filePath = $this->cObj->stdWrap($conf['additional.']['filePath'], $conf['additional.']['filePath.']);
		$fileList = trim($this->cObj->stdWrap($conf['additional.']['fileList'], $conf['additional.']['fileList.']));
		$refField = trim($this->cObj->stdWrap($conf['refField'], $conf['refField.']));
		$fileList = t3lib_div::trimExplode(',', $fileList);
		foreach ($fileList as $file) {
			if($file) {
				$files[] = $filePath . $file;
			}
		}

		$uid      = $this->cObj->data['_LOCALIZED_UID'] ? $this->cObj->data['_LOCALIZED_UID'] : $this->cObj->data['uid'];
		$refTable = ($conf['refTable'] && is_array($GLOBALS['TCA'][$conf['refTable']])) ? $conf['refTable'] : 'tt_content';

		if (isset($GLOBALS['BE_USER']->workspace) && $GLOBALS['BE_USER']->workspace !== 0) {
			$workspaceRecord = $GLOBALS['TSFE']->sys_page->getWorkspaceVersionOfRecord(
				$GLOBALS['BE_USER']->workspace,
				$refTable,
				$uid,
				'uid'
			);

			if (is_array($workspaceRecord)) {
				$uid = $workspaceRecord['uid'];
			}
		}

		$damFiles = tx_dam_db::getReferencedFiles($refTable, $uid, $refField);
		
		// Now we check if we have cropped images
		
		$this->updateProcessedImages($damFiles);

		$files = array_merge($files, $damFiles['files']);

		return implode(',', $files);
	}
	
	protected function updateProcessedImages($damFiles) {
		t3lib_div::debug($damFiles, __FILE__.':'.__FUNCTION__."\t".__LINE__ );
	} 

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/dam/class.tx_fdfxbeimage_dam_tsfe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/dam/class.tx_fdfxbeimage_dam_tsfe.php']);
}

?>