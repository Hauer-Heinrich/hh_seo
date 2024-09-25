<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function(string $extensionKey) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'Hauer-Heinrich SEO'
    );
}, 'hh_seo');
