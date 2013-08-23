<?php
namespace Zabus\CropFal\Xclass;


class GifBuilder extends \TYPO3\CMS\Frontend\Imaging\GifBuilder {

	const Z_CROP_FAL_W = 2;
	const Z_CROP_FAL_H = 3;
	const Z_CROP_FAL_X1 = 0;
	const Z_CROP_FAL_Y1 = 1;
	const Z_CROP_FAL_X2 = 4;
	const Z_CROP_FAL_Y2 = 5;

	/***********************************
	 *
	 * Scaling, Dimensions of images
	 *
	 ***********************************/
	/**
	 * Converts $imagefile to another file in temp-dir of type $newExt (extension).
	 *
	 * @param string $imagefile The image filepath
	 * @param string $newExt New extension, eg. "gif", "png", "jpg", "tif". If $newExt is NOT set, the new imagefile will be of the original format. If newExt = 'WEB' then one of the web-formats is applied.
	 * @param string $w Width. $w / $h is optional. If only one is given the image is scaled proportionally. If an 'm' exists in the $w or $h and if both are present the $w and $h is regarded as the Maximum w/h and the proportions will be kept
	 * @param string $h Height. See $w
	 * @param string $params Additional ImageMagick parameters.
	 * @param string $frame Refers to which frame-number to select in the image. '' or 0 will select the first frame, 1 will select the next and so on...
	 * @param array $options An array with options passed to getImageScale (see this function).
	 * @param boolean $mustCreate If set, then another image than the input imagefile MUST be returned. Otherwise you can risk that the input image is good enough regarding messures etc and is of course not rendered to a new, temporary file in typo3temp/. But this option will force it to.
	 * @return array [0]/[1] is w/h, [2] is file extension and [3] is the filename.
	 * @see getImageScale(), typo3/show_item.php, fileList_ext::renderImage(), tslib_cObj::getImgResource(), SC_tslib_showpic::show(), maskImageOntoImage(), copyImageOntoImage(), scale()
	 * @todo Define visibility
	 */
	public function imageMagickConvert($imagefile, $newExt = '', $w = '', $h = '', $params = '', $frame = '', $options = array(), $mustCreate = FALSE, $zabusCropFal = NULL) {
		//$this->scalecmd = "-resize";
		if ($this->NO_IMAGE_MAGICK) {
			// Returning file info right away
			return $this->getImageDimensions($imagefile);
		}
		if ($info = $this->getImageDimensions($imagefile)) {
			$newExt = strtolower(trim($newExt));
			// If no extension is given the original extension is used
			if (!$newExt) {
				$newExt = $info[2];
			}
			if ($newExt == 'web') {
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->webImageExt, $info[2])) {
					$newExt = $info[2];
				} else {
					$newExt = $this->gif_or_jpg($info[2], $info[0], $info[1]);
					if (!$params) {
						$params = $this->cmds[$newExt];
					}
				}
			}
			if (\TYPO3\CMS\Core\Utility\GeneralUtility::inList($this->imageFileExt, $newExt)) {
				if (strstr($w . $h, 'm')) {
					$max = 1;
				} else {
					$max = 0;
				}
				
				//ZabusCropFal
				if($zabusCropFal != NULL)
				{
					$cropValues = explode(",",$zabusCropFal);
					$cWidth = $cropValues[self::Z_CROP_FAL_W];
					$cHeight = $cropValues[self::Z_CROP_FAL_H];
					
					$ratio = ($cropValues[self::Z_CROP_FAL_X2] - $cropValues[self::Z_CROP_FAL_X1]) / ($cropValues[self::Z_CROP_FAL_Y2] - $cropValues[self::Z_CROP_FAL_Y1]);
					$aspectRatio = $ratio = ($cropValues[self::Z_CROP_FAL_X2] - $cropValues[self::Z_CROP_FAL_X1]) .":". ($cropValues[self::Z_CROP_FAL_Y2] - $cropValues[self::Z_CROP_FAL_Y1]);
				}
				/*if ($aspectRatio > 0) {
					$aspect = preg_split('/:/', $aspectRatio, 2);
					if ($options['maxW'] && !$w) {
						$w = $options['maxW'] . 'c';
						$h = intval($options['maxW'] * ($aspect[1] / $aspect[0])) . 'c';
					}
					if ($options['maxW'] && $w) {
						$w = $w . 'c';
						$options['maxW'] = $w;
						$h = intval($options['maxW'] * ($aspect[1] / $aspect[0])) . 'c';
					} elseif ($h && $w) {
						$w = intval($h * ($aspect[0] / $aspect[1])) . 'c';
						$h = $h . 'c';
					} elseif($h == '' && $w == '') {
						$w = $cropValues[self::Z_CROP_FAL_W];
						$h = $cropValues[self::Z_CROP_FAL_H];
					}
				}*/
				
				//////////////////////////////////////////////
				$data = $this->getImageScale($info, $w, $h, $options);
				$w = $data['origW'];
				$h = $data['origH'];
				// If no conversion should be performed
				// this flag is TRUE if the width / height does NOT dictate
				// the image to be scaled!! (that is if no width / height is
				// given or if the destination w/h matches the original image
				// dimensions or if the option to not scale the image is set)
				$noScale = !$w && !$h || $data[0] == $info[0] && $data[1] == $info[1] || $options['noScale'];
				
				if($zabusCropFal != NULL)
					$noScale = false;
				
				if ($noScale && !$data['crs'] && !$params && !$frame && $newExt == $info[2] && !$mustCreate) {
					// Set the new width and height before returning,
					// if the noScale option is set
					if (!empty($options['noScale'])) {
						$info[0] = $data[0];
						$info[1] = $data[1];
					}
					$info[3] = $imagefile;
					return $info;
				}
				$file['w'] = $info[0];
				$file['h'] = $info[1];
				$info[0] = $data[0];
				$info[1] = $data[1];
				
				$frame = $this->noFramePrepended ? '' : intval($frame);
				if (!$params) {
					$params = $this->cmds[$newExt];
				}
				// Cropscaling:
				if ($data['crs']) {
					if (!$data['origW']) {
						$data['origW'] = $data[0];
					}
					if (!$data['origH']) {
						$data['origH'] = $data[1];
					}
					$offsetX = intval(($data[0] - $data['origW']) * ($data['cropH'] + 100) / 200);
					$offsetY = intval(($data[1] - $data['origH']) * ($data['cropV'] + 100) / 200);
					$params .= ' -crop ' . $data['origW'] . 'x' . $data['origH'] . '+' . $offsetX . '+' . $offsetY . ' ';
				}
				
				//zabusCrop					
				if ($cropValues) {
					if (!$data['origW']) {
						$data['origW'] = $file['w'];
					}
					if (!$data['origH']) {
						$data['origH'] = $file['h'];
					}


					$xRatio = $data['origW'] / $cWidth;
					
					$xRatio = $yRatio = 1;
					
					$offsetX1 = intval($cropValues[self::Z_CROP_FAL_X1] * $xRatio);
					$yRatio = $data['origH'] / $cHeight;
					$offsetY1 = intval($cropValues[self::Z_CROP_FAL_Y1] * $yRatio);
					
					$offsetX1 = $cropValues[self::Z_CROP_FAL_X1];
					$offsetY1 = $cropValues[self::Z_CROP_FAL_Y1];
					
					
					if(($data['origW'] == $info[0] && $data['origH'] == $info[1]) || ($info[0] > $cropValues[self::Z_CROP_FAL_W]) || ($info[1] > $cropValues[self::Z_CROP_FAL_H]))
					{
						$info[0] = $cropValues[self::Z_CROP_FAL_W]; 
						$info[1] = $cropValues[self::Z_CROP_FAL_H]; 
					}
					
					$data['origW'] = $cropValues[self::Z_CROP_FAL_W]; 
					$data['origH'] = $cropValues[self::Z_CROP_FAL_H]; 
					//$data['origW'] = "125";
					$cropParams .= ' -crop ' . $data['origW'] . 'x' . $data['origH'] . '+' . $offsetX1 . '+' . $offsetY1 . ' ';
					
					
					//$info[0] = intval($data['origW'] + ($file['w'] - $cWidth) * $xRatio);
					//$info[1] = intval($data['origH'] + ($file['h'] - $cHeight) * $yRatio);
					$params = $paramsOrg . $cropParams;
				}
				////////////////////
				
				$command =  $params . ' +repage '.$this->scalecmd . ' ' . $info[0] . 'x' . $info[1] . '! ';
				$cropscale = $data['crs'] ? 'crs-V' . $data['cropV'] . 'H' . $data['cropH'] : '';
				if ($this->alternativeOutputKey) {
					$theOutputName = \TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5($command . $cropscale . basename($imagefile) . $this->alternativeOutputKey . '[' . $frame . ']');
				} else {
					$theOutputName = \TYPO3\CMS\Core\Utility\GeneralUtility::shortMD5($command . $cropscale . $imagefile . filemtime($imagefile) . '[' . $frame . ']');
				}
				if ($this->imageMagickConvert_forceFileNameBody) {
					$theOutputName = $this->imageMagickConvert_forceFileNameBody;
					$this->imageMagickConvert_forceFileNameBody = '';
				}
				// Making the temporary filename:
				$this->createTempSubDir('pics/');
				$output = $this->absPrefix . $this->tempPath . 'pics/' . $this->filenamePrefix . $theOutputName . '.' . $newExt;

				// Register temporary filename:
				$GLOBALS['TEMP_IMAGES_ON_PAGE'][] = $output;
				
				if ($this->dontCheckForExistingTempFile || !$this->file_exists_typo3temp_file($output, $imagefile)) {
					$this->imageMagickExec($imagefile, $output, $command, $frame);
				}
				if (file_exists($output)) {
					$info[3] = $output;
					$info[2] = $newExt;
					// params could realisticly change some imagedata!
					if ($params) {
						$info = $this->getImageDimensions($info[3]);
					}
					if ($info[2] == $this->gifExtension && !$this->dontCompress) {
						// Compress with IM (lzw) or GD (rle)  (Workaround for the absence of lzw-compression in GD)
						\TYPO3\CMS\Core\Utility\GeneralUtility::gif_compress($info[3], '');
					}
					return $info;
				}
			}
		}
	}

}