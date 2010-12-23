<?php
	$extPath = t3lib_extMgm::extPath('fdfx_be_image');
	$autoLoadArray = Array(
		  'tx_fdfxbeimage_cropFile'		=> $extPath . 'lib/action/class.tx_fdfxbeimage_cropFile.php'
		, 'tx_fdfxbeimage_rotateFile'	=> $extPath . 'lib/action/class.tx_fdfxbeimage_rotateFile.php'
		, 'tx_fdfxbeimage_image'		=> $extPath . 'cm1/class.tx_fdfxbeimage_image.php'
		, 'tx_fdfxbeimage_image_basic'	=> $extPath . 'lib/class.tx_fdfxbeimage_image_basic.php'
		, 'tx_fdfxbeimage_data'			=> $extPath . 'lib/data/class.tx_fdfxbeimage_data.php'
		, 'tx_fdfxbeimage_image_crop'	=> $extPath . 'lib/class.tx_fdfxbeimage_image_crop.php'
		, 'tx_fdfxbeimage_image_rotate'	=> $extPath . 'lib/class.tx_fdfxbeimage_image_rotate.php'
		, 'tx_fdfxbeimage_modcrop'		=> $extPath . 'cm1/class.tx_fdfxbeimage_modcrop.php'
		
	);
	
	return $autoLoadArray;
?>