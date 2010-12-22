<?php
	$extPath = t3lib_extMgm::extPath('fdfx_be_image');
	$autoLoadArray = Array(
		'tx_dam_actionbase' 			=> PATH_txdam . 'lib/class.tx_dam_actionbase.php'
		, 'tx_fdfxbeimage_cropFile'		=> $extPath . 'lib/action/class.tx_fdfxbeimage_cropFile.php'
		, 'tx_fdfxbeimage_rotateFile'	=> $extPath . 'lib/action/class.tx_fdfxbeimage_rotateFile.php'
		, 'fdfx_image'					=> $extPath . 'cm1/class.fdfx_image.php'
		, 'fdfx_image_basic'			=> $extPath . 'lib/class.fdfx_image_basic.php'
		, 'fdfx_image_crop'				=> $extPath . 'lib/class.fdfx_image_crop.php'
		, 'fdfx_image_rotate'			=> $extPath . 'lib/class.fdfx_image_rotate.php'
		
	);
	
	return $autoLoadArray;
?>