<?php
defined('TYPO3') || die('Access denied.');

// Configure new fields:
$fields = [
    'html_head' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.html_head',
        'exclude' => 1,
        'config' => [
            'type' => 'text',
            'default' => '',
            'eval' => 'trim',
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'html_body_top' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.html_body_top',
        'exclude' => 1,
        'config' => [
            'type' => 'text',
            'default' => '',
            'eval' => 'trim',
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'html_body_bottom' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.html_body_bottom',
        'exclude' => 1,
        'config' => [
            'type' => 'text',
            'default' => '',
            'eval' => 'trim',
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'geo_region' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_region',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => 100,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'geo_placename' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_placename',
        'config' => [
            'type' => 'input',
            'max' => 100,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ]
    ],
    'geo_position_long' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_position_long',
        'config' => [
            'type' => 'input',
            'max' => 100,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ]
    ],
    'geo_position_lat' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_position_lat',
        'config' => [
            'type' => 'input',
            'max' => 100,
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'noimageindex' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.noimageindex',
        'exclude' => true,
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    'label' => '',
                    'invertStateDisplay' => true,
                ],
            ],
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'noarchive' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.noarchive',
        'exclude' => true,
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    'label' => '',
                    'invertStateDisplay' => true,
                ],
            ],
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
    'nosnippet' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.nosnippet',
        'exclude' => true,
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    'label' => '',
                    'invertStateDisplay' => true,
                ],
            ],
            'behaviour' => [
                'allowLanguageSynchronization' => true
            ],
        ],
    ],
];

// Add new fields to pages:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $fields);

// Add the new palette:
$GLOBALS['TCA']['pages']['palettes']['geo_place'] = [
    'showitem' => '
        geo_region,
        geo_placename,
        --linebreak--,
        geo_position_long,
        geo_position_lat
    '
];
$GLOBALS['TCA']['pages']['palettes']['additional_robots'] = [
    'showitem' => '
        noimageindex,
        noarchive,
        nosnippet
    '
];

// Make fields visible in the TCEforms:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages', // Table name
    '--palette--;http://www.geo-tag.de/generator/de.html;geo_place', // Field list to add
    '1', // List of specific types to add the field list to. (If empty, all type entries are affected)
    'after:description' // Insert fields before (default) or after one, or replace a field
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    '--palette--;Advanced robot instructions;additional_robots',
    '1',
    'after:no_follow'
);

// Make fields visible in the TCEforms:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages', // Table name
    '--div--;SEO Analytics,html_head,html_body_top,html_body_bottom'
);
