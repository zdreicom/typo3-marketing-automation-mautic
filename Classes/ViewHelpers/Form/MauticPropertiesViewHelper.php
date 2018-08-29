<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\ViewHelpers\Form;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

class MauticPropertiesViewHelper extends SelectViewHelper
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $contactFieldsApi;

    public function __construct()
    {
        parent::__construct();

        $this->authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->contactFieldsApi = $api->newApi('contactFields', $this->authorization, $this->authorization->getBaseUrl());
    }

    /**
     * Fills the form engine dropdown with all known Mautic contact field types
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $options[''] = 'None';

        $contactFields = $this->contactFieldsApi->getList();

        foreach ($contactFields['fields'] as $field) {
            $options[$field['alias']] = $field['label'];
        }

        return $options;
    }
}
