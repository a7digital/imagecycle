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
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(t3lib_extMgm::extPath('imagecycle').'pi1/class.tx_imagecycle_pi1.php');

/**
 * Plugin 'Slice-Box' for the 'imagecycle' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_imagecycle
 */
class tx_imagecycle_pi5 extends tx_imagecycle_pi1
{
	public $prefixId      = 'tx_imagecycle_pi5';
	public $scriptRelPath = 'pi5/class.tx_imagecycle_pi5.php';
	public $extKey        = 'imagecycle';
	public $pi_checkCHash = true;
	public $images        = array();
	public $hrefs         = array();
	public $captions      = array();
	public $type          = 'normal';
	protected $lConf      = array();
	protected $contentKey = null;
	protected $jsFiles    = array();
	protected $js         = array();
	protected $cssFiles   = array();
	protected $css        = array();
	protected $imageDir   = 'uploads/tx_imagecycle/';
	protected $templateFileJS = null;
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
		$this->setContentKey("imagecycle-slice");

		// set the system language
		$this->sys_language_uid = $GLOBALS['TSFE']->sys_language_content;

		// set the uid of the tt_content
		$this->uid = $this->cObj->data['_LOCALIZED_UID'] ? $this->cObj->data['_LOCALIZED_UID'] : $this->cObj->data['uid'];

		if ($this->cObj->data['list_type'] == $this->extKey.'_pi5') {
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

			$this->lConf['imagewidth']            = $this->getFlexformData('settings', 'imagewidth');
			$this->lConf['imageheight']           = $this->getFlexformData('settings', 'imageheight');
			$this->lConf['sliceColorHiddenSides'] = $this->getFlexformData('settings', 'sliceColorHiddenSides');

			$this->lConf['sliceOrientation']        = $this->getFlexformData('movement', 'sliceOrientation');
			$this->lConf['slicePerspective']        = $this->getFlexformData('movement', 'slicePerspective');
			$this->lConf['sliceSlicesCount']        = $this->getFlexformData('movement', 'sliceSlicesCount');
			$this->lConf['sliceDisperseFactor']     = $this->getFlexformData('movement', 'sliceDisperseFactor');
			$this->lConf['sliceSequentialRotation'] = $this->getFlexformData('movement', 'sliceSequentialRotation');
			$this->lConf['sliceSequentialFactor']   = $this->getFlexformData('movement', 'sliceSequentialFactor');
			$this->lConf['sliceSpeed3d']            = $this->getFlexformData('movement', 'sliceSpeed3d');
			$this->lConf['sliceSlideshow']          = $this->getFlexformData('movement', 'sliceSlideshow');
			$this->lConf['sliceSlideshowTime']      = $this->getFlexformData('movement', 'sliceSlideshowTime');
			$this->lConf['sliceEasing']             = $this->getFlexformData('movement', 'sliceEasing');

			$this->lConf['sliceSpeed']         = $this->getFlexformData('fallback', 'sliceSpeed');

			$this->lConf['options']         = $this->getFlexformData('special', 'options');
			$this->lConf['optionsOverride'] = $this->getFlexformData('special', 'optionsOverride');

			// define the key of the element
			$this->setContentKey("imagecycle-slice_c" . $this->uid);

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
			if ($this->lConf['sliceColorHiddenSides'] && $this->lConf['sliceColorHiddenSides'] != 'on') {
				$this->conf['sliceColorHiddenSides'] = $this->lConf['sliceColorHiddenSides'];
			}

			if ($this->lConf['sliceOrientation']) {
				$this->conf['sliceOrientation'] = $this->lConf['sliceOrientation'];
			}
			if ($this->lConf['slicePerspective']) {
				$this->conf['slicePerspective'] = $this->lConf['slicePerspective'];
			}
			if ($this->lConf['sliceSlicesCount']) {
				$this->conf['sliceSlicesCount'] = $this->lConf['sliceSlicesCount'];
			}
			if ($this->lConf['sliceDisperseFactor']) {
				$this->conf['sliceDisperseFactor'] = $this->lConf['sliceDisperseFactor'];
			}
			if ($this->lConf['sliceSequentialRotation']) {
				$this->conf['sliceSequentialRotation'] = $this->lConf['sliceSequentialRotation'];
			}
			if ($this->lConf['sliceSequentialFactor']) {
				$this->conf['sliceSequentialFactor'] = $this->lConf['sliceSequentialFactor'];
			}
			if ($this->lConf['sliceSpeed3d']) {
				$this->conf['sliceSpeed3d'] = $this->lConf['sliceSpeed3d'];
			}
			if ($this->lConf['sliceEasing']) {
				$this->conf['sliceEasing'] = $this->lConf['sliceEasing'];
			}
			if ($this->lConf['sliceSpeed']) {
				$this->conf['sliceSpeed'] = $this->lConf['sliceSpeed'];
			}
			if ($this->lConf['sliceSlideshow'] < 2) {
				$this->conf['sliceSlideshow'] = $this->lConf['sliceSlideshow'];
			}
			if ($this->lConf['sliceSlideshowTime']) {
				$this->conf['sliceSlideshowTime'] = $this->lConf['sliceSlideshowTime'];
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
			foreach ($GLOBALS['TSFE']->rootLine as $page) {
				if (! $pageID) {
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
			$this->setContentKey("imagecycle-slice_key");
		}

		if (! $this->conf['imagewidth']) {
			$this->conf['imagewidth'] = "200c";
		}
		if (! $this->conf['imageheight']) {
			$this->conf['imageheight'] = "200c";
		}

		// wrap if integer
		if (is_numeric($this->conf['imagewidth'])) {
			$this->conf['imagewidth'] = $this->cObj->stdWrap($this->conf['imagewidth'], $this->conf['integerWidthWrap.']);
		}
		if (is_numeric($this->conf['imageheight'])) {
			$this->conf['imageheight'] = $this->cObj->stdWrap($this->conf['imageheight'], $this->conf['integerHeightWrap.']);
		}

		// define the css file
		$this->pagerenderer->addCssFile($this->conf['cssFileSlice']);

		// We have to build the images first to get the maximum width and height
		$returnString = null;
		$images = null;
		$no_script = null;
		$GLOBALS['TSFE']->register['key'] = $this->getContentKey();
		$GLOBALS['TSFE']->register['imagewidth']  = $this->conf['imagewidth'];
		$GLOBALS['TSFE']->register['imageheight'] = $this->conf['imageheight'];
		$GLOBALS['TSFE']->register['showcaption'] = $this->conf['showcaption'];
		$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = 0;
		$GLOBALS['TSFE']->register['IMAGE_COUNT'] = count($data);
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $item) {
				$GLOBALS['TSFE']->register['caption_key'] = $this->getContentKey() . "-" .$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'];
				$image = null;
				$imgConf = $this->conf['slice.'][$this->type.'.']['image.'];
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
				// Add the noscript wrap to the first image
				if ($key == 0) {
					$no_script = $this->cObj->stdWrap($image, $this->conf['slice.'][$this->type.'.']['noscriptWrap.']);
				}
				$images .= $image;
				$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] ++;
			}
			// the stdWrap
			$returnString = $this->cObj->stdWrap($images, $this->conf['slice.'][$this->type.'.']['stdWrap.']);
			$returnString .= $no_script;
		}

		// define the jQuery mode and function
		if ($this->conf['jQueryNoConflict']) {
			$jQueryNoConflict = "jQuery.noConflict();";
		} else {
			$jQueryNoConflict = "";
		}

		$options = array();

		if ($this->conf['sliceColorHiddenSides']) {
			$options['colorHiddenSides'] = "colorHiddenSides: '#".str_replace('#', '', $this->conf['sliceColorHiddenSides'])."'";
		}

		if (in_array($this->conf['sliceOrientation'], array('v', 'h', 'r'))) {
			$options['orientation'] = "orientation: '".$this->conf['sliceOrientation']."'";
		}
		if ($this->conf['slicePerspective'] > 0) {
			$options['perspective'] = "perspective: {$this->conf['slicePerspective']}";
		}
		if ($this->conf['sliceSlicesCount'] > 0) {
			$options['cuboidsCount'] = "cuboidsCount: {$this->conf['sliceSlicesCount']}";
		}
		if ($this->conf['sliceDisperseFactor'] > 0) {
			$options['disperseFactor'] = "disperseFactor: {$this->conf['sliceDisperseFactor']}";
		}
		$options['sequentialRotation'] = "sequentialRotation: ".($this->conf['sliceSequentialRotation'] ? 'true' : 'false');
		if ($this->conf['sliceSequentialFactor'] > 0) {
			$options['sequentialFactor'] = "sequentialFactor: {$this->conf['sliceSequentialFactor']}";
		}
		if ($this->conf['sliceSpeed3d'] > 0) {
			$options['speed'] = "speed: {$this->conf['sliceSpeed3d']}";
		}
		/* FALLBACK*/
		if ($this->conf['sliceEasing']) {
			$options['easing'] = "easing: '{$this->conf['sliceEasing']}'";
		}
		if ($this->conf['sliceSpeed'] > 0) {
			$options['fallbackFadeSpeed'] = "fallbackFadeSpeed: {$this->conf['sliceSpeed']}";
		}
		/* SLIDESHOW */
		$options['slideshow'] = "autoplay: ".($this->conf['sliceSlideshow'] ? 'true' : 'false');
		if ($this->conf['sliceSlideshowTime'] > 0) {
			$options['slideshowTime'] = "interval: {$this->conf['sliceSlideshowTime']}";
		}
		// overwrite all options if set
		if (trim($this->conf['options'])) {
			if ($this->conf['optionsOverride']) {
				$options = array($this->conf['options']);
			} else {
				$options['options'] = $this->conf['options'];
			}
		}

		// checks if t3jquery is loaded
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			$this->pagerenderer->addJsFile($this->conf['jQueryLibrary'], true);
			$this->pagerenderer->addJsFile($this->conf['jQueryEasing']);
		}

		// define the js file
		$this->pagerenderer->addJsFile($this->conf['modernizer']);
		$this->pagerenderer->addJsFile($this->conf['jQuerySlice']);

		// The template for JS
		if (! $this->templateFileJS = $this->cObj->fileResource($this->conf['templateFileJS'])) {
			$this->templateFileJS = $this->cObj->fileResource("EXT:imagecycle/res/tx_imagecycle.js");
		}

		// get the Template of the Javascript
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_SLICEBOX_JS###"))) {
			$templateCode = "alert('Template TEMPLATE_SLICEBOX_JS is missing')";
		}

		// define the markers
		$markerArray = array();
		$markerArray["KEY"]     = $this->getContentKey();
		$markerArray["OPTIONS"] = implode(",\n				", $options);

		// set the markers
		$templateCode = $this->cObj->substituteMarkerArray($templateCode, $markerArray, '###|###', 0);

		$this->pagerenderer->addJS($jQueryNoConflict . $templateCode);

		// Add the ressources
		$this->pagerenderer->addResources();

		if ($onlyJS === true) {
			return true;
		}

		return $returnString;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/pi5/class.tx_imagecycle_pi5.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/pi5/class.tx_imagecycle_pi5.php']);
}

?>