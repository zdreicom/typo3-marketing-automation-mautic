<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Model\Repository;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;

class ContactRepository
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $contactsApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->contactsApi = $api->newApi('contacts', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function findContactSegments(int $id): array
    {
        $segments = $this->contactsApi->getContactSegments($id);

        return $segments['lists'] ?? [];
    }

    public function findContactFields(): array
    {
        return $this->contactsApi->getFieldList();
    }

    public function createContact(array $parameters): array
    {
        return $this->contactsApi->create($parameters) ?: [];
    }

    public function editContact(int $id, array $data)
    {
        $this->contactsApi->edit($id, $data, false);
    }
}
