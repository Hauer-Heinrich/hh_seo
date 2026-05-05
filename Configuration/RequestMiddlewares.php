<?php
declare(strict_types=1);

return [
    'frontend' => [
        'hauer-heinrich/hh-seo/html-body-top' => [
            'target' => \HauerHeinrich\HhSeo\Middleware\HtmlBodyTopMiddleware::class,
            'after' => [
                'typo3/cms-frontend/prepare-tsfe-rendering',
            ],
            'before' => [
                'typo3/cms-frontend/output-compression',
            ],
        ],
    ],
];
