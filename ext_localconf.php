<?php
defined('TYPO3_MODE') or die();

call_user_func(function() {
    if (!class_exists('Mautic\\Auth\\ApiAuth')) {
        $pharFile = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('marketing_automation_mautic') . 'Libraries/mautic-api-library.phar';
        require 'phar://' . $pharFile . '/vendor/autoload.php';
    }

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

    $marketingDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bitmotion\MarketingAutomation\Cookie\Dispatcher::class);
    $marketingDispatcher->addSubscriber(\Bitmotion\MarketingAutomationMautic\Slot\MauticSubscriber::class);

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'tx_marketingautomationmautic-mautic-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        [
            'source' => 'EXT:marketing_automation_mautic/Resources/Public/Icons/mautic.png',
        ]
    );
});
