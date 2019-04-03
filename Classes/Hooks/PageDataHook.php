<?php
namespace HauerHeinrich\HhSeo\Hooks;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
        $headerData = $this->additionalData['headerData'];

        // DebuggerUtility::var_dump($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo']);

        $newData = '';
        if($headerData['title']) {
            $newData .= "<title>{$headerData['title']}</title>";
        }

        if($headerData['description']) {
            $newData .= "<meta name='description' content='{$headerData['description']}'>";
        }

        if($headerData['og:title']) {
            $newData .= "<meta property='og:title' content='{$headerData['og:title']}'>";
        } else if($headerData['title']) {
            $newData .= "<meta property='og:title' content='{$headerData['title']}'>";
        }

        if ($headerData['touchIcon']) {
            $imageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\\CMS\\Extbase\\Service\\ImageService");
            $image = $imageService->getImage($headerData['touchIcon'], null, false);

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

        // <meta http-equiv="imagetoolbar" content="false">
        // <meta name="format-detection" content="telephone=no">

        if ($headerData['theme-color']) {
            $newData .= "<meta name='theme-color' content='{$headerData['theme-color']}'>";
            $newData .= "<meta name='msapplication-TileColor' content='{$headerData['theme-color']}'>";
        }

        $parameters["headerData"][2] = $newData;
    }
}
