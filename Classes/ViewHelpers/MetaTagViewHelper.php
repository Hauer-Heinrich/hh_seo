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
 *  EXAMPLE: Resources/Private/Templates/Example.html
 */

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use function GuzzleHttp\json_decode;

class MetaTagViewHelper extends AbstractViewHelper {

    public function initializeArguments() {
        $this->registerArguments([
            ['order', 'int', 'Ordering int', true],
            ['type', 'string', 'headerData or custom Data Type', false, 'headerData'],
            ['title', 'string', 'Title-Tag'],
            ['titleBefore', 'string', 'Title before string'],
            ['titleAfter', 'string', 'Title after string'],
            ['titleSeparate', 'string', 'Title seperator'],
            ['titleSeparateBefore', 'string', 'Title seperator before (overrides titleSeparate)'],
            ['titleSeparateAfter', 'string', 'Title seperator after (overrides titleSeparate)'],
            ['description', 'string', 'Description-Tag'],

            ['designer', 'string', 'Designer'],
            ['theme-color', 'string', 'theme-color'],
            ['touchIcon', 'string', 'touch icon - output for devers gadgets, shouldbe 310x310px'],
            ['imagetoolbar', 'boolean', 'Imagetoolbar - boolean'],
            ['format-detection', 'boolean', 'Autoformat phonenumbers - on various gadgets'],
            ['last-modified', 'int', 'last-modified as timestamp'],
            ['author', 'string', 'Author'],
            ['copyright', 'string', 'Copyright'],

            ['robots-index', 'string', 'robots index'],
            ['robots-follow', 'string', 'robots follow'],

            ['og-title', 'string', 'OpenGraph title e. g. for facebook'],
            ['og-description', 'string', 'OpenGraph description e. g. for facebook'],
            ['og-image', 'string', 'OpenGraph image absolute path e. g. for facebook'],

            ['twitter-title', 'string', 'Twitter title'],
            ['twitter-description', 'string', 'Twitter description'],
            ['twitter-image', 'string', 'Twitter image absolute path'],

            ['geo-region', 'string', 'Countrycode - regioncode e. g. DE-BY for Germany-Bavaria'],
            ['geo-placename', 'string', 'City name'],
            ['geo-position:long', 'double', 'longitude'],
            ['geo-position:lat', 'double', 'latitude'],
            ['override', 'boolean', 'Overwrites the data with lower order completely', false]
        ]);
    }

    function registerArguments(Array $registers){
        foreach($registers as $registerKey => $registerVal){
            $this->registerArgument(...$registerVal);
        }
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
        $dataType = $arguments["type"];
        $dataArray[$dataType] = $arguments;
        $childData = [];
        $renderChildren = $renderChildrenClosure();

        if(!empty(trim($renderChildren))) {
            $childData[$dataType] = reset(json_decode($renderChildren, true));
            $childData['override'] = $arguments['override'];
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag'][$arguments['order']] = array_replace_recursive($dataArray, $childData);
    }
}
