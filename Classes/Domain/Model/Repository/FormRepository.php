<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Model\Repository;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Escopecz\MauticFormSubmit\Mautic;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;

class FormRepository
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $formsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->formsApi = $api->newApi('forms', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function createForm(array $parameters): array
    {
        return $this->formsApi->create($parameters) ?: [];
    }

    public function editForm(int $id, array $parameters, $createIfNotExists = false): array
    {
        return $this->formsApi->edit($id, $parameters, $createIfNotExists) ?: [];
    }

    public function deleteForm(int $id): array
    {
        return $this->formsApi->delete($id) ?: [];
    }

    public function submitForm(int $id, array $data)
    {
        $mautic = new Mautic($this->authorization->getBaseUrl());
        $form = $mautic->getForm($id);
        $form->submit($data);
    }
}
