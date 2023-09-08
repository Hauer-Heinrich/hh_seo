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

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use \Symfony\Component\Yaml\Yaml;

class MetaTagViewHelper extends AbstractViewHelper {

    public function initializeArguments(): void {
        $this->registerArguments([
            ['dataType', 'string', '', false],
            ['order', 'int', 'Ordering int', true],
            ['type', 'string', 'headerData or custom Data Type', false, 'headerData'],
            ['overwrite', 'boolean', 'Overwrites the data with lower order completely', false]
        ]);
    }

    function registerArguments(Array $registers): void {
        foreach($registers as $registerVal) {
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
        $type = $arguments['type'];
        $dataType = isset($arguments['dataType']) ? $arguments['dataType'] : 'ini';
        $dataArray[$type] = $arguments;
        $childData = [];
        $renderChildren = $renderChildrenClosure();

        if(!empty(trim($renderChildren))) {
            if($dataType === 'yaml') {
                $iniArrayUnformated = Yaml::parse($renderChildren);
            } else {
                $iniArrayUnformated = parse_ini_string($renderChildren, true, INI_SCANNER_RAW);
            }

            if(\is_array($iniArrayUnformated)) {
                foreach ($iniArrayUnformated as $key => $value) {
                    if(\is_string($value)) {
                        $iniArrayUnformated[$key] = \urldecode($value);
                    }
                }
            } else {
                $logger = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager')->getLogger(__CLASS__);
                $logger->error('EXT:hh_seo -> MetaTagViewHelper: parse_ini_string = false', ['iniArrayUnformated' => $iniArrayUnformated]);
            }
            $childData[$type] = $iniArrayUnformated;
            $childData['overwrite'] = $arguments['overwrite'];
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']['MetaTag'][$arguments['order']] = array_replace_recursive($dataArray, $childData);
    }
}
