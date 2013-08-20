<?php
class Tx_CropFal_Backend_ContentObject 
	implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectGetImageResourceHookInterface
{
	public function getImgResourcePostProcess($file, array $configuration, array $imageResource, \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer $parent)
	{
		/*\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($file);
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($configuration);
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($imageResource);
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($parent->data);
		*/
		
		if(isset($configuration['import.']['current']) && $configuration['import.']['current'] == '1')
		{
			$contentId = $parent->data['uid'];
			$splitCurrentRecord = explode(':',$parent->currentRecord);
			$tableName = $splitCurrentRecord[0];
			
			$select_fields = "tx_zabus_crop_fal";
			$from_table = "sys_file_reference";
			$where_clause = "uid_local=".$file." AND tablenames='".$tableName."' AND uid_foreign=".$contentId;
			
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where_clause);
			
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			
			$aspect = explode(',',$row['tx_zabus_crop_fal']);
		}
		

		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($file);
		\TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($parent->currentRecord);
		return $imageResource;
	}
	

}