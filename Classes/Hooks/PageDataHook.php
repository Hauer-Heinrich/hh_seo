<?php
declare(strict_types = 1);

namespace HauerHeinrich\HhSeo\Hooks;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use HauerHeinrich\HhSeo\Helpers\MetaTagGenerator;

class PageDataHook {
    protected $pluginSettings;

    protected $additionalData;

    /**
     * @var MetaTagGenerator
     */
    protected $metaTagGenerator;

    public function __construct() {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];

        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
        $this->metaTagGenerator = new MetaTagGenerator();
    }

    /**
     * addPageData
     *
     * @param array $parameters
     * @return string
    */
    public function addPageData(&$parameters) {
        $metaTag = $this->additionalData['MetaTag'];

        $imageService = GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Service\\ImageService");

        if(!empty($metaTag)) {
            ksort($metaTag);

            $fullDataArray = [];
            foreach ($metaTag as $key => $value) {
                $data = $value['headerData'];

                if($value['override'] == true) {
                    $fullDataArray = $data;
                } else {
                    foreach ($data as $dataKey => $dataValue) {
                        if (is_array($dataValue)) {
                            $errors = array_filter($dataValue);
                            if (!empty($errors)) {
                                $fullDataArray[$dataKey] = $dataValue;
                            }
                        } else if(is_string($dataValue) && !empty(trim($dataValue))) {
                            $fullDataArray[$dataKey] = strip_tags($dataValue);
                        } else if(!empty($dataValue)) {
                            $fullDataArray[$dataKey] = $dataValue;
                        }
                    }
                }
            }

            $newData = '';

            if($fullDataArray['title']) {
                $newData .= $this->metaTagGenerator->getTitleTag(
                    $fullDataArray['title'],
                    $fullDataArray['titleBefore'],
                    $fullDataArray['titleAfter'],
                    $fullDataArray['titleSeparate'],
                    $fullDataArray['titleSeparateBefore'],
                    $fullDataArray['titleSeparateAfter']
                );
            }

            if($fullDataArray['description']) {
                $newData .= $this->metaTagGenerator->getNameMetaTag("description", $fullDataArray['description']);
            }

            if($fullDataArray['og:title']) {
                $newData .= $this->metaTagGenerator->getPropertyMetaTag("og:title", $fullDataArray['og:title']);
            }

            if($fullDataArray['og:description']) {
                $newData .= $this->metaTagGenerator->getPropertyMetaTag("og:description", $fullDataArray['og:description']);
            }

            if($fullDataArray['twitter:title']) {
                $newData .= $this->metaTagGenerator->getPropertyMetaTag("twitter:title", $fullDataArray['twitter:title']);
            }

            if($fullDataArray['twitter:description']) {
                $newData .= $this->metaTagGenerator->getPropertyMetaTag("twitter:description", $fullDataArray['twitter:description']);
            }

            if($fullDataArray['shortcutIcon']) {
                $image = $imageService->getImage($fullDataArray['shortcutIcon'], null, false);
                $imageUri = $imageService->getImageUri($image);
                $newData .= "<link rel='shortcut icon' href='{$imageUri}'>";
            }

            if ($fullDataArray['touchIcon']) {
                $image = $imageService->getImage($fullDataArray['touchIcon'], null, false);

                // you have to set these variables or remove if you don't need them
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
                if (empty($fullDataArray['shortcutIcon'])) {
                    $processingInstructions['favicon'] = [
                        'tag' => '<link rel="shortcut icon" href="%s">'
                    ];
                }

                foreach ($processingInstructions as $key => $value) {
                    $tag = $value['tag'];
                    foreach ($value['sizes'] as $sizesKey => $sizesValue) {
                        $processedImage = $imageService->applyProcessingInstructions($image, $sizesValue);
                        $imageUri = $imageService->getImageUri($processedImage);
                        $newData .= sprintf($tag, $sizesValue['width'], $sizesValue['height'], $imageUri);
                    }
                }
            }

            if ($fullDataArray['imagetoolbar']) {
                $newData .= "<meta http-equiv='imagetoolbar' content='{$fullDataArray['imagetoolbar']}'>";
            }

            if ($fullDataArray['format-detection'] === "false") {
                $newData .= $this->metaTagGenerator->getNameMetaTag('format-detection', 'telephone=no');
            } else if ($fullDataArray['format-detection'] === "true") {
                $newData .= $this->metaTagGenerator->getNameMetaTag('format-detection', 'telephone=yes');
            }

            if ($fullDataArray['theme-color']) {
                $newData .= $this->metaTagGenerator->getNameMetaTag('theme-color', $fullDataArray['theme-color']);
                $newData .= $this->metaTagGenerator->getNameMetaTag('msapplication-TileColor', $fullDataArray['theme-color']);
            }

            if ($fullDataArray['last-modified']) {
                $date = gmdate('D, d M Y H:i:s \G\M\T', intval($fullDataArray['last-modified']));
                $newData .= $this->metaTagGenerator->getNameMetaTag('Last-Modified', $date);
            }

            // geo data - position
            if($fullDataArray['geo:region']) {
                $newData .= $this->metaTagGenerator->getNameMetaTag("geo:region", $fullDataArray['geo:region']);
            }

            if($fullDataArray['geo:placename']) {
                $newData .= $this->metaTagGenerator->getNameMetaTag("geo:placename", $fullDataArray['geo:placename']);
            }

            if($fullDataArray['geo:position:long'] && $fullDataArray['geo:position:lat']) {
                $pos = $fullDataArray['geo:position:long'] . ";" . $fullDataArray['geo:position:lat'];
                $icbm = $fullDataArray['geo:position:long'] . ", " . $fullDataArray['geo:position:lat'];
                $newData .= $this->metaTagGenerator->getNameMetaTag("geo:position", $pos);
                $newData .= $this->metaTagGenerator->getNameMetaTag("ICBM", $icbm);
            }

            // Robots
            if($fullDataArray['robots:index'] || $fullDataArray['robots:follow']) {
                $content = $fullDataArray['robots:index'];
                if (trim($content) != "" && $fullDataArray['robots:follow']) {
                    $content .= ',';
                }
                $content .= $fullDataArray['robots:follow'];
                $newData .= $this->metaTagGenerator->getNameMetaTag("robots", $content);
            }

            // Author
            if($fullDataArray['author']) {
                $newData .= $this->metaTagGenerator->getNameMetaTag("author", $fullDataArray['author']);
            }

            if($fullDataArray['copyright']) {
                $newData .= $this->metaTagGenerator->getNameMetaTag("copyright", $fullDataArray['copyright']);
            }

            $parameters["headerData"][2] = $newData;
        }
    }
}
