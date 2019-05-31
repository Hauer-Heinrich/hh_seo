<?php
namespace HauerHeinrich\HhSeo\DataProcessing;

// use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ConstantsProcessor implements DataProcessorInterface {

    /**
     *
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData) {
        // $objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        // $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        // $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        // $settings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];
        // Result equals to the 4 lines above
        $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hhseo.'];

        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'hhSeo');
        $processedData[$targetVariableName] = $settings;
        return $processedData;
    }
}
