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
        $settings = [];
        if(isset($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hhseo.'])) {
            $settings = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_hhseo.'];
        } else {
            $settings = isset($contentObjectConfiguration['settings.']) ? $contentObjectConfiguration['settings.'] : [];
        }
        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'hhSeo');
        $processedData[$targetVariableName] = $settings;

        return $processedData;
    }
}
