<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    if (!class_exists('Mautic\\Auth\\ApiAuth')) {
        require \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('marketing_automation_mautic') . 'Libraries/vendor/autoload.php';
    }

    $marketingDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Bitmotion\MarketingAutomation\Dispatcher\Dispatcher::class);
    $marketingDispatcher->addSubscriber(\Bitmotion\MarketingAutomationMautic\Slot\MauticSubscriber::class);

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['settingLanguage_postProcess']['marketing_automation_mautic'] =
        \Bitmotion\MarketingAutomationMautic\Slot\MauticSubscriber::class . '->setPreferredLocale';

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Bitmotion\MarketingAutomationMautic\Form\FormDataProvider\MauticFormDataProvider::class] = [
        'depends' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\DatabaseRowDefaultValues::class,
        ],
        'before' => [
            \TYPO3\CMS\Backend\Form\FormDataProvider\TcaSelectItems::class,
        ],
    ];

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
        if (\Bitmotion\MarketingAutomationMautic\Service\MauticTrackingService::trackingEnabled()) {
            $renderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
            $renderer->addJsInlineCode('Mautic', \Bitmotion\MarketingAutomationMautic\Service\MauticTrackingService::getTrackingCode());
        }
    }

    // Register for hook to show preview of tt_content element of CType="mautic_form" in page module
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem']['mautic_form'] =
        \BitMotion\MarketingAutomationMautic\Hooks\PageLayoutView\MauticFormPreviewRenderer::class;
});
