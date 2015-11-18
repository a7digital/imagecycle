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
class tx_imagecycle_TCAform
{
	/**
	 * The uploadRTE section will be hidden
	 * @return array
	 */
	function hideRTE($PA, $fobj)
	{
		if (class_exists(t3lib_utility_VersionNumber) && t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4004000) {
			$classes = array (
				'.t3-form-field-label-flexsection',
				'.t3-form-field-toggle-flexsection',
				'.t3-form-field-container-flexsection',
				'.t3-form-field-add-flexsection',
			);
			return t3lib_div::wrapJS("$$('".implode(',', $classes)."').each(function(n){n.hide();});");
		} elseif (class_exists(t3lib_utility_VersionNumber) && t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4003000) {
			return t3lib_div::wrapJS("$$('div.bgColor2').each(function(n){n.next(0).hide();n.next(1).hide();n.next(2).hide();n.hide();})");
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/lib/class.tx_imagecycle_TCAform.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/imagecycle/lib/class.tx_imagecycle_TCAform.php']);
}
?>