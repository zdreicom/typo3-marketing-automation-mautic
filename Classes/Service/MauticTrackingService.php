<?php
declare(strict_types=1);
namespace Bitmotion\MarketingAutomationMautic\Service;

class MauticTrackingService
{
    /**
     * Returns true when tracking is enabled
     */
    public static function trackingEnabled(): bool
    {
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['marketing_automation_mautic'], ['allowed_classes' => false]);

        return $config['tracking'] === '1';
    }

    /**
     * Returns the Mautic frontend tracking code
     */
    public static function getTrackingCode(): string
    {
        $config = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['marketing_automation_mautic'], ['allowed_classes' => false]);

        if (!isset($config['baseUrl'])) {
            return '';
        }

        return "(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
            w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
            m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
            })(window,document,'script','" . rtrim($config['baseUrl'], '/') . "/mtc.js','mt');
            mt('send', 'pageview');";
    }
}
