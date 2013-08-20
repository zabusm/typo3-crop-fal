<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Crop Images');


if (TYPO3_MODE == 'BE') {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath('zabus_crop_fal', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'app/');
}


\TYPO3\CMS\Core\Utility\GeneralUtility::loadTCA('sys_file_reference');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_reference', array(
		'tx_zabus_crop_fal' => array (
				'exclude' => 1,
				'label' => 'Crop Image',
				'config' => array (
					'type' => 'user',
                	'size' => '30',
                	'userFunc' => 'Zabus\\CropFal\\Tca\\Wizard->showIcon',
				)
		),1
	));

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file_reference','tx_zabus_crop_fal');

$TCA['sys_file_reference']['palettes']['imageoverlayPalette']['showitem'].=",--linebreak--,tx_zabus_crop_fal";

//\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($TCA['sys_file_reference']['palettes']['imageoverlayPalette']['showitem']);
//var_dump($TCA['tt_content']['columns']['media']);
//exit();
//EXT:crop_fal/Classes/Tca/class.tx_examples_tca.php:tx_examples_tca-> specialField
?>