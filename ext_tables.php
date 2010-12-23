<?php
if (!defined('TYPO3_MODE'))	die('Access denied.');
if (TYPO3_MODE == "BE") {
	// add DAM support
	if (t3lib_extMgm::isLoaded('dam'))
	{
		t3lib_extMgm::insertModuleFunction(
			'txdamM1_cmd',
			'tx_fdfxbeimage_modcrop',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/class.tx_fdfxbeimage_modcrop.php',
			'LLL:EXT:fdfx_be_image/cm1/locallang.xml:tx_fdfxbeimage_function1'
		);
		t3lib_extMgm::insertModuleFunction(
			'txdamM1_cmd',
			'tx_fdfxbeimage_modrotate',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/class.tx_fdfxbeimage_modrotate.php',
			t3lib_extMgm::extPath($_EXTKEY).'cm1/locallang.xml:tx_fdfxbeimage_function2'
		);
		tx_dam::register_action ('tx_fdfxbeimage_rotateFile',
			t3lib_extMgm::extPath($_EXTKEY).'lib/action/class.tx_fdfxbeimage_rotateFile.php:&tx_fdfxbeimage_rotateFile'
			,'top'
			);
		tx_dam::register_action ('tx_fdfxbeimage_cropFile',
			t3lib_extMgm::extPath($_EXTKEY).'lib/action/class.tx_fdfxbeimage_cropFile.php:&tx_fdfxbeimage_cropFile'
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
	
	if (t3lib_extMgm::isLoaded('dam_ttcontent')) {
		$TCA['tt_content']['columns']['tx_damttcontent_files']['config']['wizards'] = array(
			'_PADDING' => 1,
			'_VERTICAL' => 1,
				'crop' => array(
					'type' => 'popup',
					'title' => 'Crop image',
					'script' => 'EXT:fdfx_be_image/cm1/wizard_crop.php',
					'icon' => 'EXT:fdfx_be_image/res/cm_icon_crop.png',
					'popup_onlyOpenIfSelected' => 1,
					'JSopenParams' => 'height=350,width=350,status=0,menubar=0,scrollbars=1',
				),
		);
	}
	
}
t3lib_extMgm::addStaticFile($_EXTKEY,'res/static/dam_ttcontent/', 'fdfx_be_image:dam');
?>