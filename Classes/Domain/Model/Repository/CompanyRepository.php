<?php
declare(strict_types = 1);

namespace Bitmotion\MarketingAutomationMautic\Domain\Model\Repository;


use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Api\Companies;
use Mautic\Api\CompanyFields;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;

class CompanyRepository
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var CompanyFields
     */
    protected $fieldsApi;

    /**
     * @var Companies
     */
    protected $companiesApi;

    public function __construct(AuthInterface $authorization = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->fieldsApi = $api->newApi('companyFields', $this->authorization, $this->authorization->getBaseUrl());
        $this->companiesApi = $api->newApi('companies', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function findCompanyFields(): array
    {
        $response = $this->fieldsApi->getList();

        return $response['fields'] ?? [];
    }

    public function createCompany(array $parameters)
    {
        return $this->companiesApi->create($parameters);
    }

    public function editCompany(int $id, array $parameters)
    {
        return $this->companiesApi->edit($id, $parameters, false);
    }
}