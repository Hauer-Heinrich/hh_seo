<?php
declare(strict_types = 1);

namespace HauerHeinrich\HhSeo\Helpers;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class MetaTagGenerator {

    /**
     * My example function
     *
     * @param string $property
     * @param string $content
     * @return string
    */
    public function getPropertyMetaTag($property, $content) {
        return "<meta property='{$property}' content='{$content}'>";
    }

    /**
     * My example function
     *
     * @param string $title
     * @param string $before
     * @param string $after
     * @param string $separate
     * @param string $separateBefore
     * @param string $separateAfter
     * @return string
    */
    public function getTitleTag($title, $before = "", $after = "", $separate = " - ", $separateBefore = "", $separateAfter = "") {
        $sepBefore = "";
        $sepAfter = "";

        if(!empty($before)) {
            $sepBefore = $separateBefore ? $before . $separateBefore : $before . $separate;
        }

        if(!empty($after)) {
            $sepAfter = $separateAfter ? $separateAfter . $after : $separate . $after;
        }

        $fullTitle = $sepBefore . $title . $sepAfter;
        return "<title>{$fullTitle}</title>";
    }

    /**
     * My example function
     *
     * @param string $property
     * @param string $content
     * @return string
    */
    public function getNameMetaTag($property, $content) {
        return "<meta name='{$property}' content='{$content}'>";
    }

    /**
     * My example function
     *
     * @param string $content
     * @return string
    */
    public function getCustomTag($content) {
        return $content;
    }
}
