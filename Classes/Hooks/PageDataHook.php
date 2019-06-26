<?php
declare(strict_types = 1);

namespace HauerHeinrich\HhSeo\Hooks;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use PedroBorges\MetaTags\MetaTags;
use HauerHeinrich\HhSeo\Helpers\CanonicalGenerator;

class PageDataHook {

    /**
     * pluginSettings
     * @var array
     */
    protected $pluginSettings;


    /**
     * additionalData
     * @var array
     */
    protected $additionalData;

    /**
     * url
     * @var string
     */
    protected $url;

    /**
     * imageService
     * @var TYPO3\\CMS\\Extbase\\Service\\ImageService
     */
    protected $imageService;

    public function __construct() {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);

        $this->pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];
        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
        $this->imageService = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Service\\ImageService");

        DebuggerUtility::var_dump($extbaseFrameworkConfiguration);

        //$TSparserObject = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser::class);
        //$TSparserObject->parse($this->pluginSettings);
        // DebuggerUtility::var_dump($TSparserObject);

        $request = $GLOBALS['TYPO3_REQUEST'];
        $this->url = $request->getUri()->getScheme() . "://" . $request->getUri()->getHost();
    }

    /**
     * addPageData
     *
     * @param array $parameters
     * @return string
    */
    public function addPageData(&$parameters) {
        $metaTag = $this->additionalData['MetaTag'];

        if(!empty($metaTag)) {
            ksort($metaTag);

            $fluidData = [];
            foreach ($metaTag as $key => $value) {
                $data = $value['headerData'];

                if($value['overwrite'] == true) {
                    $fluidData = $data;
                } else {
                    foreach ($data as $dataKey => $dataValue) {
                        if (is_array($dataValue)) {
                            $errors = array_filter($dataValue);
                            if (!empty($errors)) {
                                $fluidData[$dataKey] = $dataValue;
                            }
                        } else if(is_string($dataValue) && !empty(trim($dataValue))) {
                            $fluidData[$dataKey] = strip_tags($dataValue);
                        } else if(!empty($dataValue)) {
                            $fluidData[$dataKey] = $dataValue;
                        }
                    }
                }
            }

            $newData = '';

            $tags = new MetaTags;

            if($fluidData['title']) {
                $separateBefore = str_replace("&nbsp;", " ", $fluidData['titleSeparateBefore'] ? $fluidData['titleSeparateBefore'] : $fluidData['titleSeparate']);
                $separateAfter = str_replace("&nbsp;", " ", $fluidData['titleSeparateAfter'] ? $fluidData['titleSeparateAfter'] : $fluidData['titleSeparate']);
                $title = $fluidData['titleBefore'] . $separateBefore . $fluidData['title'] . $separateAfter . $fluidData['titleAfter'];
                $tags->title($title);
            }

            if($fluidData['description']) {
                $tags->meta('description', $fluidData['description']);
            }

            if($fluidData['og:title']) {
                $tags->og("title", $fluidData['og:title']);
            }

            if($fluidData['og:description']) {
                $tags->og("description", $fluidData['og:description']);
            }

            if($fluidData['og:image']) {
                if(is_array($fluidData['og:image'])) {
                    foreach ($fluidData['og:image'] as $key => $value) {
                        $tags->og('image', $this->url . "/". $value);
                    }
                } else {
                    $tags->og('image', $this->url . "/". $fluidData['og:image']);
                }
            }

            if($fluidData['twitter:title']) {
                $tags->twitter("title", $fluidData['twitter:title']);
            }

            if($fluidData['twitter:description']) {
                $tags->twitter("description", $fluidData['twitter:description']);
            }

            if($fluidData['shortcutIcon']) {
                $image = $this->imageService->getImage($fluidData['shortcutIcon'], null, false);
                $imageUri = $this->imageService->getImageUri($image);
                $tags->link('shortcut icon', $imageUri);
            }

            if ($fluidData['touchIcon']) {
                $newData .= $this->setTouchIcons($fluidData['touchIcon']);
            }

            if ($fluidData['format-detection'] === "false") {
                $tags->meta('format-detection', 'telephone=no');
            } else if ($fluidData['format-detection'] === "true") {
                $tags->meta('format-detection', 'telephone=yes');
            }

            if ($fluidData['theme-color']) {
                $tags->meta('theme-color', $fluidData['theme-color']);
                $tags->meta('msapplication-TileColor', $fluidData['theme-color']);
            }

            if ($fluidData['last-modified']) {
                $date = gmdate('D, d M Y H:i:s \G\M\T', intval($fluidData['last-modified']));
                $tags->meta('Last-Modified', $date);
            }

            // geo data - position
            if($fluidData['geo:region']) {
                $tags->meta("geo:region", $fluidData['geo:region']);
            }

            if($fluidData['geo:placename']) {
                $tags->meta("geo:placename", $fluidData['geo:placename']);
            }

            if($fluidData['geo:position:long'] && $fluidData['geo:position:lat']) {
                $pos = $fluidData['geo:position:long'] . ";" . $fluidData['geo:position:lat'];
                $icbm = $fluidData['geo:position:long'] . ", " . $fluidData['geo:position:lat'];
                $tags->meta("geo:position", $pos);
                $tags->meta("ICBM", $icbm);
            }

            // Custom
            if ($fluidData['custom'] && is_array($fluidData['custom'])) {
                $newData .= $this->setCustomTags($fluidData['custom']);
            }

            // Robots
            if($fluidData['robots:index'] || $fluidData['robots:follow']) {
                $content = $fluidData['robots:index'];
                if (trim($content) != "" && $fluidData['robots:follow']) {
                    $content .= ',';
                }
                $content .= $fluidData['robots:follow'];
                $tags->meta("robots", $content);
            }

            // Author
            if($fluidData['author']) {
                $tags->meta("author", $fluidData['author']);
            }

            if($fluidData['copyright']) {
                $tags->meta("copyright", $fluidData['copyright']);
            }

            // set canonical path-string if set, for slot
            $canonicalGenerator = GeneralUtility::makeInstance(CanonicalGenerator::class);
            if(!empty($fluidData['canonical'])) {
                $newData .= $canonicalGenerator->generate($fluidData['canonical']);
            } else {
                $newData .= $canonicalGenerator->generate();
            }

            // output to HTML
            $result = $tags->render() . $newData;
            $parameters["headerData"][2] = $result;
        }
    }

    /**
     * Generate touchicon meta-tags
     *
     * @param string $iconPath Icon file path
     *
     * @return string
     */
    public function setTouchIcons($iconPath): string {
        $touchIcons = "";
        $image = $this->imageService->getImage($iconPath, null, false);

        $processingInstructions = [
            // Apple
            'apple' => [
                'tag' => '<link rel="apple-touch-icon-precomposed" sizes="%sx%s" href="%s">',
                'sizes' => [
                    [
                        'width' => '57',
                        'height' => '57'
                    ],
                    [
                        'width' => '76',
                        'height' => '76'
                    ],
                    [
                        'width' => '114',
                        'height' => '114'
                    ],
                    [
                        'width' => '128',
                        'height' => '128'
                    ],
                    [
                        'width' => '144',
                        'height' => '144'
                    ],
                    [
                        'width' => '180',
                        'height' => '180'
                    ],
                    [
                        'width' => '192',
                        'height' => '192'
                    ]
                ]
            ],
            'android' => [
                'tag' => '<link rel="icon" type="image/png" sizes="%sx%s" href="%s">',
                'sizes' => [
                    [
                        'width' => '16',
                        'height' => '16'
                    ],
                    [
                        'width' => '128',
                        'height' => '128'
                    ],
                    [
                        'width' => '192',
                        'height' => '192'
                    ]
                ]
            ],
            'microsoft' => [
                'tag' => '<meta name="msapplication-square%sx%slogo" content="%s" />',
                'sizes' => [
                    [
                        'width' => '70',
                        'height' => '70'
                    ],
                    [
                        'width' => '150',
                        'height' => '150'
                    ],
                    [
                        'width' => '310',
                        'height' => '310'
                    ],
                ]
            ],
            'others' => [
                'tag' => '<link rel="icon" type="image/png" sizes="%sx%s" href="%s">',
                'sizes' => [
                    [
                        'width' => '160',
                        'height' => '160'
                    ],
                    [
                        'width' => '96',
                        'height' => '96'
                    ],
                ]
            ]
        ];

        foreach ($processingInstructions as $key => $value) {
            $tag = $value['tag'];
            foreach ($value['sizes'] as $sizesKey => $sizesValue) {
                $processedImage = $this->imageService->applyProcessingInstructions($image, $sizesValue);
                $imageUri = $this->imageService->getImageUri($processedImage);
                $touchIcons .= sprintf($tag, $sizesValue['width'], $sizesValue['height'], $imageUri);
            }
        }

        return $touchIcons;
    }

    /**
     * Set your custom meta-tags
     *
     * @param array $customMetaTags
     *
     * @return void
     */
    public function setCustomTags($customMetaTags): string {
        $custom = "";
        foreach ($customMetaTags as $key => $value) {
            $custom .= "<".$value.">";
        }

        return $custom;
    }
}
