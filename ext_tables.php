<?php

// Assign the hooks for pushing newly created and edited forms to Mautic
if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormCreate'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDuplicate'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormDelete'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['beforeFormSave'][1489959059]
        = \Bitmotion\MarketingAutomationMautic\Hooks\MauticFormHook::class;
}
