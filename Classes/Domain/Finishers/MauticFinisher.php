<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Finishers;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\FormRepository;
use Escopecz\MauticFormSubmit\Mautic;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement;

class MauticFinisher extends AbstractFinisher
{
    /**
     * @var Mautic
     */
    protected $formRepository;

    public function __construct(string $finisherIdentifier = '', FormRepository $formRepository = null)
    {
        parent::__construct($finisherIdentifier);

        $this->formRepository = $formRepository ?: GeneralUtility::makeInstance(FormRepository::class);
    }

    /**
     * Post the form result to a Mautic form
     *
     * @api
     */
    protected function executeInternal()
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition()->getRenderingOptions();
        if (empty($this->parseOption('mauticId'))) {
            $mauticId = $formDefinition['mauticId'];
        } else {
            $mauticId = $this->parseOption('mauticId');
        }

        $formValues = $this->transformFormStructure($this->finisherContext->getFormValues());

        $this->formRepository->submitForm((int)$mauticId, $formValues);
    }

    /**
     * Transform the TYPO3 form structure to a Mautic structure
     *
     * @param array $formStructure
     *
     * @return array
     */
    private function transformFormStructure(array $formStructure): array
    {
        $mauticStructure = [];
        foreach ($formStructure as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $formElement = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($key);

            if ($formElement instanceof GenericFormElement) {
                $properties = $formElement->getProperties();
                if (!empty($properties['mauticAlias'])) {
                    $mauticStructure[$properties['mauticAlias']] = $value;
                }
            }
        }

        return $mauticStructure;
    }
}
