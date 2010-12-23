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
class tx_fdfxbeimage_Image_Basic {
	protected $fileName = '';
	protected $fileNameLocal = '';
	protected $conf = array ();
	static protected $extKey = 'fdfx_be_image';
	static protected $tableName = 'tx_fdfxbeimage_dam_content_ref';
	protected $returnUrl = '';
	protected $docHeaderButtons = false;
	
	public function _init($extKey = 'fdfx_be_image', $fName = '') {
		if ($fName != '') {
			$this->setFileName ( $fName );
		}
		$userConf = $GLOBALS ['BE_USER']->getTSConfig ( strtoupper ( $extKey ) );
		$this->conf = unserialize ( $GLOBALS ['TYPO3_CONF_VARS'] ['EXT'] ['extConf'] [$extKey] );
		if (isset ( $userConf ['properties'] ) && is_array ( $userConf ['properties'] )) {
			if (isset ( $userConf ['properties'] ['maxWidth'] ) && $userConf ['properties'] ['maxWidth'] > 0) {
				$this->conf ['MAX_WIDTH'] = $userConf ['properties'] ['maxWidth'];
			}
			if (isset ( $userConf ['properties'] ['maxHeight'] ) && $userConf ['properties'] ['maxHeight'] > 0) {
				$this->conf ['MAX_HEIGHT'] = $userConf ['properties'] ['maxHeight'];
			}
			if (isset ( $userConf ['properties'] ['fixedSize'] ) && $userConf ['properties'] ['fixedSize'] != '') {
				$this->conf ['FIXED_SIZE'] = $userConf ['properties'] ['fixedSize'];
			}
		}
		$this->returnUrl = t3lib_div::_GP ( 'returnUrl' );
	}
	
	public function btn_back($params = array(), $absUrl = '') {
		global $LANG, $BACK_PATH;
		if (t3lib_div::compat_version ( '4.2' ) && t3lib_extMgm::isLoaded ( 'dam' )) {
			$this->docHeaderButtons = true;
		} else {
			if ($absUrl) {
				$url = t3lib_extMgm::isLoaded ( 'dam' ) ? $absUrl : "javascript:top.goToModule('file_list');";
			} else {
				$url = "javascript:top.goToModule('file_list');";
			}
			
			$content = '<a href="' . htmlspecialchars ( $url ) . '" class="typo3-goBack">' . '<img' . t3lib_iconWorks::skinImg ( $BACK_PATH, 'gfx/goback.gif"', 'width="14" height="14"' ) . ' class="absmiddle" alt="" /> ' . $LANG->sL ( 'LLL:EXT:lang/locallang_core.xml:labels.goBack', 1 ) . '</a>';
		}
		return $content;
	}
	
	public function getDocHeaderButtons() {
		return $this->docHeaderButtons;
	}
	
	public function setFileName($fName) {
		if (file_exists ( $fName )) {
			$this->fileName = $fName;
		}
	}
	
	public function getFileName() {
		return $this->fileName;
	}

	static public function addStoredParamsFromDb(&$sessionData,$uid_local,$uid_foreign) {
		$sessionData['storedParams'] = false;
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			  'convertparams,originalparams'
			  , self::$tableName
			  , 'uid_local =' . $uid_local . ' and uid_foreign = ' . $uid_foreign
		);
		if ($result && $GLOBALS['TYPO3_DB']->sql_num_rows($result) > 0) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			if (!empty($row['convertparams']) && !empty($row['originalparams'])) {
				$row['originalparams'] = unserialize($row['originalparams']);
				$sessionData['storedParams'] = $row;
				unset ($row, $result);
			}
		}
	}
	
	static public function getValueFromSession(&$sessionData,$attribute,$action='crop') {
		$value = '';
		if (is_array($sessionData['storedParams']) && $sessionData['storedParams']['originalparams']['cmd'] === $action) {
			$value = $sessionData['storedParams']['originalparams'][$attribute];
		}
		return $value;
	}
	
	static public function saveStoredParamsToDb($sessionData, $fileName, $convertParams, $originalParams) {
		$data = array(
			  'cruser_id' => $GLOBALS['BE_USER']->user['uid']
			, 'uid_local' => intval($sessionData['uid_local'])
			, 'uid_foreign' => intval($sessionData['uid_foreign'])
			, 'filename' => $fileName
			, 'convertparams' => $convertParams
			, 'originalparams' => serialize($originalParams)
			, 'crdate' => $GLOBALS['EXEC_TIME']
			, 'tstamp' => $GLOBALS['EXEC_TIME']
		);
		if (is_array($sessionData['storedParams'])) {
			// we have to update existing record
			$where = 'uid_local =' . $data['uid_local'] . ' and uid_foreign = ' . $data['uid_foreign'];
			unset($data['uid_local'],$data['uid_foreign'],$data['crdate']);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
				self::$tableName
				, $where
				, $data
			);
						
		} else {
			// we have to inset record
			$GLOBALS['TYPO3_DB']->exec_INSERTquery(
					self::$tableName
					, $data
			);
		}
		$sessionData['storedParams'] = array(
			'convertparams' => $convertParams
			, 'originalparams' => $originalParams
		);
		self::sessionSave($sessionData);
	}
	
	static public function sessionSave($sessionData) {
		$array = $GLOBALS ['BE_USER']->getSessionData ( self::$extKey );
		if (!is_array($array)) {
			$array = array();
		}
		$array = array_merge($array, $sessionData);
		$GLOBALS ['BE_USER']->setAndSaveSessionData ( self::$extKey, $array );
	}
	
	static public function sessionGet() {
		return $GLOBALS ['BE_USER']->getSessionData ( self::$extKey );
	}
	
}
if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/lib/class.tx_fdfxbeimage_Imagebasic.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/lib/class.tx_fdfxbeimage_Imagebasic.php']);
}
?>