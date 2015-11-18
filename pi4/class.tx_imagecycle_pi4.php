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
 * Plugin 'Cross-Slider' for the 'imagecycle' extension.
 *
 * @author	Juergen Furrer <juergen.furrer@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_imagecycle
 */
class tx_imagecycle_pi4 extends tx_imagecycle_pi1
{
	public $prefixId      = 'tx_imagecycle_pi4';
	public $scriptRelPath = 'pi4/class.tx_imagecycle_pi4.php';
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
		$this->setContentKey("imagecycle-cross");

		// set the system language
		$this->sys_language_uid = $GLOBALS['TSFE']->sys_language_content;

		// set the uid of the tt_content
		$this->uid = $this->cObj->data['_LOCALIZED_UID'] ? $this->cObj->data['_LOCALIZED_UID'] : $this->cObj->data['uid'];

		if ($this->cObj->data['list_type'] == $this->extKey.'_pi4') {
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

			$this->lConf['imagewidth']  = $this->getFlexformData('settings', 'imagewidth');
			$this->lConf['imageheight'] = $this->getFlexformData('settings', 'imageheight');

			$this->lConf['crossTransition']    = $this->getFlexformData('movement', 'crossTransition');
			$this->lConf['crossTransitionDir'] = $this->getFlexformData('movement', 'crossTransitionDir');
			$this->lConf['crossTime']          = $this->getFlexformData('movement', 'crossTime');
			$this->lConf['crossFade']          = $this->getFlexformData('movement', 'crossFade');
			$this->lConf['crossVariant']       = $this->getFlexformData('movement', 'crossVariant');
			$this->lConf['crossFromTo']        = $this->getFlexformData('movement', 'crossFromTo');

			$this->lConf['options']         = $this->getFlexformData('special', 'options');
			$this->lConf['optionsOverride'] = $this->getFlexformData('special', 'optionsOverride');

			// define the key of the element
			$this->setContentKey("imagecycle-cross_c" . $this->uid);

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
			if ($this->lConf['crossTransition']) {
				$this->conf['crossTransition'] = $this->lConf['crossTransition'];
			}
			if ($this->lConf['crossTransitionDir']) {
				$this->conf['crossTransitionDir'] = $this->lConf['crossTransitionDir'];
			}
			if ($this->lConf['crossTime']) {
				$this->conf['crossTime'] = $this->lConf['crossTime'];
			}
			if ($this->lConf['crossFade']) {
				$this->conf['crossFade'] = $this->lConf['crossFade'];
			}
			if ($this->lConf['crossFromTo']) {
				$this->conf['crossFromTo'] = $this->lConf['crossFromTo'];
			}
			// Will be overridden, if not "from TS"
			if ($this->lConf['crossVariant'] < 2) {
				$this->conf['crossVariant'] = $this->lConf['crossVariant'];
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

		$crossFromTo = t3lib_div::trimExplode(LF, $this->conf['crossFromTo']);

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
			list($from, $to) = t3lib_div::trimExplode('|', $crossFromTo[$i % count($crossFromTo)]);
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
				$data[$i]['from']  = $from;
				$data[$i]['to']    = $to;
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
			$this->setContentKey("imagecycle-cross_key");
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

		// We have to build the images first to get the maximum width and height
		$returnString = null;
		$imagesString = null;
		$images = array();
		$maxWidth = 0;
		$maxHeight = 0;
		$factor = 1;
		$no_script = null;
		$GLOBALS['TSFE']->register['key'] = $this->getContentKey();
		$GLOBALS['TSFE']->register['imagewidth']  = $this->conf['imagewidth'];
		$GLOBALS['TSFE']->register['imageheight'] = $this->conf['imageheight'];
		$GLOBALS['TSFE']->register['showcaption'] = $this->conf['showcaption'];
		$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] = 0;
		$GLOBALS['TSFE']->register['IMAGE_COUNT'] = count($data);
		if (is_array($data) && count($data) > 0) {
			foreach ($data as $key => $item) {
				$image = null;
				$imgConf = $this->conf['cross.'][$this->type.'.']['image.'];
				if (file_exists(t3lib_div::getIndpEnv("TYPO3_DOCUMENT_ROOT") . '/' . $item['image'])) {
					$totalImagePath = $item['image'];
				} else {
					$totalImagePath = $dir . $item['image'];
				}
				$GLOBALS['TSFE']->register['file']    = $totalImagePath;
				$GLOBALS['TSFE']->register['href']    = $item['href'];
				$GLOBALS['TSFE']->register['caption'] = $item['caption'];
				$GLOBALS['TSFE']->register['CURRENT_ID'] = $GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] + 1;
				$image = $this->cObj->IMAGE($imgConf);
				$lastImageInfo = $GLOBALS['TSFE']->lastImageInfo;
				if (intval($lastImageInfo[0] / $factor) > $maxWidth) {
					$maxWidth = intval($lastImageInfo[0] / $factor);
				}
				if (intval($lastImageInfo[1] / $factor) > $maxHeight) {
					$maxHeight = intval($lastImageInfo[1] / $factor);
				}
				// Add the noscript wrap to the firs image
				if ($key == 0) {
					$no_script = $this->cObj->stdWrap($image, $this->conf['cross.'][$this->type.'.']['noscriptWrap.']);
				}
				$images[] = array(
					'src'  => $lastImageInfo[3],
					'alt'  => $item['caption'],
					'from' => $item['from'],
					'to'   => $item['to'],
					'time' => ($this->conf['crossTime'] ? ($this->conf['crossTime'] / 1000) : 2),
				);
				$GLOBALS['TSFE']->register['IMAGE_NUM_CURRENT'] ++;
			}
			$returnString = $this->cObj->stdWrap(' ', $this->conf['cross.'][$this->type.'.']['stdWrap.']);
			$returnString .= $no_script;
			$imagesString = str_replace("\/", "/", json_encode($images));
		}

		// The template for JS
		if (! $this->templateFileJS = $this->cObj->fileResource($this->conf['templateFileJS'])) {
			$this->templateFileJS = $this->cObj->fileResource("EXT:imagecycle/res/tx_imagecycle.js");
		}

		// define the jQuery mode and function
		if ($this->conf['jQueryNoConflict']) {
			$jQueryNoConflict = "jQuery.noConflict();";
		} else {
			$jQueryNoConflict = "";
		}

		$this->pagerenderer->addCSS("
#{$this->getContentKey()} {
	width: {$maxWidth}px;
	height: {$maxHeight}px;
}");

		$options = array();

		if ($this->conf['crossFade'] > 0) {
			$options['fade'] = "fade: ".($this->conf['crossFade'] / 1000);
		}
		if ($this->conf['crossTransitionDir'] && $this->conf['crossTransition']) {
			$options['easing'] = "easing: 'ease{$this->conf['crossTransitionDir']}{$this->conf['crossTransition']}'";
		}
		$options['variant'] = "variant: ".($this->conf['crossVariant'] ? 'true' : 'false');

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
		$this->pagerenderer->addJsFile($this->conf['jQueryCross']);

		// define the css file
		$this->pagerenderer->addCssFile($this->conf['cssFileCross']);

		// get the Template of the Javascript
		if (! $templateCode = trim($this->cObj->getSubpart($this->templateFileJS, "###TEMPLATE_CROSSSLIDER_JS###"))) {
			$templateCode = "alert('Template TEMPLATE_CROSSSLIDER_JS is missing')";
		}

		// define the markers
		$markerArray = array();
		$markerArray["KEY"]     = $this->getContentKey();
		$markerArray["OPTIONS"] = implode(",\n		", $options);
		$markerArray["IMAGES"] = $imagesString;

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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/pi4/class.tx_imagecycle_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/pi4/class.tx_imagecycle_pi4.php']);
}

?>