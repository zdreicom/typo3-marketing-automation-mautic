<?php
declare(strict_types = 1);

namespace Bitmotion\MarketingAutomationMautic\Domain\FormElement;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\ContactRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Model\FormElements\Page;

class MauticFormElement
{
    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * MauticFormElement constructor.
     *
     * @param $contactRepository
     */
    public function __construct(ContactRepository $contactRepository = null)
    {
        $this->contactRepository = $contactRepository ?: GeneralUtility::makeInstance(ContactRepository::class);
    }


    public function beforeRendering(\TYPO3\CMS\Form\Domain\Runtime\FormRuntime $formRuntime, \TYPO3\CMS\Form\Domain\Model\Renderable\RootRenderableInterface $renderable)
    {
        if ($renderable instanceof Page) {
            $mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
            if (0 === $mauticId) {
                return;
            }

            $contact = $this->contactRepository->getContact($mauticId);
            foreach ($renderable->getElementsRecursively() as $element) {
                if (!empty($element->getProperties()['mauticTable'])) {
                    $element->setDefaultValue($contact['contact']['fields']['core'][$element->getProperties()['mauticTable']]['value'] ?? '');
                }
            }
        }
    }
}