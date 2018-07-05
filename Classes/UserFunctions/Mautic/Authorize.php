<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\UserFunctions\Mautic;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Auth\AuthInterface;
use Mautic\Exception\AbstractApiException;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;
use TYPO3\CMS\Lang\LanguageService;

class Authorize
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var array
     */
    protected $extensionConfiguration;

    /**
     * @var string
     */
    protected $messageQueueIdentifier = 'marketingautomation.mautic.flashMessages';

    public function __construct()
    {
        if (session_id() === '') {
            session_start();
        }

        $this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['marketing_automation_mautic'], ['allowed_classes' => false]);
        $this->authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration($this->extensionConfiguration);
    }

    public function render(): string
    {
        if (empty($this->extensionConfiguration['baseUrl'])
            || empty($this->extensionConfiguration['publicKey'])
            || empty($this->extensionConfiguration['secretKey'])
        ) {
            return $this->showCredentialsInformation();
        }

        if (substr($this->extensionConfiguration['baseUrl'], -1) === '/') {
            $this->extensionConfiguration['baseUrl'] = rtrim($this->extensionConfiguration['baseUrl'], '/');
            $this->saveConfiguration();
        }

        if (empty($this->extensionConfiguration['accessToken'])
            || empty($this->extensionConfiguration['accessTokenSecret'])
        ) {
            return $this->showAuthorizeButton();
        }

        $api = new MauticApi();
        $api = $api->getContext('contacts', $this->authorization, $this->authorization->getBaseUrl());
        $api->getList('', 0, 1);
        if ($api->getMauticVersion() === null) {
            return $this->showErrorMessage();
        }

        unset($_SESSION['oauth']);
        if (empty($_SESSION)) {
            $sessionName = session_name();
            $sessionCookie = session_get_cookie_params();
            setcookie(
                $sessionName,
                '',
                $sessionCookie['lifetime'],
                $sessionCookie['path'],
                $sessionCookie['domain'],
                $sessionCookie['secure']
            );
        }

        return $this->showSuccessMessage();
    }

    protected function showCredentialsInformation(): string
    {
        $missingInformation = [];
        if (empty($this->extensionConfiguration['baseUrl'])) {
            $missingInformation[] = 'baseUrl';
        }
        if (empty($this->extensionConfiguration['publicKey'])) {
            $missingInformation[] = 'publicKey';
        }
        if (empty($this->extensionConfiguration['secretKey'])) {
            $missingInformation[] = 'secretKey';
        }

        $languageService = $this->getLanguageServer();
        $title = $languageService->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.missingInformation.title');
        $message = sprintf(
            $languageService->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.missingInformation.message'),
            implode(', ', $missingInformation)
        );

        return $this->showErrorMessage($title, $message);
    }

    protected function showAuthorizeButton(): string
    {
        if (empty($_GET['tx_marketingauthorizemautic_authorize'])) {
            $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
            $title = $this->getLanguageServer()->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.withMautic');

            return '<a href="' . $_SERVER['REQUEST_URI'] . '&amp;tx_marketingauthorizemautic_authorize=1" class="btn btn-default btn-sm" title="' . htmlspecialchars($title) . '">'
                . $iconFactory->getIcon('tx_marketingautomationmautic-mautic-icon', Icon::SIZE_SMALL)
                . ' ' . htmlspecialchars($title)
                . '</a>';
        }

        try {
            if ($this->authorization->validateAccessToken()) {
                if ($this->authorization->accessTokenUpdated()) {
                    $accessTokenData = $this->authorization->getAccessTokenData();
                    $this->extensionConfiguration['accessToken'] = $accessTokenData['access_token'];
                    $this->extensionConfiguration['accessTokenSecret'] = $accessTokenData['access_token_secret'];
                }

                $this->saveConfiguration();
            }
        } catch (AbstractApiException $e) {
        }

        return $this->showErrorMessage();
    }

    protected function showErrorMessage(string $title = null, string $message = null): string
    {
        $title = $title ?: $this->getLanguageServer()->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.error.title');
        $message = $message ?: $this->getLanguageServer()->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.error.message');

        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, FlashMessage::ERROR);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier($this->messageQueueIdentifier);
        $flashMessageQueue->enqueue($flashMessage);

        return $flashMessageQueue->renderFlashMessages();
    }

    protected function showSuccessMessage(string $title = null, string $message = null): string
    {
        $title = $title ?: $this->getLanguageServer()->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.success.title');
        $message = $message ?: $this->getLanguageServer()->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_em.xlf:authorization.success.message');

        $flashMessage = GeneralUtility::makeInstance(FlashMessage::class, $message, $title, FlashMessage::OK);
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier($this->messageQueueIdentifier);
        $flashMessageQueue->enqueue($flashMessage);

        return $flashMessageQueue->renderFlashMessages();
    }

    protected function saveConfiguration()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $configurationUtility = $objectManager->get(ConfigurationUtility::class);
        $configurationUtility->writeConfiguration(
            $this->extensionConfiguration,
            'marketing_automation_mautic'
        );
        HttpUtility::redirect($_SERVER['REQUEST_URI']);
    }

    protected function getLanguageServer(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
