<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

// Configure new fields:
$fields = [
    'geo_region' => [
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_region',
        'exclude' => 1,
        'config' => [
            'type' => 'input',
            'max' => 100
        ],
    ],
    'geo_placename' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_placename',
        'config' => [
            'type' => 'input',
            'max' => 100
        ]
    ],
    'geo_position_long' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_position_long',
        'config' => [
            'type' => 'input',
            'max' => 100
        ]
    ],
    'geo_position_lat' => [
        'exclude' => 1,
        'label' => 'LLL:EXT:hh_seo/Resources/Private/Language/locallang_db.xlf:pages.geo_position_lat',
        'config' => [
            'type' => 'input',
            'max' => 100
        ]
    ]
];

// Add new fields to pages:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $fields);

// Make fields visible in the TCEforms:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages', // Table name
    '--palette--;http://www.geo-tag.de/generator/de.html;geo_place', // Field list to add
    '1', // List of specific types to add the field list to. (If empty, all type entries are affected)
    'after:description' // Insert fields before (default) or after one, or replace a field
);

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