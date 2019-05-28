<?php
declare(strict_types = 1);

namespace HauerHeinrich\HhSeo\Hooks;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use PedroBorges\MetaTags\MetaTags;
use HauerHeinrich\HhSeo\Helpers\CanonicalGenerator;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PageDataHook {
    protected $pluginSettings;

    protected $additionalData;

    public function __construct() {
        $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
        $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
        $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        $this->pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];

        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
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

            $tags = new MetaTags;

            if($fullDataArray['title']) {
                $tags->title(
                    $fullDataArray['title'],
                    $fullDataArray['titleBefore'],
                    $fullDataArray['titleAfter'],
                    $fullDataArray['titleSeparate'],
                    $fullDataArray['titleSeparateBefore'],
                    $fullDataArray['titleSeparateAfter']
                );
            }

            if($fullDataArray['description']) {
                $tags->meta('description', $fullDataArray['description']);
            }

            if($fullDataArray['og:title']) {
                $tags->og("title", $fullDataArray['og:title']);
            }

            if($fullDataArray['og:description']) {
                $tags->og("description", $fullDataArray['og:description']);
            }

            if($fullDataArray['twitter:title']) {
                $tags->twitter("title", $fullDataArray['twitter:title']);
            }

            if($fullDataArray['twitter:description']) {
                $tags->twitter("description", $fullDataArray['twitter:description']);
            }

            if($fullDataArray['shortcutIcon']) {
                $image = $imageService->getImage($fullDataArray['shortcutIcon'], null, false);
                $imageUri = $imageService->getImageUri($image);
                $tags->link('shortcut icon', $imageUri);
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
                $tags->meta('format-detection', 'telephone=no');
            } else if ($fullDataArray['format-detection'] === "true") {
                $tags->meta('format-detection', 'telephone=yes');
            }

            if ($fullDataArray['theme-color']) {
                $tags->meta('theme-color', $fullDataArray['theme-color']);
                $tags->meta('msapplication-TileColor', $fullDataArray['theme-color']);
            }

            if ($fullDataArray['last-modified']) {
                $date = gmdate('D, d M Y H:i:s \G\M\T', intval($fullDataArray['last-modified']));
                $tags->meta('Last-Modified', $date);
            }

            // geo data - position
            if($fullDataArray['geo:region']) {
                $tags->meta("geo:region", $fullDataArray['geo:region']);
            }

            if($fullDataArray['geo:placename']) {
                $tags->meta("geo:placename", $fullDataArray['geo:placename']);
            }

            if($fullDataArray['geo:position:long'] && $fullDataArray['geo:position:lat']) {
                $pos = $fullDataArray['geo:position:long'] . ";" . $fullDataArray['geo:position:lat'];
                $icbm = $fullDataArray['geo:position:long'] . ", " . $fullDataArray['geo:position:lat'];
                $tags->meta("geo:position", $pos);
                $tags->meta("ICBM", $icbm);
            }

            // Robots
            if($fullDataArray['robots:index'] || $fullDataArray['robots:follow']) {
                $content = $fullDataArray['robots:index'];
                if (trim($content) != "" && $fullDataArray['robots:follow']) {
                    $content .= ',';
                }
                $content .= $fullDataArray['robots:follow'];
                $tags->meta("robots", $content);
            }

            // Author
            if($fullDataArray['author']) {
                $tags->meta("author", $fullDataArray['author']);
            }

            if($fullDataArray['copyright']) {
                $tags->meta("copyright", $fullDataArray['copyright']);
            }

            // set canonical path-string if set, for slot
            $canonicalGenerator = GeneralUtility::makeInstance(CanonicalGenerator::class);
            if(!empty($fullDataArray['canonical'])) {
                $newData .= $canonicalGenerator->generate($fullDataArray['canonical']);
            } else {
                $newData .= $canonicalGenerator->generate();
            }

            // output to HTML
            $result = $tags->render() . $newData;
            $parameters["headerData"][2] = $result;
        }
    }
}
