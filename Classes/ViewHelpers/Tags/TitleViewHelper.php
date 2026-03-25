<?php
namespace HauerHeinrich\HhSeo\ViewHelpers\Tags;

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
 *   xmlns:seo="http://typo3.org/ns/VENDOR/NAMESPACE/ViewHelpers"
 *   data-namespace-typo3-fluid="true">
 *
 *  EXAMPLE: Resources/Private/Templates/Example.html
 */

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use \TYPO3\CMS\Core\Utility\GeneralUtility;

class TitleViewHelper extends AbstractViewHelper {

    public function initializeArguments() {
        $this->registerArguments([
            ['order', 'int', 'Ordering int', true],
            ['content', 'string', 'title meta-tag', true],
            ['before', 'string', 'content before title', false],
            ['after', 'string', 'content after title', false],
            ['separate', 'string', 'title separate fallback', false],
            ['beforeSeparate', 'string', 'title separate before', false],
            ['afterSeparate', 'string', 'title separate after', false],
        ]);
    }

    function registerArguments(Array $registers){
        foreach($registers as $registerKey => $registerVal){
            $this->registerArgument(...$registerVal);
        }
    }

    public function render(): string {
        $configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');

        $typoScript = $configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'hhseo'
        );

        $separate = $this->arguments['separate'] ? $this->arguments['separate'] : ' ' . $typoScript['separate'] . ' ';
        $bSeparate = $this->arguments['beforeSeparate'] ? $this->arguments['beforeSeparate'] : ' ' . $typoScript['beforeSeparate'] . ' ';
        $aSeparate = $this->arguments['afterSeparate'] ? $this->arguments['afterSeparate'] : ' ' . $typoScript['afterSeparate'] . ' ';

        $beforeSeparate = '';
        if(!empty($this->arguments['before'])) {
            $beforeSeparate = trim($bSeparate) ? $this->arguments['before'] . $bSeparate : $this->arguments['before'] . $separate;
        }

        $afterSeparate = '';
        if(!empty($this->arguments['after'])) {
            $afterSeparate = trim($aSeparate) ? $aSeparate . $this->arguments['after'] : $separate . $this->arguments['after'];
        }

        $content = $beforeSeparate . $this->arguments['content'] . $afterSeparate;
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hh_seo']['title'][$this->arguments['order']] = trim($content);

        return '';
    }
}
