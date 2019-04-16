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

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use function GuzzleHttp\json_decode;

class MetaTagViewHelper extends AbstractViewHelper {

    public function initializeArguments() {
        $this->registerArgument('order', 'int', 'Ordering int', true);
        $this->registerArgument('data', 'array', 'Array', false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
        $renderChildren = $renderChildrenClosure();
        if(!empty(trim($renderChildren))) {
            $newHeaderData = json_decode($renderChildren, true);
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag'][$arguments['order']] = $newHeaderData;
        } else if(isset($arguments['data'])) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag'][$arguments['order']] = $arguments['data'];
        }
    }
}
