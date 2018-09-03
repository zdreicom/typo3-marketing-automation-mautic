<?php
declare(strict_types=1);
namespace Bitmotion\MarketingAutomationMautic\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MauticTrackingService implements SingletonInterface
{
    /**
     * @var array
     */
    protected $extensionConfiguration;

    public function __construct(array $extensionConfiguration = null)
    {
        $this->extensionConfiguration = $extensionConfiguration ?: unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['marketing_automation_mautic'], ['allowed_classes' => false]);
    }

    /**
     * Returns true when tracking is enabled
     */
    public function isTrackingEnabled(): bool
    {
        return !empty($this->extensionConfiguration['tracking']) && !empty($this->extensionConfiguration['baseUrl']);
    }

    /**
     * Returns the Mautic frontend tracking code
     */
    public function getTrackingCode(): string
    {
        if (!$this->isTrackingEnabled()) {
            return '';
        }

        return '(function(w,d,t,u,n,a,m){w[\'MauticTrackingObject\']=n;'
            . 'w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),m=d.getElementsByTagName(t)[0];'
            . 'a.async=1;a.src=u;m.parentNode.insertBefore(a,m)})(window,document,\'script\','
            . GeneralUtility::quoteJSvalue($this->extensionConfiguration['baseUrl'] . '/mtc.js')
            . ',\'mt\');mt(\'send\', \'pageview\');';
    }
}
