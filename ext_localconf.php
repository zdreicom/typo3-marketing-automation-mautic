<?php

defined('TYPO3_MODE') or die();

call_user_func(function () {
    if (!class_exists('Mautic\\Auth\\ApiAuth')) {
        $pharFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('marketing_automation_mautic') . 'Libraries/mautic-api-library.phar';
        require 'phar://' . $pharFile . '/vendor/autoload.php';
    }

    $marketingDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bitmotion\MarketingAutomation\Dispatcher\Dispatcher::class);
    $marketingDispatcher->addSubscriber(\Bitmotion\MarketingAutomationMautic\Slot\MauticSubscriber::class);

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess']['marketing_automation_mautic'] =
        \Bitmotion\MarketingAutomationMautic\Slot\MauticSubscriber::class . '->setPreferredLocale';

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530047235] = [
        'nodeName' => 'updateSegmentsControl',
        'priority' => 30,
        'class' => \Bitmotion\MarketingAutomationMautic\FormEngine\FieldControl\UpdateSegmentsControl::class,
    ];

    $slotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $slotDispatcher->connect(
        \TYPO3\CMS\Backend\Controller\EditDocumentController::class,
        'initAfter',
        \Bitmotion\MarketingAutomationMautic\Slot\EditDocumentControllerSlot::class,
        'synchronizeSegments'
    );

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'tx_marketingautomationmautic-mautic-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        [
            'source' => 'EXT:marketing_automation_mautic/Resources/Public/Icons/mautic.png',
        ]
    );

    if (TYPO3_MODE === 'FE') {
        $extensionConfiguration = Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $mauticUrl = $extensionConfiguration->getBaseUrl();
        if (!empty($mauticUrl) && $extensionConfiguration['tracking']) {
            $mauticUrl = rtrim($mauticUrl, '/').'/';
            $renderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
            $renderer->addJsInlineCode('Mautic', "(function(w,d,t,u,n,a,m){w['MauticTrackingObject']=n;
            w[n]=w[n]||function(){(w[n].q=w[n].q||[]).push(arguments)},a=d.createElement(t),
            m=d.getElementsByTagName(t)[0];a.async=1;a.src=u;m.parentNode.insertBefore(a,m)
            })(window,document,'script','" .$mauticUrl."mtc.js','mt');
            mt('send', 'pageview');");
        }
    }
});
