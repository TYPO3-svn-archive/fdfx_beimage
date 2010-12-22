<?php
if (!defined('TYPO3_MODE'))
	die('Access denied.');
if (TYPO3_MODE == "BE")
{
	// add DAM support
	if (t3lib_extMgm :: isLoaded('dam'))
	{
		t3lib_extMgm::insertModuleFunction(
			'txdamM1_cmd',
			'tx_fdfxbeimage_modfunc1',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/class.tx_fdfxbeimage_modfunc1.php',
//			t3lib_extMgm::extPath($_EXTKEY).'cm1/locallang.xml:tx_fdfxbeimage_function1'
			'LLL:EXT:fdfx_be_image/cm1/locallang.xml:tx_fdfxbeimage_function1'
		);
		t3lib_extMgm::insertModuleFunction(
			'txdamM1_cmd',
			'tx_fdfxbeimage_modrotate',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/class.tx_fdfxbeimage_modrotate.php',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/locallang.xml:tx_fdfxbeimage_function2'
		);
		tx_dam::register_action ('tx_fdfxbeimage_rotateFile',
			t3lib_extMgm::extPath($_EXTKEY).'class.tx_fdfxbeimage_rotateFile.php:&tx_fdfxbeimage_rotateFile'
			,'top'
			);
		tx_dam::register_action ('tx_fdfxbeimage_cropFile',
			t3lib_extMgm::extPath($_EXTKEY).'class.tx_fdfxbeimage_cropFile.php:&tx_fdfxbeimage_cropFile'
			,'top'
			);
	}
	else
	{
		$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][] = array (
			"name" => "tx_fdfxbeimage_cm1",
			"path" => t3lib_extMgm :: extPath($_EXTKEY)."cm1/class.tx_fdfxbeimage_cm1.php"
			);
	}
}
?>