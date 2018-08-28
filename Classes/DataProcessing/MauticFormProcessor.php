<?php
declare(strict_types=1);

namespace Bitmotion\MarketingAutomationMautic\DataProcessing;

use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

class MauticFormProcessor implements DataProcessorInterface
{
    /**
     * @var string
     */
    protected $baseUrl;

    public function __construct()
    {
        $authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $this->baseUrl = rtrim($authorization->getBaseUrl(), '/');
    }

    /**
     * Process data for the content element "Mautic Form".
     *
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     *
     * @return array the processed data as key/value store
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array
    {
        $processedData['mauticBaseUrl'] = $this->baseUrl;
        return $processedData;
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager(): ObjectManager
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}