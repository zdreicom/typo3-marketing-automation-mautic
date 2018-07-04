<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Slot;

use Bitmotion\MarketingAutomation\Cookie\Cookie;
use Bitmotion\MarketingAutomation\Cookie\SubscriberInterface;
use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\ContactRepository;
use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\PersonaRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticSubscriber implements SubscriberInterface
{
    /**
     * @var TypoScriptFrontendController
     */
    protected $typoScriptFrontendController;

    /**
     * @var int
     */
    protected $mauticId;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * @var PersonaRepository
     */
    protected $personaRepository;

    public function __construct(
        TypoScriptFrontendController $typoScriptFrontendController,
        ContactRepository $contactRepository = null,
        PersonaRepository $personaRepository = null
    ) {
        $this->typoScriptFrontendController = $typoScriptFrontendController;
        $this->contactRepository = $contactRepository ?: GeneralUtility::makeInstance(ContactRepository::class);
        $this->personaRepository = $personaRepository ?: GeneralUtility::makeInstance(PersonaRepository::class);

        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    public function needsUpdate(Cookie $cookie): bool
    {
        $isValidMauticId = !empty($this->mauticId);
        $isEmptyPersonaId = empty($cookie->getPersonaId());
        $isExpired = $cookie->getLastModified() < time() - 300;

        return $isValidMauticId && ($isEmptyPersonaId || $isExpired);
    }

    public function update(Cookie $cookie): Cookie
    {
        $segments = $this->contactRepository->findContactSegments($this->mauticId);
        $segmentIds = array_map(
            function ($segment) {
                return (int)$segment['id'];
            },
            $segments
        );

        $personaSegments = $cookie->getData()['mautic']['segments'] ?? [];
        if (empty(array_diff($segmentIds, $personaSegments))) {
            return $cookie;
        }

        $personaId = $this->personaRepository->findBySegments($segmentIds)['uid'] ?? 0;

        return $cookie->withPersonaId($personaId)
            ->withData(
                [
                    'mautic' => [
                        'segments' => $segmentIds,
                    ],
                ]
            );
    }
}
