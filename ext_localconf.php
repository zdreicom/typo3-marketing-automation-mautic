<?php
defined('TYPO3_MODE') or die();

call_user_func(function() {
    if (!class_exists('Mautic\\Auth\\ApiAuth')) {
        $pharFile = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('marketing_automation_mautic') . 'Libraries/mautic-api-library.phar';
        require 'phar://' . $pharFile . '/vendor/autoload.php';
    }

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'tx_marketingautomationmautic-mautic-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        [
            'source' => 'EXT:marketing_automation_mautic/Resources/Public/Icons/mautic.png',
        ]
    );
});
