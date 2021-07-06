<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "typo3themeskeleton"
 *
 * Auto generated by Extension Builder 2017-02-24
 *
 * Manual updates:
 * Only the data in the array - anything else is removed by next write.
 * "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF['hh_seo'] = [
    'title' => 'Hauer-Heinrich - SEO - Meta-Tag - ViewHelpers',
    'description' => 'Hauer-Heinrich - SEO - Meta-Tag - ViewHelpers',
    'category' => 'distribution',
    'author' => 'Christian Hackl',
    'author_email' => 'chackl@hauer-heinrich.de',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.3.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
            'seo' => '10.4.0-10.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'HauerHeinrich\\HhSeo\\' => 'Classes',
            'PedroBorges\\MetaTags\\' => 'meta-tags/src'
        ],
    ],
];
