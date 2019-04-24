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

use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use function GuzzleHttp\json_decode;

class MetaTagViewHelper extends AbstractViewHelper {

    public function initializeArguments() {
        $this->registerArgument('order', 'int', 'Ordering int', true);

        $this->registerArgument('title', 'string', 'Title-Tag');
        $this->registerArgument('titleBefore', 'string', 'Title before string');
        $this->registerArgument('titleAfter', 'string', 'Title after string');
        $this->registerArgument('titleSeparate', 'string', 'Title seperator');
        $this->registerArgument('titleSeparateBefore', 'string', 'Title seperator before (overrides titleSeparate)');
        $this->registerArgument('titleSeparateAfter', 'string', 'Title seperator after (overrides titleSeparate)');
        $this->registerArgument('description', 'string', 'Description-Tag');

        $this->registerArgument('designer', 'string', 'Designer');
        $this->registerArgument('theme-color', 'string', 'theme-color');
        $this->registerArgument('touchIcon', 'string', 'touch icon - output for devers gadgets, shouldbe 310x310px');
        $this->registerArgument('imagetoolbar', 'boolean', 'Imagetoolbar - boolean');
        $this->registerArgument('format-detection', 'boolean', 'Autoformat phonenumbers - on various gadgets');
        $this->registerArgument('last-modified', 'int', 'last-modified as timestamp');
        $this->registerArgument('author', 'string', 'Author');
        $this->registerArgument('copyright', 'string', 'Copyright´');

        $this->registerArgument('robots-index', 'string', 'robots index');
        $this->registerArgument('robots-follow', 'string', 'robots follow');

        $this->registerArgument('og-title', 'string', 'OpenGraph title e. g. for facebook');
        $this->registerArgument('og-description', 'string', 'OpenGraph description e. g. for facebook');
        $this->registerArgument('og-image', 'string', 'OpenGraph image absolute path e. g. for facebook');

        $this->registerArgument('twitter-title', 'string', 'Twitter title');
        $this->registerArgument('twitter-description', 'string', 'Twitter description');
        $this->registerArgument('twitter-image', 'string', 'Twitter image absolute path');

        $this->registerArgument('geo-region', 'string', 'Countrycode - regioncode e. g. DE-BY for Germany-Bavaria');
        $this->registerArgument('geo-placename', 'string', 'City name');
        $this->registerArgument('geo-position:long', 'double', 'longitude');
        $this->registerArgument('geo-position:lat', 'double', 'latitude');
        $this->registerArgument('override', 'boolean', 'Overwrites the data with lower order completely', false);
    }

    /**
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
        $dataArray = [
            'headerData' => $arguments
        ];
        $newHeaderData = [];
        $headerData = [];

        $renderChildren = $renderChildrenClosure();
        if(!empty(trim($renderChildren))) {
            $newHeaderData = json_decode($renderChildren, true);
            $headerData = $newHeaderData[0];
            $headerData['override'] = $arguments['override'];
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag'][$arguments['order']] = array_replace_recursive($headerData, $dataArray);
    }
}
