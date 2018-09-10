<?php
declare(strict_types = 1);

namespace Bitmotion\MarketingAutomationMautic\Domain\Finishers;


use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\ContactRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticPointsFinisher extends AbstractFinisher
{
    /**
     * @var int
     */
    protected $mauticId;

    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    public function __construct(string $finisherIdentifier = '', ContactRepository $contactRepository = null)
    {
        parent::__construct($finisherIdentifier);

        $this->contactRepository = $contactRepository ?: GeneralUtility::makeInstance(ContactRepository::class);
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    /**
     * Adds or substracts points to a Mautic contact
     */
    protected function executeInternal()
    {
        if (0 === $this->mauticId) {
            return;
        }

        $pointsModifier = (int)$this->parseOption('mauticPointsModifier');
        $data = [];
        $data['eventName'] = $this->parseOption('mauticEventName');

        $this->contactRepository->modifyContactPoints($this->mauticId, $pointsModifier, $data);
    }
}