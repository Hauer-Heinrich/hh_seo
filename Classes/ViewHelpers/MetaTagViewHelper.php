<?php
namespace HauerHeinrich\HhSeo\ViewHelpers;

/***************************************************************
 * Copyright notice
 *
 * (c) 2018 Christian Hackl <hackl.chris@googlemail.com>
 * All rights reserved
 *
 * This script is part of the TYPO3 project. The TYPO3 project is
 * free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 *
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * This copyright notice MUST APPEAR in all copies of the script!
 * Example
 * <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
 *   xmlns:hh="http://typo3.org/ns/VENDOR/NAMESPACE/ViewHelpers"
 *   data-namespace-typo3-fluid="true">
 *
 *  <hh:MetaTag type="title">
 *  or
 *  <hh:MetaTag type="title" string="my new title">
 */

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class MetaTagViewHelper extends AbstractViewHelper {
    public function initializeArguments() {
        $this->registerArgument('order', 'int', 'Ordering int', true);
        $this->registerArgument('type', 'string', 'def. type', false);
        $this->registerArgument('title', 'string', 'New title string', false);
        $this->registerArgument('description', 'string', 'New description string', false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {

        if((int)$arguments['order'] > (int)$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag']['order']) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag']['order'] = (int)$arguments['order'];

            if(null !== $renderChildrenClosure()) {
                $newHeaderData = json_decode($renderChildrenClosure(), true);
                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], $newHeaderData);
            } else {
                $headerChanges = [];

                $headerTags = [];
                foreach ($arguments as $key => $value) {
                    if ($key !== 'order' && $key !== 'type') {
                        $headerTags[$key] = $value;
                    }
                }
                $headerChanges['headerData'] = $headerTags;

                $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'] = array_replace_recursive($GLOBALS['TYPO3_CONF_VARS'], $headerChanges);
            }
        }
    }
}