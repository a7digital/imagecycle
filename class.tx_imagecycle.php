<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Juergen Furrer <juergen.furrer@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_imagecycle
 */
class tx_imagecycle
{
	public $cObj;

	public function getImageForTTnews($paramArray, $conf)
	{
		$markerArray = $paramArray[0];
		$lConf = $paramArray[1];
		$pObj = &$conf['parentObj']; // make a reference to the parent-object
		$row = $pObj->local_cObj->data;
		if ($row['tx_imagecycle_activate']) {
			$imageConf = 'imagecycleSingleImage.';
			$lConf['imageCount'] = 1000;
			// DAM_TTNEWS - single image option - morini@gammsystem.com 
			$lConf['imageCount'] = $lConf[$imageConf]['imageCount']?$lConf[$imageConf]['imageCount']:1000;
		} else {
			$imageConf = 'image.';
		}
		$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount'] : 1;
		$imageNum = MathUtility::forceIntegerInRange($imageNum, 0, 1000);
		$theImgCode = '';
		$imgsCaptions = explode(chr(10), $row['imagecaption']);

		// DAM_TTNEWS - load image from DAM - morini@gammsystem.com
		if (ExtensionManagementUtility::isLoaded('dam_ttnews')) {
			$imgs = $this->getDamImages($pObj, $lConf);
		} else {
			$imgs = GeneralUtility::trimExplode(',', $row['image'], 1);
		}

		// remove first img from the image array in single view if the TSvar firstImageIsPreview is set
		if ($pObj->theCode == 'SINGLE') {
			$iC = count($imgs);
			if (($iC > 1 && $pObj->config['firstImageIsPreview']) || ($iC >= 1 && $pObj->config['forceFirstImageIsPreview'])) {
				array_shift($imgs);
				array_shift($imgsCaptions);
				$iC--;
			}
			// get img array parts for single view pages
			if ($pObj->piVars[$pObj->pObj['singleViewPointerName']]) {
				$spage = $this->piVars[$this->config['singleViewPointerName']];
				$astart = $imageNum * $spage;
				$imgs = array_slice($imgs, $astart, $imageNum);
				$imgsCaptions = array_slice($imgsCaptions, $astart, $imageNum);
			}
		}

		reset($imgs);
		$cc = 0;
		while (list($key, $val) = each($imgs)) {
			if ($cc == $imageNum) {
				break;
			}
			if ($val) {
				// register some vars
				$GLOBALS['TSFE']->register['image']        = $val;
				$GLOBALS['TSFE']->register['imagecaption'] = $imgsCaptions[$cc];
				$GLOBALS['TSFE']->register['caption']      = $imgsCaptions[$cc];
				$GLOBALS['TSFE']->register['key']          = 'imagecycle_' . $pObj->local_cObj->data['uid'];
				$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = $key;

				// DAM_TTNEWS - set path for DAM images - morini@gammsystem.com
				if (ExtensionManagementUtility::isLoaded('dam_ttnews')) {
					$lConf[$imageConf]['file'] = $val;
				} else {
					$lConf[$imageConf]['file'] = 'uploads/pics/'.$val;
				}

				// define the file
				if ($row['tx_imagecycle_activate']) {
					$image = $pObj->local_cObj->IMAGE($lConf[$imageConf]);
					$caption = $pObj->local_cObj->stdWrap($image, $lConf['captionWrap.']);
					$theImgCode .= $pObj->local_cObj->stdWrap($caption, $lConf['itemWrap.']);
				} else {
					$theImgCode .= $pObj->local_cObj->IMAGE($lConf[$imageConf]).$pObj->local_cObj->stdWrap($imgsCaptions[$cc], $lConf['caption_stdWrap.']);
				}
			}
			$cc ++;
		}

		$markerArray['###NEWS_IMAGE###'] = '';
		if ($cc) {
			if ($row['tx_imagecycle_activate']) {
				$markerArray['###NEWS_IMAGE###'] = $pObj->local_cObj->stdWrap(trim($theImgCode), $lConf['imagecycleImageWrapIfAny.']);
			} else {
				$markerArray['###NEWS_IMAGE###'] = $pObj->local_cObj->wrap(trim($theImgCode), $lConf['imageWrapIfAny']);
			}
		}
		return $markerArray;
	}

	public function getSlideshow($content, $conf)
	{
		$return_string = NULL;
		if ($this->cObj->data['tx_imagecycle_activate']) {
			$instanceClass = ($conf['instanceClass'] ? $conf['instanceClass'] : ExtensionManagementUtility::extPath('imagecycle').'pi1/class.tx_imagecycle_pi1.php');
			if (! file_exists($instanceClass)) {
				// try to get the filename if file not exists
				$instanceClass = $GLOBALS['TSFE']->tmpl->getFileName($instanceClass);
			}
			if (! file_exists($instanceClass)) {
				GeneralUtility::devLog('Class \''.$instanceClass.'\' not found', 'imagecycle', 1);
				return $content;
			}
			require_once($instanceClass);
			$instance = ($conf["instance"] ? $conf["instance"] : 'tx_imagecycle_pi1');
			$obj = GeneralUtility::makeInstance($instance);
			$obj->setContentKey($obj->extKey . '_' . $this->cObj->data['uid']);
			$obj->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.'][$instance . '.'];
			// overwrite the width and height of the config
			$obj->conf['imagewidth'] = $GLOBALS['TSFE']->register['imagewidth'];
			$obj->conf['imageheight'] = $GLOBALS['TSFE']->register['imageheight'];
			if ($this->cObj->data['tx_imagecycle_duration'] > 0) {
				$obj->conf['displayDuration'] = $this->cObj->data['tx_imagecycle_duration'];
				$obj->conf['nivoPauseTime'] = $this->cObj->data['tx_imagecycle_duration'];
			}
			$obj->cObj = $this->cObj;
			$obj->type = 'content';
			$return_string = $obj->parseTemplate(array(), 'uploads/pics/', TRUE);
		}
		return $content;
	}

	/**
	 * DAM_TTNEWS - load images from DAM - morini@gammsystem.com
	 */
	private function getDamImages(&$pObj, &$lConf)
	{
		$row = $pObj->local_cObj->data;

		$mode = $GLOBALS['TSFE']->tmpl->setup['plugin.']['dam_ttnews.']['mode'];

		$imageNum = isset($lConf['imageCount']) ? $lConf['imageCount']:1;
		$imageNum = MathUtility::forceIntegerInRange($imageNum, 0, 100);
		$theImgCode = '';

		$imgsCaptions = explode(chr(10), $row['imagecaption']);
		$imgsAltTexts = explode(chr(10), $row['imagealttext']);
		$imgsTitleTexts = explode(chr(10), $row['imagetitletext']);

		// to get correct DAM files, set uid
		// workspaces
		if (isset($row['_ORIG_uid']) && ($row['_ORIG_uid'] > 0)) {
			// draft workspace
			$uid = $row['_ORIG_uid'];
		} else {
			// live workspace
			$uid = $row['uid'];
		}
		// translations - i10n mode
		if ($row['_LOCALIZED_UID']) {
			// i10n mode = exclude   -> do nothing
			// i10n mode = mergeIfNotBlank
			$confArr_ttnews=unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tt_news']);
			if (! $confArr_ttnews['l10n_mode_imageExclude'] && $row['tx_damnews_dam_images']) {
				$uid = $row['_LOCALIZED_UID'];
			}
		}
		$cc = 0;
		$shift = false;
		// get DAM data
		$infoFields = tx_dam_db::getMetaInfoFieldList(true,array('alt_text'=>'alt_text','caption'=>'caption'));
		$damData = tx_dam_db::getReferencedFiles('tt_news', $uid, 'tx_damnews_dam_images','tx_dam_mm_ref',$infoFields);
		$damFiles = $damData['files'];
		$damRows = $damData['rows'];
		// localisation of DAM data  
		while (list($key,$val) = each($damRows)) {
			$damRows[$key] =  $GLOBALS['TSFE']->sys_page->getRecordOverlay('tx_dam', $val, $GLOBALS['TSFE']->sys_language_uid, '');
		}

		return $damFiles;
	}
}


// XCLASS inclusion code
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/class.tx_imagecycle.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/class.tx_imagecycle.php']);
}
