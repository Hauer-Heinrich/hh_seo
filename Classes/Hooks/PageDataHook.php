<?php
declare(strict_types = 1);

namespace HauerHeinrich\HhSeo\Hooks;

// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Page\PageRenderer;
use \TYPO3\CMS\Frontend\Page\PageRepository;
use \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use PedroBorges\MetaTags\MetaTags;
use HauerHeinrich\HhSeo\Helpers\CanonicalGenerator;

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
     * @var TYPO3\\CMS\\Frontend\\Page\\PageRepository
     */
    protected $pageRepository;

    /**
     * currentPageProperties
     *
     * @var array
     */
    protected $currentPageProperties;

    /**
     * pluginSettings
     *
     * @var array
     */
    protected $pluginSettings;

    /**
     * additionalData
     *
     * @var array
     */
    protected $additionalData;

    /**
     * url
     *
     * @var string
     */
    protected $url;

    /**
     * currentPageUid
     *
     * @var int
     */
    protected $currentPageUid;

    public function __construct() {
        $this->currentPageUid = $GLOBALS['TSFE']->id;
        $this->additionalData = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['hh_seo'];
        $this->pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
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
    public function addPageData(&$parameters) {
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
        $cache = $cacheManager->getCache('hhseo_meta');
        $cacheData = $cache->get('meta_'.$this->currentPageUid);

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
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();
            $objectManager = GeneralUtility::makeInstance('TYPO3\\CMS\Extbase\\Object\\ObjectManager');
            $configurationManager = $objectManager->get('TYPO3\\CMS\\Extbase\\Configuration\\ConfigurationManager');
            $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            $this->pluginSettings = $extbaseFrameworkConfiguration['plugin.']['tx_hhseo.'];

            $this->typoScriptFrontendController = $GLOBALS['TSFE'] ?? GeneralUtility::makeInstance(TypoScriptFrontendController::class);
            $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);

            $request = $GLOBALS['TYPO3_REQUEST'];
            $this->url = rtrim($request->getUri()->getScheme() . '://' . $request->getUri()->getHost(), '/');

            $this->currentPageProperties = $this->pageRepository->getPage($this->typoScriptFrontendController->getRequestedId());

            if($fluidData['title']) {
                $separateBefore = str_replace('&nbsp;', ' ', $fluidData['titleSeparateBefore'] ? $fluidData['titleSeparateBefore'] : $fluidData['titleSeparate']);
                $separateAfter = str_replace('&nbsp;', ' ', $fluidData['titleSeparateAfter'] ? $fluidData['titleSeparateAfter'] : $fluidData['titleSeparate']);
                $titleBefore = $fluidData['titleBefore'] != null ? $fluidData['titleBefore'] . $separateBefore : '';
                $titleAfter = $fluidData['titleAfter'] != null ? $separateAfter . $fluidData['titleAfter'] : '';
                $title = $titleBefore . $fluidData['title'] . $titleAfter;
                $tags->title($title);
            }

            if($fluidData['description']) {
                $tags->meta('description', $fluidData['description']);
            }

            if($fluidData['og:type']) {
                $tags->og('type', $fluidData['og:type']);
            } else if(!$fluidData['og:type']) {
                $tags->og('type', 'website');
            }

            if($fluidData['og:title']) {
                $tags->og('title', $fluidData['og:title']);
            }

            if($fluidData['og:description']) {
                $tags->og('description', $fluidData['og:description']);
            }

            $ogImage = $fluidData['og:image'];
            if($ogImage) {
                if(is_array($ogImage)) {
                    foreach ($ogImage as $key => $value) {
                        $file = $resourceFactory->getFileObjectFromCombinedIdentifier($value);
                        $tags->og('image', $this->url . '/'. $file->getPublicUrl());
                        $tags->og('image:width', $file->getProperty('width'));
                        $tags->og('image:width', $file->getProperty('height'));
                    }
                } else {
                    $file = $resourceFactory->getFileObjectFromCombinedIdentifier($ogImage);
                    $tags->og('image', $this->url . '/'. $file->getPublicUrl());
                    $tags->og('image:width', $file->getProperty('width'));
                    $tags->og('image:width', $file->getProperty('height'));
                }
            }

            $tags->twitter('card', 'summary');
            if ($fluidData['twitter:card']) {
                $tags->twitter('card', $fluidData['twitter:card']);
            }

            if($fluidData['twitter:title']) {
                $tags->twitter('title', $fluidData['twitter:title']);
            }

            if($fluidData['twitter:description']) {
                $tags->twitter('description', $fluidData['twitter:description']);
            }

            $twitterImage = $fluidData['twitter:image'];
            if($twitterImage) {
                if(is_array($twitterImage)) {
                    foreach ($twitterImage as $key => $value) {
                        $file = $resourceFactory->getFileObjectFromCombinedIdentifier($value);
                        $tags->twitter('image', $this->url . '/'. $file->getPublicUrl());
                    }
                } else {
                    $file = $resourceFactory->getFileObjectFromCombinedIdentifier($twitterImage);
                    $tags->twitter('image', $this->url . '/'. $file->getPublicUrl());
                }
            }

            $shortcutIcon = $fluidData['shortcutIcon'];
            if($shortcutIcon) {
                $image = $resourceFactory->getFileObjectFromCombinedIdentifier($shortcutIcon);
                $tags->link('shortcut icon', $this->url . '/'. $image->getPublicUrl());
            }

            $touchIcon = $fluidData['touchIcon'];
            if ($touchIcon && file_exists($touchIcon)) {
                $newData .= $this->setTouchIcons($touchIcon);
            }

            if ($fluidData['format-detection'] === 'false') {
                $tags->meta('format-detection', 'telephone=no');
            } else if ($fluidData['format-detection'] === 'true') {
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
                $tags->meta('geo:region', $fluidData['geo:region']);
            }

            if($fluidData['geo:placename']) {
                $tags->meta('geo:placename', $fluidData['geo:placename']);
            }

            if($fluidData['geo:position:long'] && $fluidData['geo:position:lat']) {
                $pos = $fluidData['geo:position:long'] . ';' . $fluidData['geo:position:lat'];
                $icbm = $fluidData['geo:position:long'] . ', ' . $fluidData['geo:position:lat'];
                $tags->meta('geo:position', $pos);
                $tags->meta('ICBM', $icbm);
            }

            // Custom
            if ($fluidData['custom'] && is_array($fluidData['custom'])) {
                $newData .= $this->setCustomTags($fluidData['custom']);
            }

            // Robots
            if($fluidData['robots:index'] || $fluidData['robots:follow']) {
                $content = $fluidData['robots:index'];
                if ($content != null && trim($content) != '' && $fluidData['robots:follow']) {
                    $content .= ',';
                }
                $content .= $fluidData['robots:follow'];
                $tags->meta('robots', $content);
            }

            // Author
            if($fluidData['author']) {
                $tags->meta('author', $fluidData['author']);
            }
            if($fluidData['link:author']) {
                $tags->link('author', $fluidData['link:author']);
            }

            if($fluidData['copyright']) {
                $tags->meta('copyright', $fluidData['copyright']);
            }

            if($fluidData['designer']) {
                $tags->meta('designer', $fluidData['designer']);
            }

            // set canonical path-string if set, for slot
            $canonicalGenerator = GeneralUtility::makeInstance(CanonicalGenerator::class);
            if(!empty($fluidData['canonical'])) {
                $newData .= $canonicalGenerator->generate($fluidData['canonical']);
            } else {
                $newData .= $canonicalGenerator->generate();
            }

            if (!empty($fluidData['jsonld'])) {
                $tags->jsonld($fluidData['jsonld']);
            }

            // output to HTML
            $result = $tags->render() . $newData;
            array_unshift($parameters['headerData'], $result);

            // set DB cache
            $cache->set('meta_'.$this->currentPageUid, $result, ['meta', 'meta-tags'], 0);
        } else {
            array_unshift($parameters['headerData'], $cacheData);
        }

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
}
