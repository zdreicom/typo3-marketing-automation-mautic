<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Slot;

use Bitmotion\MarketingAutomation\Dispatcher\SubscriberInterface;
use Bitmotion\MarketingAutomation\Persona\Persona;
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
        ContactRepository $contactRepository = null,
        PersonaRepository $personaRepository = null,
        TypoScriptFrontendController $typoScriptFrontendController = null
    ) {
        $this->contactRepository = $contactRepository ?: GeneralUtility::makeInstance(ContactRepository::class);
        $this->personaRepository = $personaRepository ?: GeneralUtility::makeInstance(PersonaRepository::class);
        $this->typoScriptFrontendController = $typoScriptFrontendController ?: $GLOBALS['TSFE'];

        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    public function needsUpdate(Persona $currentPersona, Persona $newPersona): bool
    {
        $isValidMauticId = !empty($this->mauticId);
        $isEmptyPersonaId = empty($currentPersona->getId());
        $this->languageNeedsUpdate = $this->typoScriptFrontendController->sys_language_uid !== $currentPersona->getLanguage();

        return $isValidMauticId && ($isEmptyPersonaId || $this->languageNeedsUpdate);
    }

    public function update(Persona $persona): Persona
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

        return $persona->withId($personaId);
    }
}
