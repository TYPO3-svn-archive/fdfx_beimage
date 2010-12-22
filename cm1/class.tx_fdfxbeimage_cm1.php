<?php
/***************************************************************
*  Copyright notice
*
*  (c)  2006 Peter Russ (peter.russ@uon.li)  All rights reserved
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
*  
 * Addition of an item to the clickmenu
 *
 * @author	Peter Russ <peter.russ@uon.li>
 * @version $Id$
 */
class tx_fdfxbeimage_cm1
{
	function main(& $backRef, $menuItems, $table, $uid)
	{
		global $BE_USER, $TCA, $LANG;
		$fI = pathinfo($table);
		if (in_array(strtolower($fI['extension']), array ('gif', 'jpg', 'png')))
		{
			$localItems = array ();
			$localItems[] = 'spacer';
			if (!$backRef->cmLevel)
			{
				// Adds the regular item:
				$LL = $this->includeLL();
				// Repeat this (below) for as many items you want to add!
				// Remember to add entries in the localconf.php file for additional titles.
				$url = t3lib_extMgm :: extRelPath("fdfx_be_image")."cm1/index.php?id=".rawurlencode($table);
					$localItems[] = $backRef->linkItem($GLOBALS["LANG"]->getLLL("cm1_title", $LL), $backRef->excludeIcon('<img src="'.t3lib_extMgm :: extRelPath("fdfx_be_image").'cm1/cm_icon.gif" width="15" height="12" border=0 align=top>'), $backRef->urlRefForCM($url), 1);
					// Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
				// Find position of "delete" element:
				reset($menuItems);
				$c = 0;
				while (list ($k) = each($menuItems))
				{
					$c ++;
					if (!strcmp($k, "delete"))
						break;
				}
				// .. subtract two (delete item + divider line)
				$c -= 2;
				// ... and insert the items just before the delete element.
				array_splice($menuItems, $c, 0, $localItems);
				// Removes the view-item from clickmenu
				//				unset ($menuItems["view"]);
			}
		}
		return $menuItems;
	}
	/**
	 * Includes the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 */
	function includeLL()
	{
		return $GLOBALS['LANG']->includeLLFile('EXT:fdfx_be_image/locallang.xml',FALSE);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/class.tx_fdfxbeimage_cm1.php'])
{
	include_once ($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/fdfx_be_image/class.tx_fdfxbeimage_cm1.php']);
}
?>