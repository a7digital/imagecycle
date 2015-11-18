<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Juergen Furrer <juergen.furrer@gmail.com>
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

require_once(t3lib_extMgm::extPath('imagecycle').'lib/class.tx_imagecycle_pagerenderer.php');

/**
 * Plugin 'Image Cycle' for the 'imagecycle' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_imagecycle
 */
class tx_imagecycle_pi1 extends tslib_pibase
{
	public $prefixId      = 'tx_imagecycle_pi1';
	public $scriptRelPath = 'pi1/class.tx_imagecycle_pi1.php';
	public $extKey        = 'imagecycle';
	public $pi_checkCHash = true;
	public $images        = array();
	public $hrefs         = array();
	public $captions      = array();
	public $hidden        = array();
	public $type          = 'normal';
	protected $lConf      = array();
	protected $contentKey = null;
	protected $piFlexForm = array();
	protected $imageDir   = 'uploads/tx_imagecycle/';
	protected $templateFileJS = null;
	protected $pagerenderer = NULL;
	protected $uid        = NULL;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	public function main($content, $conf)
	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		// define the key of the element
		$this->setContentKey("imagecycle");

		// set the system language
		$this->sys_language_uid = $GLOBALS['TSFE']->sys_language_content;

		// set the uid of the tt_content
		$this->uid = $this->cObj->data['_LOCALIZED_UID'] ? $this->cObj->data['_LOCALIZED_UID'] : $this->cObj->data['uid'];

		if ($this->cObj->data['list_type'] == $this->extKey.'_pi1') {
			$this->type = 'normal';

			// It's a content, all data from flexform

			$this->conf['mode']           = $this->getFlexformData('general', 'mode');
			$this->conf['images']         = $this->getFlexformData('general', 'images', ($this->conf['mode'] == 'upload'));
			$this->conf['hrefs']          = $this->getFlexformData('general', 'hrefs', ($this->conf['mode'] == 'upload'));
			$this->conf['captions']       = $this->getFlexformData('general', 'captions', ($this->conf['mode'] == 'upload'));
			$this->conf['captionsData']   = $this->getFlexformData('general', 'captionsData', ($this->conf['mode'] == 'uploadData'));
			$this->conf['damimages']      = $this->getFlexformData('general', 'damimages', ($this->conf['mode'] == 'dam'));
			$this->conf['damcategories']  = $this->getFlexformData('general', 'damcategories', ($this->conf['mode'] == 'dam_catedit'));

			$this->lConf['onlyFirstImage'] = $this->getFlexformData('general', 'onlyFirstImage');

			$imagesRTE = $this->getFlexformData('general', 'imagesRTE', ($this->conf['mode'] == 'uploadRTE'));
			$this->conf['imagesRTE'] = array();
			if (is_array($imagesRTE['el']) && count($imagesRTE['el']) > 0) {
				foreach ($imagesRTE['el'] as $elKey => $el) {
					if (is_numeric($elKey)) {
						$this->conf['imagesRTE'][] = array(
							"image"   => $el['data']['el']['image']['vDEF'],
							"href"    => $el['data']['el']['href']['vDEF'],
							"caption" => $this->pi_RTEcssText($el['data']['el']['caption']['vDEF']),
							"hide"    => $el['data']['el']['hide']['vDEF'],
						);
					}
				}
			}

			$this->lConf['imagewidth']        = $this->getFlexformData('settings', 'imagewidth');
			$this->lConf['imageheight']       = $this->getFlexformData('settings', 'imageheight');
			$this->lConf['showcaption']       = $this->getFlexformData('settings', 'showcaption');
			$this->lConf['showControl']       = $this->getFlexformData('settings', 'showControl');
			$this->lConf['showPager']         = $this->getFlexformData('settings', 'showPager');
			$this->lConf['random']            = $this->getFlexformData('settings', 'random');
			$this->lConf['cleartypeNoBg']     = $this->getFlexformData('settings', 'cleartypeNoBg');
			$this->lConf['stoponmousover']    = $this->getFlexformData('settings', 'stoponmousover');
			$this->lConf['stopAfterOneCycle'] = $this->getFlexformData('settings', 'stopAfterOneCycle');
			$this->lConf['pausedBegin']       = $this->getFlexformData('settings', 'pausedBegin');

			$this->lConf['type']               = $this->getFlexformData('movement', 'type');
			$this->lConf['transition']         = $this->getFlexformData('movement', 'transition');
			$this->lConf['transitiondir']      = $this->getFlexformData('movement', 'transitiondir');
			$this->lConf['transitionduration'] = $this->getFlexformData('movement', 'transitionduration');
			$this->lConf['displayduration']    = $this->getFlexformData('movement', 'displayduration');
			$this->lConf['delayduration']      = $this->getFlexformData('movement', 'delayduration');
			$this->lConf['fastOnEvent']        = $this->getFlexformData('movement', 'fastOnEvent');
			$this->lConf['sync']               = $this->getFlexformData('movement', 'sync');

			$this->lConf['options']         = $this->getFlexformData('special', 'options');
			$this->lConf['optionsOverride'] = $this->getFlexformData('special', 'optionsOverride');

			// define the key of the element
			$this->setContentKey("imagecycle_c" . $this->uid);

			// define the images
			switch ($this->conf['mode']) {
				case "" : {}
				case "folder" : {}
				case "upload" : {
					$this->setDataUpload();
					break;
				}
				case "uploadRTE" : {
					$this->setDataUploadRTE();
					break;
				}
				case "uploadData" : {
					$this->setDataUploadData();
					break;
				}
				case "dam" : {
					$this->setDataDam(false, 'tt_content', $this->uid);
					break;
				}
				case "dam_catedit" : {
					$this->setDataDam(true, 'tt_content', $this->uid);
					break;
				}
			}
			// Override the config with flexform data
			if ($this->lConf['imagewidth']) {
				$this->conf['imagewidth'] = $this->lConf['imagewidth'];
			}
			if ($this->lConf['imageheight']) {
				$this->conf['imageheight'] = $this->lConf['imageheight'];
			}
			if ($this->lConf['onlyFirstImage'] < 2) {
				$this->conf['onlyFirstImage'] = $this->lConf['onlyFirstImage'];
			}
			if ($this->lConf['type']) {
				$this->conf['type'] = implode(',', t3lib_div::trimExplode(',', $this->lConf['type']));
			}
			if ($this->lConf['transition']) {
				$this->conf['transition'] = $this->lConf['transition'];
			}
			if ($this->lConf['transitiondir']) {
				$this->conf['transitionDir'] = $this->lConf['transitiondir'];
			}
			if ($this->lConf['transitionduration']) {
				$this->conf['transitionDuration'] = $this->lConf['transitionduration'];
			}
			if ($this->lConf['displayduration']) {
				$this->conf['displayDuration'] = $this->lConf['displayduration'];
			}
			if (is_numeric($this->lConf['delayduration']) && $this->lConf['delayduration'] != 0) {
				$this->conf['delayDuration'] = $this->lConf['delayduration'];
			}
			if ($this->lConf['fastOnEvent']) {
				$this->conf['fastOnEvent'] = $this->lConf['fastOnEvent'];
			}
			// Will be overridden, if not "from TS"
			if ($this->lConf['showcaption'] < 2) {
				$this->conf['showcaption'] = $this->lConf['showcaption'];
			}
			if ($this->lConf['showControl'] < 2) {
				$this->conf['showControl'] = $this->lConf['showControl'];
			}
			if ($this->lConf['showPager'] < 2) {
				$this->conf['showPager'] = $this->lConf['showPager'];
			}
			if ($this->lConf['random'] < 2) {
				$this->conf['random'] = $this->lConf['random'];
			}
			if ($this->lConf['cleartypeNoBg'] < 2) {
				$this->conf['cleartypeNoBg'] = $this->lConf['cleartypeNoBg'];
			}
			if ($this->lConf['stoponmousover'] < 2) {
				$this->conf['stopOnMousover'] = $this->lConf['stoponmousover'];
			}
			if ($this->lConf['stopAfterOneCycle'] < 2) {
				$this->conf['stopAfterOneCycle'] = $this->lConf['stopAfterOneCycle'];
			}
			if ($this->lConf['pausedBegin'] < 2) {
				$this->conf['pausedBegin'] = $this->lConf['pausedBegin'];
			}
			if ($this->lConf['sync'] < 2) {
				$this->conf['sync'] = $this->lConf['sync'];
			}
			if ($this->lConf['options']) {
				$this->conf['options'] = $this->lConf['options'];
			}
			if ($this->lConf['optionsOverride'] < 2) {
				$this->conf['optionsOverride'] = $this->lConf['optionsOverride'];
			}
		} else {
			$this->type = 'header';
			// It's the header
			$used_page = array();
			$pageID    = false;
			$effectChanged = false;
			foreach ($GLOBALS['TSFE']->rootLine as $page) {
				if (! $pageID) {
					if ($effectChanged === false && trim($page['tx_imagecycle_effect']) && ! $this->conf['disableRecursion']) {
						$this->conf['type'] = $page['tx_imagecycle_effect'];
						$effectChanged = true;
					}
					if ($page['tx_imagecycle_mode'] != 'recursiv' && $page['tx_imagecycle_mode']) {
						if (
							($page['tx_imagecycle_mode'] == 'upload'      && trim($page['tx_imagecycle_images']) != '') ||
							($page['tx_imagecycle_mode'] == 'dam'         && trim($page['tx_imagecycle_damimages'])) ||
							($page['tx_imagecycle_mode'] == 'dam_catedit' && trim($page['tx_imagecycle_damcategories']) != '') ||
							$this->conf['disableRecursion'] ||
							$page['tx_imagecycle_stoprecursion']
						) {
							$used_page = $page;
							$pageID    = $used_page['uid'];
							$this->conf['mode']          = $used_page['tx_imagecycle_mode'];
							$this->conf['damcategories'] = $used_page['tx_imagecycle_damcategories'];
						}
					}
				}
			}
			if ($pageID) {
				if ($this->sys_language_uid) {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('tx_imagecycle_images, tx_imagecycle_hrefs, tx_imagecycle_captions, tx_imagecycle_effect, tx_imagecycle_mode', 'pages_language_overlay', 'pid='.intval($pageID).' AND sys_language_uid='.$this->sys_language_uid, '', '', 1);
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
					if (trim($used_page['tx_imagecycle_effect'])) {
						$this->conf['type'] = $row['tx_imagecycle_effect'];
					}
					if (trim($used_page['tx_imagecycle_mode'])) {
						$this->conf['mode'] = $row['tx_imagecycle_mode'];
					}
				}
				// define the images
				switch ($this->conf['mode']) {
					case "" : {}
					case "folder" : {}
					case "upload" : {
						$this->images   = t3lib_div::trimExplode(',',     $used_page['tx_imagecycle_images']);
						$this->hrefs    = t3lib_div::trimExplode(chr(10), $used_page['tx_imagecycle_hrefs']);
						$this->captions = t3lib_div::trimExplode(chr(10), $used_page['tx_imagecycle_captions']);
						// Language overlay
						if ($this->sys_language_uid) {
							if (trim($row['tx_imagecycle_images']) != '') {
								$this->images   = t3lib_div::trimExplode(',',     $row['tx_imagecycle_images']);
								$this->hrefs    = t3lib_div::trimExplode(chr(10), $row['tx_imagecycle_hrefs']);
								$this->captions = t3lib_div::trimExplode(chr(10), $row['tx_imagecycle_captions']);
							}
						}
						break;
					}
					case "dam" : {
						if ($this->sys_language_uid) {
							$this->setDataDam(false, 'pages_language_overlay', $pageID);
						}
						if (count($this->images) < 1) {
							$this->setDataDam(false, 'pages', $pageID);
						}
						break;
					}
					case "dam_catedit" : {
						if ($this->sys_language_uid) {
							$this->setDataDam(true, 'pages_language_overlay', $pageID);
						}
						if (count($this->images) < 1) {
							$this->setDataDam(true, 'pages', $pageID);
						}
						break;
					}
				}
			}
		}

		$count = null;
		if ($this->conf['onlyFirstImage']) {
			$count = (count($this->hrefs) > count($this->captions) ? count($this->hrefs) : count($this->captions));
			if (! $count) {
				$count = count($this->images);
			}
		} else {
			$count = count($this->images);
		}
		$data = array();
		$i = 0;
		for ($a=0; $a<$count; $a++) {
			if ($this->conf['onlyFirstImage']) {
				// Only use the first image
				$image = $this->images[0];
			} else {
				$image = $this->images[$a];
			}
			if ($image && ! $this->hidden[$a]) {
				$data[$i]['image']   = $image;
				$data[$i]['href']    = $this->hrefs[$a];
				$data[$i]['caption'] = $this->captions[$a];
				$i ++;
			}
		}

		return $this->parseTemplate($data);
	}

	/**
	 * Set the contentKey
	 * @param string $contentKey
	 */
	public function setContentKey($contentKey=null)
	{
		$this->contentKey = ($contentKey == null ? $this->extKey : $contentKey);
	}

	/**
	 * Get the contentKey
	 * @return string
	 */
	public function getContentKey()
	{
		return $this->contentKey;
	}

	/**
	 * Set the Information of the images if mode = upload
	 * @return boolean
	 */
	protected function setDataUpload()
	{
		if ($this->conf['images']) {
			// define the images
			$this->images = t3lib_div::trimExplode(',', $this->conf['images']);
			// define the hrefs
			if ($this->conf['hrefs']) {
				$this->hrefs = t3lib_div::trimExplode(chr(10), $this->conf['hrefs']);
			}
			// define the captions
			if ($this->conf['captions']) {
				$this->captions = t3lib_div::trimExplode(chr(10), $this->conf['captions']);
			}
			return true;
		}
		return false;
	}

	/**
	 * Set the information of the images if mode = uploadRTE
	 */
	protected function setDataUploadRTE()
	{
		if (is_array($this->conf['imagesRTE']) && count($this->conf['imagesRTE']) > 0) {
			foreach ($this->conf['imagesRTE'] as $key => $image) {
				$this->images[]   = $image['image'];
				$this->hrefs[]    = $image['href'];
				$this->captions[] = $image['caption'];
				$this->hidden[] = $image['hide'];
			}
		}
	}

	/**
	 * Set the information of the images if mode = uploadData
	 */
	protected function setDataUploadData()
	{
		if ($this->conf['images']) {
			// define the images
			$images = array();
			if ($this->conf['images']) {
				$images = t3lib_div::trimExplode(',', $this->conf['images']);
			}
			// define the hrefs
			$hrefs = array();
			if ($this->conf['hrefs']) {
				$hrefs = t3lib_div::trimExplode(chr(10), $this->conf['hrefs']);
			}
			// define the captions
			$this->captions = array();
			$captions = t3lib_div::trimExplode(",", $this->conf['captionsData']);
			$count = count($images) > count($captions) ? count($images) : count($captions);
			for ($a=0; $a < $count; $a++) {
				$GLOBALS['TSFE']->register['source'] = $captions[$a];
				// get the used table
				$table = substr($captions[$a], 0, strrpos($captions[$a], "_"));
				$dataConfTable = $this->conf['dataConf.'][$table.'.'];
				$cObjImage = $this->cObj->cObjGetSingle($dataConfTable['image'], $dataConfTable['image.']);
				if ($cObjImage) {
					$this->images[] = $cObjImage;
				} else {
					$this->images[] = $images[$a];
				}
				$cObjHref = $this->cObj->cObjGetSingle($dataConfTable['href'], $dataConfTable['href.']);
				if ($cObjHref) {
					$this->hrefs[] = $cObjHref;
				} else {
					$this->hrefs[] = $hrefs[$a];
				}
				$this->captions[] = $this->cObj->cObjGetSingle($dataConfTable['caption'], $dataConfTable['caption.']);
			}
			return true;
		}
		return false;
	}

	/**
	 * Set the Information of the images if mode = dam
	 * 
	 * @param boolean $fromCategory
	 * @param string $table
	 * @param integer $uid
	 * @return boolean
	 */
	protected function setDataDam($fromCategory=false, $table='tt_content', $uid=0)
	{
		// clear the imageDir
		$this->imageDir = '';
		// get all fields for captions
		$damCaptionFields = t3lib_div::trimExplode(',', $this->conf['damCaptionFields'], true);
		$damHrefFields    = t3lib_div::trimExplode(',', $this->conf['damHrefFields'], true);
		$fieldsArray = array_merge(
			$damCaptionFields,
			$damHrefFields
		);
		$fields = NULL;
		if (is_array($fieldsArray) && count($fieldsArray) > 0) {
			foreach ($fieldsArray as $field) {
				$fields .= ',tx_dam.' . $field;
			}
		}
		if ($fromCategory === true) {
			// Get the images from dam category
			$damcategories = $this->getDamcats($this->conf['damcategories']);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
				tx_dam_db::getMetaInfoFieldList() . $fields,
				'tx_dam',
				'tx_dam_mm_cat',
				'tx_dam_cat',
				" AND tx_dam_cat.uid IN (".implode(",", $damcategories).")",
				'',
				'tx_dam_mm_cat.sorting_foreign',
				''
			);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$images['rows'][] = $row;
			}
		} else {
			// Get the images from dam
			$images = tx_dam_db::getReferencedFiles(
				$table,
				$uid,
				'imagecycle',
				'tx_dam_mm_ref',
				tx_dam_db::getMetaInfoFieldList() . $fields,
				'',
				'',
				'tx_dam_mm_ref.sorting_foreign'
			);
		}
		if (is_array($images['rows']) && count($images['rows']) > 0) {
			// overlay the translation
			$conf = array(
				'sys_language_uid' => $this->sys_language_uid,
				'lovl_mode' => ''
			);
			// add image
			foreach ($images['rows'] as $key => $row) {
				$row = tx_dam_db::getRecordOverlay('tx_dam', $row, $conf);
				$absFileName = t3lib_div::getFileAbsFileName($row['file_path'] . $row['file_name']);
				$size = @getimagesize($absFileName);
				if (preg_match("/^image\//i", $size['mime'])) {
					// set the data
					$this->images[] = $row['file_path'] . $row['file_name'];$
					// set the href
					$href = '';
					unset($href);
					if (is_array($damHrefFields) && count($damHrefFields) > 0) {
						foreach ($damHrefFields as $damHrefField) {
							if (! isset($href) && trim($row[$damHrefField])) {
								$href = $row[$damHrefField];
								break;
							}
						}
					}
					$this->hrefs[] = $href;
					// set the caption
					$caption = '';
					unset($caption);
					if (is_array($damCaptionFields) && count($damCaptionFields) > 0) {
						if (isset($this->conf['damCaptionObject'])) {
							foreach ($damCaptionFields as $damCaptionField) {
								if (isset($row[$damCaptionField])) {
									$GLOBALS['TSFE']->register['dam_'.$damCaptionField] = $row[$damCaptionField];
								}
							}
							$caption = trim($this->cObj->cObjGetSingle($this->conf['damCaptionObject'], $this->conf['damCaptionObject.']));
							// Unset the registered values
							foreach ($damCaptionFields as $damCaptionField) {
								unset($GLOBALS['TSFE']->register['dam_'.$damCaptionField]);
							}
						} else {
							// the old way
							if (! isset($caption) && trim($row[$damCaptionField])) {
								$caption = $row[$damCaptionField];
								break;
							}
						}
					}
					$this->captions[] = $caption;
				}
			}
		}
		return true;
	}

	/**
	 * return all DAM categories including subcategories
	 *
	 * @return	array
	 */
	protected function getDamcats($dam_cat='')
	{
		$damCats = t3lib_div::trimExplode(",", $dam_cat, true);
		if (count($damCats) < 1) {
			return array();
		} else {
			// select subcategories
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid, parent_id',
				'tx_dam_cat',
				'parent_id IN ('.implode(",", $damCats).') '.$this->cObj->enableFields('tx_dam_cat'),
				'',
				'parent_id',
				''
			);
			$subcats = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$damCats[] = $row['uid'];
			}
		}
		return $damCats;
	}

	/**
	 * Parse all images into the template
	 * @param $data
	 * @return string
	 */
	public function parseTemplate($data=array(), $dir='', $onlyJS=false)
	{
		$this->pagerenderer = t3lib_div::makeInstance('tx_imagecycle_pagerenderer');
		$this->pagerenderer->setConf($this->conf);

		// define the directory of images
		if ($dir == '') {
			$dir = $this->imageDir;
		}

		// Check if $data is array
		if (count($data) == 0 && $onlyJS === false) {
			return false;
		}

		// define the contentKey if not exist
		if ($this->getContentKey() == '') {
			$this->setContentKey("imagecycle_key");
		}

		// The template for JS
		if (! $this->templateFileJS = $this->cObj->fileResource($this->conf['templateFileJS'])) {
			$this->templateFileJS = $this->cObj->fileResource("EXT:imagecycle/res/tx_imagecycle.js");
		}

		// set the key
		$markerArray = array();
		$markerArray["KEY"] = $this->getContentKey();

		// define the jQuery mode and function
		if ($this->conf['jQueryNoConflict']) {
			$jQueryNoConflict = "jQuery.noConflict();";
		} else {
			$jQueryNoConflict = "";
		}

		$options = array();

		if (! $this->conf['imagewidth']) {
			$this->conf['imagewidth'] = ($this->conf['imagewidth'] ? $this->conf['imagewidth'] : "200c");
		}
		if (! $this->conf['imageheight']) {
			$this->conf['imageheight'] = ($this->conf['imageheight'] ? $this->conf['imageheight'] : "200c");
		}

		// wrap if integer
		if (is_numeric($this->conf['imagewidth'])) {
			$this->conf['imagewidth'] = $this->cObj->stdWrap($this->conf['imagewidth'], $this->conf['integerWidthWrap.']);
		}
		if (is_numeric($this->conf['imageheight'])) {
			$this->conf['imageheight'] = $this->cObj->stdWrap($this->conf['imageheight'], $this->conf['integerHeightWrap.']);
		}

		if ($this->conf['type']) {
			$options['fx'] = "fx: '{$this->conf['type']}'";
		}
		if (in_array($this->conf['transition'], array('linear', 'swing'))) {
			$options['easing'] = "easing: '{$this->conf['transition']}'";
		} elseif ($this->conf['transitionDir'] && $this->conf['transition']) {
			$options['easing'] = "easing: 'ease{$this->conf['transitionDir']}{$this->conf['transition']}'";
		}
		if ($this->conf['transitionDuration'] > 0) {
			$options['speed'] = "speed: '{$this->conf['transitionDuration']}'";
		}
		if ($this->conf['displayDuration'] > 0) {
			$options['timeout'] = "timeout: '{$this->conf['displayDuration']}'";
		}
		if (is_numeric($this->conf['delayDuration']) && $this->conf['delayDuration'] != 0) {
			$options['delay'] = "delay: {$this->conf['delayDuration']}";
		}
		if ($this->conf['fastOnEvent'] > 0) {
			$options['fastOnEvent'] = "fastOnEvent: ".$this->conf['fastOnEvent'];
		}

		if ($this->conf['stopOnMousover']) {
			$options['pause'] = "pause: true";
		}
		if ($this->conf['stopAfterOneCycle'] && is_array($data) && count($data) > 0) {
			$options['autostop']      = "autostop: true";
			$options['autostopCount'] = "autostopCount: '".count($data)."'";
		}

		$options['sync'] = "sync: ".($this->conf['sync'] ? 'true' : 'false');
		if (count($data) > 1) {
			$options['random'] = "random: ".($this->conf['random'] ? 'true' : 'false');
		}
		$options['cleartypeNoBg'] = "cleartypeNoBg: ".($this->conf['cleartypeNoBg'] ? 'true' : 'false');

		$captionTag = $this->cObj->stdWrap($this->conf['cycle.'][$this->type.'.']['captionTag'], $this->conf['cycle.'][$this->type.'.']['captionTag.']);
		$markerArray["CAPTION_TAG"] = $captionTag;
		$before = NULL;
		$after  = NULL;
		// add caption
		if ($this->conf['showcaption']) {
			// define the animation for the caption
			$fx = array();
			if (! $this->conf['captionAnimate']) {
				$before .= "jQuery('{$captionTag}', this).css('display', 'none');";
				$after  .= "jQuery('{$captionTag}', this).css('display', 'block');";
			} else {
				if ($this->conf['captionTypeOpacity']) {
					$fx[] = "opacity: 'show'";
				}
				if ($this->conf['captionTypeHeight']) {
					$fx[] = "height: 'show'";
				}
				if ($this->conf['captionTypeWidth']) {
					$fx[] = "width: 'show'";
				}
				// if no effect is choosen, opacity is the fallback
				if (count($fx) < 1) {
					$fx[] = "opacity: 'show'";
				}
				if (! is_numeric($this->conf['captionSpeed'])) {
					$this->conf['captionSpeed'] = 200;
				}

				$easing = NULL;
				if (in_array($this->conf['captionTransition'], array('linear', 'swing'))) {
					$easing = ",'{$this->conf['captionTransition']}'";
				} elseif ($this->conf['captionTransitionDir'] && $this->conf['captionTransition']) {
					$easing = ",'ease{$this->conf['captionTransitionDir']}{$this->conf['captionTransition']}'";
				}

				$before .= "jQuery('{$captionTag}', this).css('display', 'none');";
				$after  .= "jQuery('{$captionTag}', this).animate({".(implode(",", $fx))."},{$this->conf['captionSpeed']}{$easing});";
			}
			if ($this->conf['captionSync']) {
				$before = $before . $after;
				$after = NULL;
			}
		}
		// 
		if ($this->conf['showPager']) {
			$templateActivatePagerCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_ACTIVATE_PAGER_JS###"));
			$after .= $this->cObj->substituteMarkerArray($templateActivatePagerCode, $markerArray, '###|###', 0);
		}
		if ($before) {
			$options['before'] = "before: function(a,n,o,f) {".$before."}";
		}
		if ($after) {
			$options['after'] = "after: function(a,n,o,f) {".$after."}";
		}

		// overwrite all options if set
		if (trim($this->conf['options'])) {
			if ($this->conf['optionsOverride']) {
				$options = array($this->conf['options']);
			} else {
				$options['options'] = $this->conf['options'];
			}
		}

		// define the js file
		$this->pagerenderer->addJsFile($this->conf['jQueryCycle']);

		// define the css file
		$this->pagerenderer->addCssFile($this->conf['cssFile']);

		// get the Template of the Javascript
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_JS###"))) {
			$templateCode = "alert('Template TEMPLATE_JS is missing')";
		}
		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		// Show the caption when sync is turned off
		if ($this->conf['showcaption'] && ! $this->conf['captionSync']) {
			$templateShowCaption = trim($this->cObj->getSubpart($templateCode, "###SHOW_CAPTION_AT_START###"));
		} else {
			$templateShowCaption = null;
		}
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###SHOW_CAPTION_AT_START###', $templateShowCaption, 0);

		// define the control
		if ($this->conf['showControl']) {
			$templateControl = trim($this->cObj->getSubpart($templateCode, "###CONTROL###"));
			$templateControlAfter = trim($this->cObj->getSubpart($templateCode, "###CONTROL_AFTER###"));
			$options[] = trim($this->cObj->getSubpart($templateCode, "###CONTROL_OPTIONS###"));
		} else {
			$templateControl = null;
		}
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###CONTROL###', $templateControl, 0);
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###CONTROL_AFTER###', $templateControlAfter, 0);
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###CONTROL_OPTIONS###', '', 0);

		// define the play class
		if ($this->conf['pausedBegin']) {
			$templatePaused = $this->cObj->getSubpart($templateCode, "###PAUSED###");
			$templatePausedBegin = $this->cObj->getSubpart($templateCode, "###PAUSED_BEGIN###");;
		} else {
			$templatePaused = null;
			$templatePausedBegin = null;
		}
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###PAUSED###', $templatePaused, 0);
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###PAUSED_BEGIN###', $templatePausedBegin, 0);

		// define the pager
		if ($this->conf['showPager']) {
			$templatePager = $this->cObj->getSubpart($templateCode, "###PAGER###");
		} else {
			$templatePager = null;
		}
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###PAGER###', $templatePager, 0);

		// Slow connection will have a load to start
		if ($this->conf['fixSlowConnection']) {
			$templateSlowBefore = $this->cObj->getSubpart($templateCode, "###SLOW_CONNECTION_BEFORE###");
			$templateSlowAfter  = $this->cObj->getSubpart($templateCode, "###SLOW_CONNECTION_AFTER###");
		} else {
			$templateSlowBefore = null;
		}
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###SLOW_CONNECTION_BEFORE###', $templateSlowBefore, 0);
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###SLOW_CONNECTION_AFTER###',  $templateSlowAfter, 0);

		// If only one image is displayed, the caption will be show
		if (is_array($data) && count($data) == 1) {
			$templateOnlyOneImage = $this->cObj->getSubpart($templateCode, "###ONLY_ONE_IMAGE###");
		} else {
			$templateOnlyOneImage = null;
		}
		$templateCode = $this->cObj->substituteSubpart($templateCode, '###ONLY_ONE_IMAGE###', $templateOnlyOneImage, 0);

		// define the markers
		$markerArray = array();
		$markerArray["OPTIONS"]     = implode(",\n		", $options);
		$markerArray["CAPTION_TAG"] = $captionTag;

		// set the markers
		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		$this->pagerenderer->addJS($jQueryNoConflict . $templateCode);

		// checks if t3jquery is loaded
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->conf['jQueryLibrary'], true);
			$this->pagerenderer->addJsFile($this->conf['jQueryEasing']);
		}

		// Add the ressources
		$this->pagerenderer->addResources();

		if ($onlyJS === true) {
			return true;
		}

		$return_string = null;
		$images = null;
		$pager = null;
		$no_script= null;
		$GLOBALS['TSFE']->register['key'] = $this->getContentKey();
		$GLOBALS['TSFE']->register['imagewidth']  = $this->conf['imagewidth'];
		$GLOBALS['TSFE']->register['imageheight'] = $this->conf['imageheight'];
		$GLOBALS['TSFE']->register['showcaption'] = $this->conf['showcaption'];
		$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = 0;
		$GLOBALS['TSFE']->register['IMAGE_COUNT'] = count($data);
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $item) {
				$image = null;
				$imgConf = $this->conf['cycle.'][$this->type.'.']['image.'];
				if (file_exists(t3lib_div::getIndpEnv("TYPO3_DOCUMENT_ROOT") . '/' . $item['image'])) {
					$totalImagePath = $item['image'];
				} else {
					$totalImagePath = $dir . $item['image'];
				}
				$GLOBALS['TSFE']->register['file']    = $totalImagePath;
				$GLOBALS['TSFE']->register['href']    = $item['href'];
				$GLOBALS['TSFE']->register['caption'] = $item['caption'];
				$GLOBALS['TSFE']->register['CURRENT_ID'] = $GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] + 1;
				if ($this->hrefs[$key]) {
					$imgConf['imageLinkWrap.'] = $imgConf['imageHrefWrap.'];
				}
				$image = $this->cObj->IMAGE($imgConf);
				if ($item['caption'] && $this->conf['showcaption']) {
					$image = $this->cObj->stdWrap($image, $this->conf['cycle.'][$this->type.'.']['captionWrap.']);
				}
				// Add the noscript wrap to the firs image
				if ($key == 0 && $this->conf['cycle.'][$this->type.'.']['noscriptWrap.']) {
					$no_script = $this->cObj->stdWrap($image, $this->conf['cycle.'][$this->type.'.']['noscriptWrap.']);
				}
				$image = $this->cObj->stdWrap($image, $this->conf['cycle.'][$this->type.'.']['itemWrap.']);
				$images .= $image;
				// create the pager
				if ($this->conf['showPager']) {
					$pager .= trim($this->cObj->cObjGetSingle($this->conf['cycle.'][$this->type.'.']['pager'], $this->conf['cycle.'][$this->type.'.']['pager.']));
				}
				$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] ++;
			}
			$markerArray['PAGER'] = $this->cObj->stdWrap($pager, $this->conf['cycle.'][$this->type.'.']['pagerWrap.']);
			// the stdWrap
			$images = $this->cObj->stdWrap($images, $this->conf['cycle.'][$this->type.'.']['stdWrap.']);
			$return_string = $this->cObj->substituteMarkerArray($images, $markerArray, '###|###', 0);
			// add the noscript
			$return_string .= $no_script;
		}
		return $return_string;
	}

	/**
	 * Set the piFlexform data
	 * 
	 * @return void
	 */
	protected function setFlexFormData()
	{
		if (! count($this->piFlexForm)) {
			$this->pi_initPIflexForm();
			$this->piFlexForm = $this->cObj->data['pi_flexform'];
		}
	}

	/**
	 * Extract the requested information from flexform
	 * @param string $sheet
	 * @param string $name
	 * @param boolean $devlog
	 * @return string
	 */
	protected function getFlexformData($sheet='', $name='', $devlog=true)
	{
		$this->setFlexFormData();
		if (! isset($this->piFlexForm['data'])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform Data not set", $this->extKey, 1);
			}
			return null;
		}
		if (! isset($this->piFlexForm['data'][$sheet])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform sheet '{$sheet}' not defined", $this->extKey, 1);
			}
			return null;
		}
		if (! isset($this->piFlexForm['data'][$sheet]['lDEF'][$name])) {
			if ($devlog === true) {
				t3lib_div::devLog("Flexform Data [{$sheet}][{$name}] does not exist", $this->extKey, 1);
			}
			return null;
		}
		if (isset($this->piFlexForm['data'][$sheet]['lDEF'][$name]['vDEF'])) {
			return $this->pi_getFFvalue($this->piFlexForm, $name, $sheet);
		} else {
			return $this->piFlexForm['data'][$sheet]['lDEF'][$name];
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/pi1/class.tx_imagecycle_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/pi1/class.tx_imagecycle_pi1.php']);
}

?>