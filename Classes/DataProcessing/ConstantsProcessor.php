<?php
namespace HauerHeinrich\HhSeo\DataProcessing;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use \TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ConstantsProcessor implements DataProcessorInterface {

    /**
     * process
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array {
        $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hhseo.'];

        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'hhSeo');
        $processedData[$targetVariableName] = $settings;

        return $processedData;
    }
}
