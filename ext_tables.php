<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {
    // Assign the hooks for pushing newly created and edited forms to Mautic
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormCreate'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;
});
