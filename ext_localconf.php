<?php
defined('TYPO3_MODE') or die();

call_user_func(function() {
    if (!class_exists('Mautic\\Auth\\ApiAuth')) {
        $pharFile = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('marketing_automation_mautic') . 'Libraries/mautic-api-library.phar';
        require 'phar://' . $pharFile . '/vendor/autoload.php';
    }

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530047235] = [
        'nodeName' => 'updateSegmentsControl',
        'priority' => 30,
        'class' => \Bitmotion\MarketingAutomationMautic\FormEngine\FieldControl\UpdateSegmentsControl::class,
    ];

    $dispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $dispatcher->connect(
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
});
