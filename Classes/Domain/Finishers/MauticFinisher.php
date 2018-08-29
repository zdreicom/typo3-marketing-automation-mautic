<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Finishers;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\FormRepository;
use Escopecz\MauticFormSubmit\Mautic;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

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
        $mauticId = $this->parseOption('mauticId') ?? $formDefinition['mauticId'];
        $formValues = $this->transformFormStructure($this->finisherContext->getFormValues());

        $this->formRepository->submitForm((int)$mauticId, $formValues);
    }

    /**
     * Transform the TYPO3 form structure to a Mautic structure
     */
    private function transformFormStructure(array $formStructure): array
    {
        $mauticStructure = [];
        foreach ($formStructure as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $properties = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($key)->getProperties();
            if (!empty($properties['mauticAlias'])) {
                $mauticStructure[$properties['mauticAlias']] = $value;
            }
        }

        return $mauticStructure;
    }
}
