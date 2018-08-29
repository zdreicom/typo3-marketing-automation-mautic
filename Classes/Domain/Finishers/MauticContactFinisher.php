<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Finishers;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticContactFinisher extends AbstractFinisher
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $contactsApi;

    /**
     * MauticContactFinisher constructor.
     * @throws \Mautic\Exception\ContextNotFoundException
     */
    public function __construct(string $finisherIdentifier = '')
    {
        $this->authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->contactsApi = $api->newApi('contacts', $this->authorization, $this->authorization->getBaseUrl());

        parent::__construct($finisherIdentifier);
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
            $properties = $formDefinition->getElementByIdentifier($key)->getProperties();

            if (!empty($properties['mauticTable'])) {
                $mauticFields[$properties['mauticTable']] = $value;
            }
        }

        if (\count($mauticFields) === 0) {
            return;
        }

        $mauticFields['ipAddress'] = $_SERVER['REMOTE_ADDR'];
        $this->contactsApi->create($mauticFields);
    }
}
