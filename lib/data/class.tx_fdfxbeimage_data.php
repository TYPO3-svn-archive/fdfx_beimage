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
class tx_fdfxbeimage_data {
	static protected $extKey = 'fdfx_be_image';
	static protected $tableName = 'tx_fdfxbeimage_dam_content_ref';

	static public function fetchFileList($uidListLocal,$uidForeign) {
		$where = 'uid_local IN ('. implode(',', $uidListLocal).') AND uid_foreign = ' . $uidForeign;
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid_local,filename,convertparams,originalparams'
				, self::$tableName
				, $where		
			);
		$result = array();
		foreach ($rows as $values) {
			$absFileName = t3lib_div::getFileAbsFileName($values['filename']);
			if (file_exists($absFileName)) {
				$result[$values['uid_local']] = $values['filename'];
			} else {
			}
		}
		return $result;			
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
if (defined ( 'TYPO3_MODE' ) && $TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/lib/data/class.tx_fdfxbeimage_data.php']) {
	include_once ($TYPO3_CONF_VARS [TYPO3_MODE] ['XCLASS'] ['ext/fdfx_be_image/lib/data/class.tx_fdfxbeimage_data.php']);
}
?>