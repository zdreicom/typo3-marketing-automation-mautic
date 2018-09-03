<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Finishers;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\ContactRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class MauticContactFinisher extends AbstractFinisher
{
    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * MauticContactFinisher constructor.
     *
     * @throws \Mautic\Exception\ContextNotFoundException
     */
    public function __construct(string $finisherIdentifier = '', ContactRepository $contactRepository = null)
    {
        parent::__construct($finisherIdentifier);

        $this->contactRepository = $contactRepository ?: GeneralUtility::makeInstance(ContactRepository::class);
    }

    /**
     * Creates a contact in Mautic if enough data is present from the collected form results
     *
     * @return string|null
     * @api
     */
    protected function executeInternal()
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition();

        $mauticFields = [];

        foreach ($this->finisherContext->getFormValues() as $key => $value) {
            $formElement = $formDefinition->getElementByIdentifier($key);

            if ($formElement instanceof GenericFormElement) {
                $properties = $formElement->getProperties();
                if (!empty($properties['mauticTable'])) {
                    $mauticFields[$properties['mauticTable']] = $value;
                }
            }
        }

        if (\count($mauticFields) === 0) {
            return;
        }

        $mauticFields['ipAddress'] = $_SERVER['REMOTE_ADDR'];
        $this->contactRepository->createContact($mauticFields);
    }
}
