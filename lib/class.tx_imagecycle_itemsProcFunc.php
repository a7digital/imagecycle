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


/**
 * 'itemsProcFunc' for the 'imagecycle' extension.
 *
 * @author     Juergen Furrer <juergen.furrer@gmail.com>
 * @package    TYPO3
 * @subpackage tx_imagecycle
 */
class tx_imagecycle_itemsProcFunc
{
	/**
	 * Get defined Effects for dropdown
	 * @return array
	 */
	public function getEffects($config, $item)
	{
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['imagecycle']);
		$availableEffects = t3lib_div::trimExplode(",", $confArr['effects'], true);
		if (count($availableEffects) < 1) {
			$availableEffects = array('none','blindX','blindY','blindZ','cover','curtainX','curtainY','fade','fadeout','fadeZoom','growX','growY','scrollUp','scrollDown','scrollLeft','scrollRight','scrollHorz','scrollVert','shuffle','slideX','slideY','toss','turnUp','turnDown','turnLeft','turnRight','uncover','wipe','zoom','all');
		}
		$pageTS = t3lib_BEfunc::getPagesTSconfig($config['row']['pid']);
		$imagecycleEffects = t3lib_div::trimExplode(",", $pageTS['mod.']['imagecycle.']['effects'], true);
		$optionList = array();
		if (is_array($imagecycleEffects) && count($imagecycleEffects) > 0) {
			foreach ($availableEffects as $key => $availableEffect) {
				if (in_array(trim($availableEffect), $imagecycleEffects)) {
					$optionList[] = array(
						trim($availableEffect),
						trim($availableEffect),
					);
				}
			}
		} else {
			foreach ($availableEffects as $key => $availableEffect) {
				$optionList[] = array(
					trim($availableEffect),
					trim($availableEffect),
				);
			}
		}
		if (isset($config['items']) && is_array($config['items'])) {
			$config['items'] = array_merge($config['items'], $optionList);
		}
		return $config;
	}

	/**
	 * Get defined Effects for dropdown
	 * @return array
	 */
	public function getEffectsCoin($config, $item)
	{
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['imagecycle']);
		$availableEffects = t3lib_div::trimExplode(",", $confArr['effectsCoin'], true);
		if (count($availableEffects) < 1) {
			$availableEffects = array('random','swirl','rain','straight');
		}
		$pageTS = t3lib_BEfunc::getPagesTSconfig($config['row']['pid']);
		$imagecycleEffects = t3lib_div::trimExplode(",", $pageTS['mod.']['imagecycle.']['effectsCoin'], true);
		$optionList = array();
		if (is_array($imagecycleEffects) && count($imagecycleEffects) > 0) {
			foreach ($availableEffects as $key => $availableEffect) {
				if (in_array(trim($availableEffect), $imagecycleEffects)) {
					$optionList[] = array(
						trim($availableEffect),
						trim($availableEffect),
					);
				}
			}
		} else {
			foreach ($availableEffects as $key => $availableEffect) {
				$optionList[] = array(
					trim($availableEffect),
					trim($availableEffect),
				);
			}
		}
		if (isset($config['items']) && is_array($config['items'])) {
			$config['items'] = array_merge($config['items'], $optionList);
		}
		return $config;
	}

	/**
	 * Get defined Effects for dropdown
	 * @return array
	 */
	public function getEffectsNivo($config, $item)
	{
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['imagecycle']);
		$availableEffects = t3lib_div::trimExplode(",", $confArr['effectsNivo'], true);
		if (count($availableEffects) < 1) {
			$availableEffects = array('random','sliceDown','sliceDownLeft','sliceUp','sliceUpLeft','sliceUpDown','sliceUpDownLeft','fold','fade','slideInRight','slideInLeft', 'boxRandom', 'boxRain', 'boxRainReverse', 'boxRainGrow', 'boxRainGrowReverse');
		}
		$pageTS = t3lib_BEfunc::getPagesTSconfig($config['row']['pid']);
		$imagecycleEffects = t3lib_div::trimExplode(",", $pageTS['mod.']['imagecycle.']['effectsNivo'], true);
		$optionList = array();
		if (is_array($imagecycleEffects) && count($imagecycleEffects) > 0) {
			foreach ($availableEffects as $key => $availableEffect) {
				if (in_array(trim($availableEffect), $imagecycleEffects)) {
					$optionList[] = array(
						trim($availableEffect),
						trim($availableEffect),
					);
				}
			}
		} else {
			foreach ($availableEffects as $key => $availableEffect) {
				$optionList[] = array(
					trim($availableEffect),
					trim($availableEffect),
				);
			}
		}
		if (isset($config['items']) && is_array($config['items'])) {
			$config['items'] = array_merge($config['items'], $optionList);
		}
		return $config;
	}

	/**
	* Get defined Skin for dropdown
	* @return array
	*/
	public function getThemesNivo($config, $item)
	{
		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['imagecycle']);
		if (! is_dir(t3lib_div::getFileAbsFileName($confArr['nivoThemeFolder']))) {
			// if the defined folder does not exist, define the default folder
			t3lib_div::devLog('Path \''.$confArr['nivoThemeFolder'].'\' does not exist', 'imagecycle', 1);
			$confArr['nivoThemeFolder'] = "EXT:imagecycle/res/css/nivoslider/";
		}

		// get the selected item
		$configPi = array();
		if (! is_array($config['row']['pi_flexform']) && $config['row']['pi_flexform'])	{
			$configPi = t3lib_div::xml2array($config['row']['pi_flexform']);
			if (! is_array($configPi)) {
				$configPi = array();
			}
		}
		$theme = 'default';
		if (isset($configPi['data']['settings']['lDEF']['nivoTheme']['vDEF'])) {
			$theme = $configPi['data']['settings']['lDEF']['nivoTheme']['vDEF'];
		}

		// 
		$info_text = NULL;
		if (class_exists(t3lib_utility_VersionNumber) && t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4003000) {
			if (file_exists(t3lib_div::getFileAbsFileName($confArr['nivoThemeFolder'] . $theme . '/readme.txt'))) {
				$info_text = $GLOBALS['LANG']->sL(file_get_contents(t3lib_div::getFileAbsFileName($confArr['nivoThemeFolder'] . $theme . '/readme.txt')));
				$msg = t3lib_div::makeInstance('t3lib_FlashMessage', $info_text, $GLOBALS['LANG']->sL('LLL:EXT:imagecycle/locallang.xml:pi3_theme_info'), t3lib_FlashMessage::INFO);
				t3lib_FlashMessageQueue::addMessage($msg);
			}
		}

		$items = t3lib_div::get_dirs(t3lib_div::getFileAbsFileName($confArr['nivoThemeFolder']));
		if (is_array($items) && count($items) > 0) {
			$optionList = array();
			foreach ($items as $key => $item) {
				$item = trim($item);
				if (! preg_match('/^\./', $item)) {
					if (file_exists(t3lib_div::getFileAbsFileName($confArr['nivoThemeFolder']) . $item . '/style.css')) {
						$optionList[] = array(
							$item,
							$item,
						);
					}
				}
			}
			if (isset($config['items']) && is_array($config['items'])) {
				$config['items'] = array_merge($config['items'], $optionList);
			}
		}
		return $config;
	}

	/**
	 * Get all modes for image selection
	 * @return array
	 */
	public function getModes($config, $item)
	{
		$optionList = array();
		$optionList[] = array(
			$GLOBALS['LANG']->sL('LLL:EXT:imagecycle/locallang_db.xml:tt_content.pi_flexform.mode.I.upload'),
			"upload",
			"EXT:imagecycle/mode_upload.gif"
		);
		if ($config['config']['displayMode'] != 'page') {
			$optionList[] = array(
				$GLOBALS['LANG']->sL('LLL:EXT:imagecycle/locallang_db.xml:tt_content.pi_flexform.mode.I.rte'),
				"uploadRTE",
				"EXT:imagecycle/mode_rte.gif"
			);
			$optionList[] = array(
				$GLOBALS['LANG']->sL('LLL:EXT:imagecycle/locallang_db.xml:tt_content.pi_flexform.mode.I.data'),
				"uploadData",
				"EXT:imagecycle/mode_data.gif"
			);
		}
		if (t3lib_extMgm::isLoaded("dam")) {
			$optionList[] = array(
				$GLOBALS['LANG']->sL('LLL:EXT:imagecycle/locallang_db.xml:tt_content.pi_flexform.mode.I.dam'),
				"dam",
				"EXT:imagecycle/mode_dam.gif"
			);
			if (t3lib_extMgm::isLoaded("dam_catedit")) {
				$optionList[] = array(
					$GLOBALS['LANG']->sL('LLL:EXT:imagecycle/locallang_db.xml:tt_content.pi_flexform.mode.I.dam_catedit'),
					"dam_catedit",
					"EXT:imagecycle/mode_damcat.gif"
				);
			}
		}
		if (isset($config['items']) && is_array($config['items'])) {
			$config['items'] = array_merge($config['items'], $optionList);
		}
		return $config;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/lib/class.tx_imagecycle_itemsProcFunc.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/lib/class.tx_imagecycle_itemsProcFunc.php']);
}
?>