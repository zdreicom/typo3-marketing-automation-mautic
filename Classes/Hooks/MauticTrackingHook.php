<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Hooks;

use Bitmotion\MarketingAutomationMautic\Service\MauticTrackingService;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTrackingHook
{
    /**
     * @var MauticTrackingService
     */
    protected $mauticTrackingService;

    public function __construct(MauticTrackingService $mauticTrackingService = null)
    {
        $this->mauticTrackingService = $mauticTrackingService ?: GeneralUtility::makeInstance(MauticTrackingService::class);
    }

    public function addTrackingCode()
    {
        if ($this->mauticTrackingService->isTrackingEnabled()) {
            $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
            $pageRenderer->addJsInlineCode('Mautic', $this->mauticTrackingService->getTrackingCode());
        }
    }
}
