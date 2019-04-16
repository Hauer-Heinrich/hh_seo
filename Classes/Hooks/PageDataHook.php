<?php
namespace HauerHeinrich\HhSeo\Hooks;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class PageDataHook {
    protected $additionalData;

    public function __construct() {
        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
    }

    /**
     * My example function
     *
     * @param array $parameters
     * @return string
    */
    public function addPageData(&$parameters) {
        $metaTag = $this->additionalData['MetaTag'];

        if(!empty($metaTag)) {
            ksort($metaTag);

            $fullDataArray = [];
            foreach ($metaTag as $key => $value) {
                $data = $value['headerData'];

                foreach ($data as $dataKey => $dataValue) {
                    if(!empty(trim($dataValue))) {
                        $fullDataArray[$dataKey] = $dataValue;
                    }
                }
            }

            $newData = '';
            if($fullDataArray['title']) {
                $newData .= "<title>{$fullDataArray['title']}</title>";
            }

            if($fullDataArray['description']) {
                $newData .= "<meta name='description' content='{$fullDataArray['description']}'>";
            }

            if($fullDataArray['og:title']) {
                $newData .= "<meta property='og:title' content='{$fullDataArray['og:title']}'>";
            } else if($fullDataArray['title']) {
                $newData .= "<meta property='og:title' content='{$fullDataArray['title']}'>";
            }

            if($fullDataArray['og:description']) {
                $newData .= "<meta property='og:description' content='{$fullDataArray['og:description']}'>";
            } else if($fullDataArray['description']) {
                $newData .= "<meta property='og:description' content='{$fullDataArray['description']}'>";
            }

            if($fullDataArray['twitter:title']) {
                $newData .= "<meta property='twitter:title' content='{$fullDataArray['twitter:title']}'>";
            } else if($fullDataArray['title']) {
                $newData .= "<meta property='twitter:title' content='{$fullDataArray['title']}'>";
            }

            if($fullDataArray['twitter:description']) {
                $newData .= "<meta property='twitter:description' content='{$fullDataArray['twitter:description']}'>";
            } else if($fullDataArray['description']) {
                $newData .= "<meta property='twitter:description' content='{$fullDataArray['description']}'>";
            }

            if ($fullDataArray['touchIcon']) {
                $imageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Service\\ImageService");
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
                $newData .= "<meta name='format-detection' content='telephone=no'>";
            } else if ($fullDataArray['format-detection'] === "true") {
                $newData .= "<meta name='format-detection' content='telephone=yes'>";
            }

            if ($fullDataArray['theme-color']) {
                $newData .= "<meta name='theme-color' content='{$fullDataArray['theme-color']}'>";
                $newData .= "<meta name='msapplication-TileColor' content='{$fullDataArray['theme-color']}'>";
            }

            if ($fullDataArray['last-modified']) {
                $date = gmdate('D, d M Y H:i:s \G\M\T', intval($fullDataArray['last-modified']));
                $newData .= "<meta name='Last-Modified' content='{$date}' />";
            }

            $parameters["headerData"][2] = $newData;
        }
    }
}
