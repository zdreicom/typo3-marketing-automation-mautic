<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Finishers;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Escopecz\MauticFormSubmit\Mautic;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticFinisher extends AbstractFinisher
{
    protected $mauticFormSubmitter;

    public function __construct(string $finisherIdentifier = '')
    {
        $authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->mauticFormSubmitter = new Mautic($authorization->getBaseUrl());

        parent::__construct($finisherIdentifier);
    }

    /**
     * Post the form result to a Mautic form
     *
     * @return string|null
     * @api
     */
    protected function executeInternal()
    {
        $formDefinition = $this->finisherContext->getFormRuntime()->getFormDefinition()->getRenderingOptions();
        $mauticId = $this->parseOption('mauticId') ?? $formDefinition['mauticId'];
        $formValues = $this->transformFormStructure($this->finisherContext->getFormValues());

        $form = $this->mauticFormSubmitter->getForm($mauticId);
        $form->submit($formValues);
    }

    /**
     * Transform the TYPO3 form structure to a Mautic structure
     *
     * @param array $formStructure
     * @return array
     */
    private function transformFormStructure(array $formStructure): array
    {
        $mauticStructure = [];
        // Recreate the array with the Id's of the Mautic fields as Mautic has an oblivious lock on field identifiers
        foreach ($formStructure as $key => $value) {
            if (empty($value)) {
                continue;
            }

            // Substitute the TYPO3identifier with the Mautic Alias
            $properties = $this->finisherContext->getFormRuntime()->getFormDefinition()->getElementByIdentifier($key)->getProperties();
            if (!empty($properties['mauticAlias'])) {
                $mauticStructure[$properties['mauticAlias']] = $value;
            }
        }

        return $mauticStructure;
    }
}