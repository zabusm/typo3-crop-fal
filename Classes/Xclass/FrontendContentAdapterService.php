<?php
namespace Zabus\CropFal\Xclass;

class FrontendContentAdapterService extends TYPO3\CMS\Core\Resource\Service\FrontendContentAdapterService
{
	/**
	 * Modifies the DB row in the CONTENT cObj of tslib_content for supplying
	 * backwards compatibility for some file fields which have switched to using
	 * the new File API instead of the old uploads/ folder for storing files.
	 *
	 * This method is called by the render() method of tslib_content_Content.
	 *
	 * @param array $row typically an array, but can also be null (in extensions or e.g. FLUID viewhelpers)
	 * @param string $table the database table where the record is from
	 * @throws \RuntimeException
	 * @return void
	 */
	static public function modifyDBRow(&$row, $table) {
		if (isset($row['_MIGRATED']) && $row['_MIGRATED'] === TRUE) {
			return;
		}
		if (array_key_exists($table, static::$migrateFields)) {
			foreach (static::$migrateFields[$table] as $migrateFieldName => $oldFieldNames) {
				if ($row !== NULL && isset($row[$migrateFieldName]) && self::fieldIsInType($migrateFieldName, $table, $row)) {
					/** @var $fileRepository \TYPO3\CMS\Core\Resource\FileRepository */
					$fileRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
					if ($table === 'pages' && isset($row['_LOCALIZED_UID']) && intval($row['sys_language_uid']) > 0) {
						$table = 'pages_language_overlay';
					}
					$files = $fileRepository->findByRelation($table, $migrateFieldName, isset($row['_LOCALIZED_UID']) ? intval($row['_LOCALIZED_UID']) : intval($row['uid']));
					$fileFieldContents = array(
						'paths' => array(),
						'titleTexts' => array(),
						'captions' => array(),
						'links' => array(),
						'alternativeTexts' => array(),
						$migrateFieldName . '_fileUids' => array()
					);
					$oldFieldNames[$migrateFieldName . '_fileUids'] = $migrateFieldName . '_fileUids';

					foreach ($files as $file) {
						/** @var $file \TYPO3\CMS\Core\Resource\FileReference */
						$fileProperties = $file->getProperties();
						$fileFieldContents['paths'][] = '../../' . $file->getPublicUrl();
						$fileFieldContents['titleTexts'][] = $fileProperties['title'];
						$fileFieldContents['captions'][] = $fileProperties['description'];
						$fileFieldContents['links'][] = $fileProperties['link'];
						$fileFieldContents['alternativeTexts'][] = $fileProperties['alternative'];
						$fileFieldContents[$migrateFieldName .  '_fileUids'][] = $file->getOriginalFile()->getUid();
					}
					foreach ($oldFieldNames as $oldFieldType => $oldFieldName) {
						if ($oldFieldType === '__typeMatch') {
							continue;
						}
						// For paths, make comma separated list
						if ($oldFieldType === 'paths' || substr($oldFieldType, -9) == '_fileUids') {
							$fieldContents = implode(',', $fileFieldContents[$oldFieldType]);
						} else {
							// For all other fields, separate by newline
							$fieldContents = implode(chr(10), $fileFieldContents[$oldFieldType]);
						}
						$row[$oldFieldName] = $fieldContents;
					}
				}
			}
		}
		$row['_MIGRATED'] = TRUE;
		
		var_dump($row);
		die();
	}
}