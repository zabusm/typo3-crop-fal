<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

//$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_content.php']['getImgResource']['zabus_crop_fal'] = 'EXT:crop_fal/Classes/Backend/ContentObject.php:Tx_CropFal_Backend_ContentObject';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'] = array(
    'className' => 'Zabus\\CropFal\\Xclass\\ContentObjectRenderer',
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Resource\\Processing\\LocalCropScaleMaskHelper'] = array(
    'className' => 'Zabus\\CropFal\\Xclass\\LocalCropScaleMaskHelper',
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Frontend\\Imaging\\GifBuilder'] = array(
    'className' => 'Zabus\\CropFal\\Xclass\\GifBuilder',
);