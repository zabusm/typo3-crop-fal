<?php
namespace Zabus\CropFal\Xclass;
use \TYPO3\CMS\Core\Resource, \TYPO3\CMS\Core\Utility, \TYPO3\CMS\Core\Resource\Processing\TaskInterface;

class LocalCropScaleMaskHelper extends \TYPO3\CMS\Core\Resource\Processing\LocalCropScaleMaskHelper {

	/**
	 * This method actually does the processing of files locally
	 *
	 * Takes the original file (for remote storages this will be fetched from the remote server),
	 * does the IM magic on the local server by creating a temporary typo3temp/ file,
	 * copies the typo3temp/ file to the processing folder of the target storage and
	 * removes the typo3temp/ file.
	 *
	 * @param TaskInterface $task
	 * @return array
	 */
	public function process(TaskInterface $task) {
		$result = NULL;
		$targetFile = $task->getTargetFile();
		$sourceFile = $task->getSourceFile();

		$originalFileName = $sourceFile->getForLocalProcessing(FALSE);
		/** @var $gifBuilder \TYPO3\CMS\Frontend\Imaging\GifBuilder */
		$gifBuilder = Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Frontend\\Imaging\\GifBuilder');
		$gifBuilder->init();

		$configuration = $targetFile->getProcessingConfiguration();
		$configuration['additionalParameters'] = $this->modifyImageMagickStripProfileParameters($configuration['additionalParameters'], $configuration);

		if (empty($configuration['fileExtension'])) {
			$configuration['fileExtension'] = $task->getTargetFileExtension();
		}

		$options = $this->getConfigurationForImageCropScaleMask($targetFile, $gifBuilder);

		// Normal situation (no masking)
		if (!(is_array($configuration['maskImages']) && $GLOBALS['TYPO3_CONF_VARS']['GFX']['im'])) {
				// the result info is an array with 0=width,1=height,2=extension,3=filename
			$result = $gifBuilder->imageMagickConvert(
				$originalFileName,
				$configuration['fileExtension'],
				$configuration['width'],
				$configuration['height'],
				$configuration['additionalParameters'],
				$configuration['frame'],
				$options,
				false,
				$configuration['aspect']
			);
		} else {
			$targetFileName = $this->getFilenameForImageCropScaleMask($task);
			$temporaryFileName = $gifBuilder->tempPath . $targetFileName;
			$maskImage = $configuration['maskImages']['maskImage'];
			$maskBackgroundImage = $configuration['maskImages']['backgroundImage'];
			if ($maskImage instanceof Resource\FileInterface && $maskBackgroundImage instanceof Resource\FileInterface) {
				$negate = $GLOBALS['TYPO3_CONF_VARS']['GFX']['im_negate_mask'] ? ' -negate' : '';
				$temporaryExtension = 'png';
				if ($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_mask_temp_ext_gif']) {
					// If ImageMagick version 5+
					$temporaryExtension = $gifBuilder->gifExtension;
				}
				$tempFileInfo = $gifBuilder->imageMagickConvert(
					$originalFileName,
					$temporaryExtension,
					$configuration['width'],
					$configuration['height'],
					$configuration['additionalParameters'],
					$configuration['frame'],
					$options,
					false,
					$configuration['aspect']
				);
				if (is_array($tempFileInfo)) {
					$maskBottomImage = $configuration['maskImages']['maskBottomImage'];
					if ($maskBottomImage instanceof $maskBottomImage) {
						$maskBottomImageMask = $configuration['maskImages']['maskBottomImageMask'];
					} else {
						$maskBottomImageMask = NULL;
					}

					//	Scaling:	****
					$tempScale = array();
					$command = '-geometry ' . $tempFileInfo[0] . 'x' . $tempFileInfo[1] . '!';
					$command = $this->modifyImageMagickStripProfileParameters($command, $configuration);
					$tmpStr = $gifBuilder->randomName();
					//	m_mask
					$tempScale['m_mask'] = $tmpStr . '_mask.' . $temporaryExtension;
					$gifBuilder->imageMagickExec($maskImage->getForLocalProcessing(TRUE), $tempScale['m_mask'], $command . $negate);
					//	m_bgImg
					$tempScale['m_bgImg'] = $tmpStr . '_bgImg.' . trim($GLOBALS['TYPO3_CONF_VARS']['GFX']['im_mask_temp_ext_noloss']);
					$gifBuilder->imageMagickExec($maskBackgroundImage->getForLocalProcessing(), $tempScale['m_bgImg'], $command);
					//	m_bottomImg / m_bottomImg_mask
					if ($maskBottomImage instanceof Resource\FileInterface && $maskBottomImageMask instanceof Resource\FileInterface) {
						$tempScale['m_bottomImg'] = $tmpStr . '_bottomImg.' . $temporaryExtension;
						$gifBuilder->imageMagickExec($maskBottomImage->getForLocalProcessing(), $tempScale['m_bottomImg'], $command);
						$tempScale['m_bottomImg_mask'] = ($tmpStr . '_bottomImg_mask.') . $temporaryExtension;
						$gifBuilder->imageMagickExec($maskBottomImageMask->getForLocalProcessing(), $tempScale['m_bottomImg_mask'], $command . $negate);
						// BEGIN combining:
						// The image onto the background
						$gifBuilder->combineExec($tempScale['m_bgImg'], $tempScale['m_bottomImg'], $tempScale['m_bottomImg_mask'], $tempScale['m_bgImg']);
					}
					// The image onto the background
					$gifBuilder->combineExec($tempScale['m_bgImg'], $tempFileInfo[3], $tempScale['m_mask'], $temporaryFileName);
					// Unlink the temp-images...
					foreach ($tempScale as $tempFile) {
						if (@is_file($tempFile)) {
							unlink($tempFile);
						}
					}
				}
				$result = $tempFileInfo;
			}
		}
		// check if the processing really generated a new file
		if ($result !== NULL) {
			if ($result[3] !== $originalFileName) {
				$result = array(
					'width' => $result[0],
					'height' => $result[1],
					'filePath' => $result[3],
				);
			} else {
				// No file was generated
				$result = NULL;
			}
		}
		return $result;
	}


}