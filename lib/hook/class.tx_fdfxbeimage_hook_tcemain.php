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
 * @version:		$Rev: 41754 $
 * @package:		TYPO3
 * @subpackage:		fdfx_be_image
 *
 */

class tx_fdfxbeimage_hook_tcemain{
	/**
	 * Hook: processDatamap_afterDatabaseOperations
	 * 
	 * removes images from processing list that are not used any longer
	 *
	 * Note: When using the hook after INSERT operations, you will only get the temporary NEW... id passed to your hook as $id,
	 *		 but you can easily translate it to the real uid of the inserted record using the $this->substNEWwithIDs array.
	 *
	 * @param	string		$status: (reference) Status of the current operation, 'new' or 'update
	 * @param	string		$table: (refrence) The table currently processing data for
	 * @param	string		$id: (reference) The record uid currently processing data for, [integer] or [string] (like 'NEW...')
	 * @param	array		$fieldArray: (reference) The field array of a record
	 * @param	t3lib_TCEmain tcemain object reference
	 * @return	void
	 */
	
	public function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, t3lib_TCEmain &$tceMain) {
		//we need an valid id and dam ref
		if ($status === 'update' && isset($fieldArray['tx_damttcontent_files']) && $table === 'tt_content'){
			$allowedUids = $this->getAllowedUids($id);
			$this->removeImagesFromProcessList($id, $allowedUids);
		}
	}
	
	protected function getAllowedUids($uidForeign) {
		$allowedUids = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'GROUP_CONCAT(uid_local) as allowed_uids'
			, 'tx_dam_mm_ref'
			, 'uid_foreign = ' . (int)$uidForeign
			, 'uid_foreign' 
		);
		if ($result) {
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
			$allowedUids = $row['allowed_uids'];
			unset($row, $result);
		}
		return $allowedUids;
	}
	
	protected function removeImagesFromProcessList($id, $allowedUids = '') {
		if ($id > 0) {
			$and = empty($allowedUids)? '' : ' AND NOT(uid_local in (' . $allowedUids. '))';
			$GLOBALS['TYPO3_DB']->exec_DELETEquery(
				'tx_fdfxbeimage_dam_content_ref'
				, 'uid_foreign = ' . $id . $and
			);
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/hook/class.tx_fdfxbeimage_hook_tcemain.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/lib/hook/class.tx_fdfxbeimage_hook_tcemain.php']);
}

?>