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

    /**
     * @var bool
     */
    protected $languageNeedsUpdate = false;

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

    public function needsUpdate(Cookie $oldCookie, Cookie $newCookie): bool
    {
        $isValidMauticId = !empty($this->mauticId);
        $isEmptyPersonaId = empty($oldCookie->getPersonaId());
        $this->languageNeedsUpdate = $this->typoScriptFrontendController->sys_language_uid !== $oldCookie->getLanguage();

        return $isValidMauticId && ($isEmptyPersonaId || $this->languageNeedsUpdate);
    }

    public function update(Cookie $cookie): Cookie
    {
        if ($this->languageNeedsUpdate) {
            $this->contactRepository->setContactData(
                $this->mauticId,
                [
                    'preferred_locale' => $this->typoScriptFrontendController->sys_language_isocode,
                ]
            );
        }

        $segments = $this->contactRepository->findContactSegments($this->mauticId);
        $segmentIds = array_map(
            function ($segment) {
                return (int)$segment['id'];
            },
            $segments
        );
        $personaId = $this->personaRepository->findBySegments($segmentIds)['uid'] ?? 0;

        return $cookie->withPersonaId($personaId);
    }
}
