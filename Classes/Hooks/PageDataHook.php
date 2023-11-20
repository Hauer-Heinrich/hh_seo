<?php
declare(strict_types = 1);

namespace HauerHeinrich\HhSeo\Hooks;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Page\PageRenderer;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use \PedroBorges\MetaTags\MetaTags;

class PageDataHook {

    /**
     * pageRenderer
     *
     * @var TYPO3\\CMS\\Core\\Page\\PageRenderer
     */
    protected $pageRenderer;

    /**
     * typoScriptFrontendController
     *
     * @var TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * pageRepository
     *
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * currentPageProperties
     *
     * @var array
     */
    protected $currentPageProperties = [];

    /**
     * pluginSettings
     *
     * @var array
     */
    protected $pluginSettings = [];

    /**
     * additionalData
     *
     * @var array
     */
    protected $additionalData = [];

    /**
     * url
     *
     * @var string
     */
    protected $url = '';

    /**
     * currentPageUid
     *
     * @var int
     */
    protected $currentPageUid = 0;

    public function __construct() {
        $this->currentPageUid = isset($GLOBALS['TSFE']->id) ? $GLOBALS['TSFE']->id : 1;
        $this->setAdditionalData();
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
    }

    public function setAdditionalData(): void {
        // old TYPO3
        if(isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'])) {
            $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
        }

        if(isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hh_seo'])) {
            $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['hh_seo'];
        }
    }

    /**
     * Get the current language
     *
     * @param \TYPO3\CMS\Core\Http\ServerRequest $request
     * @param string $attr - hreflang, base, locale, languageId, etc. from \TYPO3\CMS\Core\Site\Entity\SiteLanguage
     * @return mixed
     */
    protected function getCurrentLanguage(\TYPO3\CMS\Core\Http\ServerRequest $request, $attr = null) {
        $attr = ucfirst($attr);
        $get = 'get'. $attr;
        $language = $request->getAttribute('language');
        try {
            $value = $language->{$get}();
            return $value;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * addPageData
     *
     * @param array $parameters
     * @return string
    */
    public function addPageData(&$parameters = null) {
        $contentObjectRenderer = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer::class);
        $htmlHead = $contentObjectRenderer->getData('levelfield : -1, html_head, slide');
        $htmlBodyTop = $contentObjectRenderer->getData('levelfield : -1, html_body_top, slide');
        $htmlBodyBottom = $contentObjectRenderer->getData('levelfield : -1, html_body_bottom, slide');

        if (!empty($htmlHead)) {
            $this->setHTMLCodeHead($htmlHead);
        }

        if (!empty($htmlBodyTop)) {
            $this->setHTMLCodeBodyTop($htmlBodyTop);
        }

        if (!empty($htmlBodyBottom)) {
            $this->setHTMLCodeBodyBottom($htmlBodyBottom);
        }

        // no meta tags set via FLUID
        if(empty($this->additionalData)) {
            return '';
        }
        $metaTag = $this->additionalData['MetaTag'];

        // TODO: make all meta-tags available as single viewhelper
        // TODO: overwrite json data with the one from the single viewhelpers
        //       so json data is less important by default
        // if(is_array($this->additionalData['title'])) {
        //     self::setTitle($this->additionalData['title']);
        // }

        // if(is_array($this->additionalData['description'])) {
        //     self::setDescription($this->additionalData['description']);
        // }

        $cacheManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Cache\\CacheManager');
        $cacheData = '';
        if($cacheManager->hasCache('hhseo_meta')) {
            $cache = $cacheManager->getCache('hhseo_meta');
            $cacheData = $cache->get('meta_'.$this->currentPageUid);
        }

        if(!empty($metaTag)) {
            ksort($metaTag);

            $fluidData = [];
            foreach ($metaTag as $value) {
                $data = $value['headerData'];

                if(array_key_exists('overwrite', $value) && $value['overwrite'] == true) {
                    $fluidData = $data;

                    continue;
                }

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

            $newData = '';
            $tags = new MetaTags;
            $resourceFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\ResourceFactory');
            $configurationManager = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
            $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $this->pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];

            $this->typoScriptFrontendController = $GLOBALS['TSFE'] ?? GeneralUtility::makeInstance(TypoScriptFrontendController::class);

            /**
             * @deprecated since TYPO3 11
             */
            if (class_exists('\\TYPO3\\CMS\\Core\\Domain\\Repository\\PageRepository', true)) {
                $this->pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Domain\Repository\PageRepository::class);
            } else {
                $this->pageRepository = GeneralUtility::makeInstance(\TYPO3\CMS\Frontend\Page\PageRepository::class);
            }

            $request = $GLOBALS['TYPO3_REQUEST'];
            $this->url = rtrim($request->getUri()->getScheme() . '://' . $request->getUri()->getHost(), '/');

            $this->currentPageProperties = $this->pageRepository->getPage($this->typoScriptFrontendController->getRequestedId());

            if(isset($fluidData['viewport'])) {
                $tags->meta('viewport', $fluidData['viewport']);
            }

            if(isset($fluidData['title'])) {
                $fluidData['titleSeparate'] = isset($fluidData['titleSeparate']) ? $fluidData['titleSeparate'] : '';
                $separateBefore = str_replace('&nbsp;', ' ', $fluidData['titleSeparateBefore'] ?? $fluidData['titleSeparate']);
                $separateAfter = str_replace('&nbsp;', ' ', $fluidData['titleSeparateAfter'] ?? $fluidData['titleSeparate']);
                $titleBefore = isset($fluidData['titleBefore']) ? $fluidData['titleBefore'] . $separateBefore : '';
                $titleAfter = isset($fluidData['titleAfter']) ? $separateAfter . $fluidData['titleAfter'] : '';
                $title = $titleBefore . $fluidData['title'] . $titleAfter;
                $tags->title($title);
            }

            if(isset($fluidData['keywords'])) {
                $tags->meta('keywords', $fluidData['keywords']);
            }

            if(isset($fluidData['description'])) {
                $tags->meta('description', $fluidData['description']);
            }

            if(isset($fluidData['og']) && \is_array($fluidData['og'])) {
                foreach ($fluidData['og'] as $key => $value) {
                    if($key === 'images' && \is_array($value)) {
                        if(\is_array($value)) {
                            foreach ($value as $imageData) {
                                if(isset($imageData['image'])) {
                                    if(\str_starts_with($imageData['image'], 'https://')) {
                                        $tags->og('image', $imageData['image'], '/');
                                    } else {
                                        $tags->og('image', ltrim($this->url . $imageData['image'], '/'));
                                    }
                                }

                                // width and height have to be after "image"
                                if(isset($imageData['width'])) {
                                    $tags->og('image:width', $imageData['width']);
                                }
                                if(isset($imageData['height'])) {
                                    $tags->og('image:height', $imageData['height']);
                                }
                            }
                        }

                        continue;
                    }

                    $tags->og($key, $value);
                }
            }

            // TODO:
            // @deprecated
            $ogImage = isset($fluidData['og:image']) ? $fluidData['og:image'] : null;
            if(\is_string($ogImage)) {
                if(filter_var($ogImage, FILTER_VALIDATE_URL)) {
                    $tags->og('image', $ogImage);
                    $ogImageHeight = isset($fluidData['og:image:height']) ? $fluidData['og:image:height'] : false;
                    $ogImageWidth = isset($fluidData['og:image:width']) ? $fluidData['og:image:width'] : false;
                    $tags->og('image:width', $ogImageWidth);
                    $tags->og('image:height', $ogImageHeight);
                }
            }

            if(isset($fluidData['twitter']) && \is_array($fluidData['twitter'])) {
                foreach ($fluidData['twitter'] as $key => $value) {
                    if($key === 'images' && \is_array($value)) {
                        if(\is_array($value)) {
                            foreach ($value as $imageData) {
                                if(isset($imageData['image'])) {
                                    if(\str_starts_with($imageData['image'], 'https://')) {
                                        $tags->twitter('image', $imageData['image'], '/');

                                        continue;
                                    }

                                    $tags->twitter('image', ltrim($this->url . $imageData['image'], '/'));
                                }

                            }
                        }

                        continue;
                    }

                    $tags->twitter($key, $value);
                }

            } else {
                // TODO:
                // @deprecated
                if (isset($fluidData['twitter:card'])) {
                    $tags->twitter('card', $fluidData['twitter:card']);
                } else {
                    $tags->twitter('card', 'summary');
                }

                if(isset($fluidData['twitter:title'])) {
                    $tags->twitter('title', $fluidData['twitter:title']);
                }

                if(isset($fluidData['twitter:description'])) {
                    $tags->twitter('description', $fluidData['twitter:description']);
                }

                $twitterImage = isset($fluidData['twitter:image']) ? $fluidData['twitter:image'] : false;
                if($twitterImage) {
                    if(is_array($twitterImage)) {
                        foreach ($twitterImage as $value) {
                            $file = $resourceFactory->getFileObjectFromCombinedIdentifier($value);
                            $tags->twitter('image', ltrim($this->url . $file->getPublicUrl(), '/'));
                        }
                    } else {
                        $file = $resourceFactory->getFileObjectFromCombinedIdentifier($twitterImage);
                        $tags->twitter('image', ltrim($this->url . $file->getPublicUrl(), '/'));
                    }
                }
            }

            $shortcutIcon = isset($fluidData['shortcutIcon']) ? $fluidData['shortcutIcon'] : false;
            if($shortcutIcon) {
                $shortcutIconPublicUrl = $this->resolveExtFilePathToWebUrl($shortcutIcon);

                if(empty($shortcutIconPublicUrl)) {
                    $image = $resourceFactory->getFileObjectFromCombinedIdentifier($shortcutIcon);
                    $shortcutIconPublicUrl = $this->url . $image->getPublicUrl();
                }

                $tags->link('shortcut icon', ltrim($shortcutIconPublicUrl, '/'));
            }

            $touchIcon = isset($fluidData['touchIcon']) ? $fluidData['touchIcon'] : false;
            if ($touchIcon && file_exists($touchIcon)) {
                $newData .= $this->setTouchIcons($touchIcon);
            }

            if(isset($fluidData['format-detection'])) {
                if ($fluidData['format-detection'] === 'false') {
                    $tags->meta('format-detection', 'telephone=no');
                } else if ($fluidData['format-detection'] === 'true') {
                    $tags->meta('format-detection', 'telephone=yes');
                }
            }

            if (isset($fluidData['theme-color'])) {
                $tags->meta('theme-color', $fluidData['theme-color']);
                $tags->meta('msapplication-TileColor', $fluidData['theme-color']);
            }

            // uses timestamp as input
            if (isset($fluidData['last-modified'])) {
                $date = gmdate('D, d M Y H:i:s \G\M\T', intval($fluidData['last-modified']));
                $tags->meta('Last-Modified', $date);
            }

            // geo data - position
            if (isset($fluidData['geo']) && \is_array($fluidData['geo'])) {
                foreach ($fluidData['geo'] as $key => $value) {
                    if($key === 'position' && \is_array($value) && isset($value['long']) && isset($value['lat'])) {
                        $pos = $value['long'] . ';' . $value['long'];
                        $icbm = $value['lat'] . ', ' . $value['lat'];
                        $tags->meta('geo:position', $pos);
                        $tags->meta('ICBM', $icbm);
                        continue;
                    }

                    $tags->meta('geo:'.$key, $value);
                }
            } else {
                // TODO:
                // @deprecated
                if(isset($fluidData['geo:region'])) {
                    $tags->meta('geo:region', $fluidData['geo:region']);
                }

                if(isset($fluidData['geo:placename'])) {
                    $tags->meta('geo:placename', $fluidData['geo:placename']);
                }

                if(isset($fluidData['geo:position:long']) && isset($fluidData['geo:position:lat'])) {
                    $pos = $fluidData['geo:position:long'] . ';' . $fluidData['geo:position:lat'];
                    $icbm = $fluidData['geo:position:long'] . ', ' . $fluidData['geo:position:lat'];
                    $tags->meta('geo:position', $pos);
                    $tags->meta('ICBM', $icbm);
                }
            }

            // Custom
            if(isset($fluidData['custom']) && \is_array($fluidData['custom'])) {
                $newData .= $this->setCustomTags($fluidData['custom']);
            }

            // Robots
            if(isset($fluidData['robots']) && \is_array($fluidData['robots'])) {
                $robotsContent = '';
                foreach ($fluidData['robots'] as $key => $value) {
                    $robotsContent .= $value;

                    if ($key !== array_key_last($fluidData['robots'])) {
                        $robotsContent .= ', ';
                    }
                }
                $tags->meta('robots', $robotsContent);
            } else {
                // TODO:
                // @deprecated
                $robotsContent = '';
                if(isset($fluidData['robots:index'])) {
                    $robotsContent = $fluidData['robots:index'];
                }

                if(isset($fluidData['robots:follow'])) {
                    if ($robotsContent != null && trim($robotsContent) != '') {
                        $robotsContent .= ',';
                    }

                    $robotsContent .= $fluidData['robots:follow'];
                }

                if ($robotsContent != null && trim($robotsContent) != '') {
                    $tags->meta('robots', $robotsContent);
                }
            }



            if(isset($fluidData['author'])) {
                $tags->meta('author', $fluidData['author']);
            }

            if(isset($fluidData['designer'])) {
                $tags->meta('designer', $fluidData['designer']);
            }

            if(isset($fluidData['link']) && \is_array($fluidData['link'])) {
                foreach ($fluidData['link'] as $key => $value) {
                    $tags->link($key, $value);
                }
            } else {
                // TODO:
                // @deprecated
                if(isset($fluidData['link:author'])) {
                    $tags->link('author', $fluidData['link:author']);
                }
                if(isset($fluidData['link:designer'])) {
                    $tags->link('designer', $fluidData['link:designer']);
                }
            }

            if(isset($fluidData['copyright'])) {
                $tags->meta('copyright', $fluidData['copyright']);
            }

            if (!empty($fluidData['jsonld'])) {
                $tags->jsonld($fluidData['jsonld']);
            }

            // output to HTML
            $result = $tags->render() . $newData;
            $this->setHTMLCodeHead($result);

            // set DB cache
            if($cacheManager->hasCache('hhseo_meta')) {
                $cache->set('meta_'.$this->currentPageUid, $result, ['meta', 'meta-tags'], 0);
            }
        } else {
            $this->setHTMLCodeHead($cacheData);
        }
    }

    /**
     * Generate touchicon meta-tags
     *
     * @param string $iconPath Icon file path
     * @return string
     */
    public function setTouchIcons(string $iconPath): string {
        $touchIcons = '';
        $imageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Service\\ImageService');
        $image = $imageService->getImage($iconPath, null, false);

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
        $custom = '';
        foreach ($customMetaTags as $key => $value) {
            $custom .= '<'.$value.'>';
        }

        return $custom;
    }

    /**
     * Set your custom HTML Code
     *
     * @param string $data
     */
    public function setHTMLCodeHead($data) {
        $this->pageRenderer->addHeaderData($data);
    }

    /**
     * Set your custom HTML Code
     *
     * @param string $data
     */
    public function setHTMLCodeBodyTop($data) {
        $bodyContent = $this->pageRenderer->getBodyContent();
        if(!empty($bodyContent)) {
            $this->pageRenderer->setBodyContent(substr_replace($bodyContent, $data, 1+strpos($bodyContent, '>'), 0));
        }
    }

    /**
     * Set your custom HTML Code
     *
     * @param string $data
     */
    public function setHTMLCodeBodyBottom($data) {
        $this->pageRenderer->addFooterData($data);
    }

    public function setOgImage(MetaTags &$tags, string $url, string $height, string $width): void {
        $tags->og('image', $url);
        $tags->og('image:height', $height);
        $tags->og('image:width', $width);
    }

    public function setTwitterImage(MetaTags &$tags, string $url): void {
        $tags->twitter('image', $url);
    }

    /**
     * resolveExtFilePathToWebUrl
     * e. g. EXT:my_extension_key/Resources/icon.svg  => typo3conf/ext/my_extension_key/Resources/icon.svg
     *
     * @param  string $filePath
     * @return string
     */
    public function resolveExtFilePathToWebUrl(string $filePath): string {
        $shortcutIconPublicUrl = '';

        if (strpos($filePath, 'EXT:') === 0) {
            $absPathName = GeneralUtility::getFileAbsFileName($filePath);
            $shortcutIconPublicUrl = str_replace(\TYPO3\CMS\Core\Core\Environment::getPublicPath().'/', '', $absPathName);
        }

        return $shortcutIconPublicUrl;
    }
}
