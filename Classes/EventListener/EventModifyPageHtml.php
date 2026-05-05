<?php
declare(strict_types=1);

namespace HauerHeinrich\HhSeo\EventListener;


// use \TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use \TYPO3\CMS\Core\Attribute\AsEventListener;
use \TYPO3\CMS\Frontend\Event\AfterCacheableContentIsGeneratedEvent;
use \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

#[AsEventListener(
    identifier: 'hh-seo/event-modify-page-html',
    after: AfterCacheableContentIsGeneratedEvent::class
)]
final readonly class EventModifyPageHtml {
    public function __construct(
        private readonly ContentObjectRenderer $contentObjectRenderer
    ) {
    }

    public function __invoke(AfterCacheableContentIsGeneratedEvent $event): void {
        $htmlBodyTop = $this->contentObjectRenderer->getData('levelfield : -1, html_body_top, slide');

        if(!empty($htmlBodyTop)) {
            // TYPO3 14+: getContent() / setContent()
            // TYPO3 13:  getController()->content
            if (method_exists($event, 'getContent')) {
                // TYPO3 14
                $content = $event->getContent();
                $content = $this->inject($content, $htmlBodyTop);
                $event->setContent($content);
            } else {
                // TYPO3 13
                $controller = $event->getController();
                $controller->content = $this->inject($controller->content, $htmlBodyTop);
            }
        }
    }

    private function inject(string $content, string $html = ''): string {
        return preg_replace(
            '/(<body[^>]*>)/i',
            '$1' . "\n" . $html,
            $content,
            1
        );
    }
}
