<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Domain\Model\Repository;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Escopecz\MauticFormSubmit\Mautic;
use Mautic\Api\Forms;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormRepository
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Forms
     */
    protected $formsApi;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(AuthInterface $authorization = null, LoggerInterface $logger = null)
    {
        $this->authorization = $authorization ?: AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->formsApi = $api->newApi('forms', $this->authorization, $this->authorization->getBaseUrl());
        $this->logger = $logger ?: GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
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
        $result = $form->submit($data);
        $code = $result['response']['info']['http_code'];

        if ($code < 200 || $code > 400) {
            $this->logger->critical(
                sprintf(
                    'An error occured submitting the form with the Mautic id %d to Mautic. Status code %d returned by Mautic.',
                    $id,
                    $code
                )
            );
        }
    }
}
