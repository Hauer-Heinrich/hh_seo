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
 *   xmlns:seo="http://typo3.org/ns/HauerHeinrich/HhSeo/ViewHelpers"
 *   data-namespace-typo3-fluid="true">
 *
 *  EXAMPLE: Resources/Private/Templates/Example.html
 */

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FormatIniViewHelper extends AbstractViewHelper {

    public function initializeArguments() {
        $this->registerArguments([
            ['data', 'string', 'String', false],
            ['strip-tags', 'bool', 'Delete HTML-Tags?', false, false],
            ['urlencode', 'bool', 'Urlencode string if string contains chars like $&!()', false, false]
        ]);
    }

    function registerArguments(Array $registers){
        foreach($registers as $registerVal){
            $this->registerArgument(...$registerVal);
        }
    }

    /**
     * make multiline string to single line string
     * deletes unauthorized characters
     * escapes douple qoutes
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     */
    public static function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
        $renderChildren = $renderChildrenClosure() ? trim($renderChildrenClosure()) : $arguments['data'];

        if(empty($renderChildren)) {
            return '';
        }

        if(isset($arguments['strip-tags']) && $arguments['strip-tags'] == true) {
            $renderChildren = \strip_tags($renderChildren);
        }

        if(isset($arguments['urlencode']) && $arguments['urlencode'] == true) {
            $renderChildren = \urlencode($renderChildren);
        }

        $formated = str_replace(["\r\n", "\n", "\r"], '', $renderChildren);

        // $stringReplace = ['"', "'",'´', '´', '<', '>'];
        // $stringReplaceWidth = ['\"', '', '', '', '', ''];
        // $result = trim(str_replace($stringReplace, $stringReplaceWidth, $formated));

        return trim($formated);
    }
}
