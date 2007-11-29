<?php
if (!defined('TYPO3_MODE'))
	die('Access denied.');
if (TYPO3_MODE == "BE")
{
	//prs+ 10.12.2006
	// add DAM support
	if (t3lib_extMgm :: isLoaded('dam'))
	{
		t3lib_extMgm::insertModuleFunction(
			'txdamM1_cmd',
			'tx_fdfxbeimage_modfunc1',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/class.tx_fdfxbeimage_modfunc1.php',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/locallang.php:tx_fdfxbeimage_title'
		);
		tx_dam::register_action ('tx_fdfxbeimage_cropFile',	
			t3lib_extMgm::extPath($_EXTKEY).'class.tx_fdfxbeimage_cropFile.php:&tx_fdfxbeimage_cropFile',
			'after:tx_dam_action_viewFile');
	}
	else
	{
		$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][] = array (
			"name" => "tx_fdfxbeimage_cm1", 
			"path" => t3lib_extMgm :: extPath($_EXTKEY)."cm1/class.tx_fdfxbeimage_cm1.php"
			);
	}
		//prs- 10.12.2006
}
?>