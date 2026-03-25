<?php
namespace HauerHeinrich\HhSeo\DataProcessing;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use \TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class ConstantsProcessor implements DataProcessorInterface {

    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array {
        $settings = isset($contentObjectConfiguration['settings.']) ? $contentObjectConfiguration['settings.'] : [];

        $targetVariableName = $cObj->stdWrapValue('as', $processorConfiguration, 'hhSeo');
        $processedData[$targetVariableName] = $settings;

        return $processedData;
    }
}
