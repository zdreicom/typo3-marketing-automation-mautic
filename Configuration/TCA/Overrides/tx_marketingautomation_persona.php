<?php
declare(strict_types = 1);
defined('TYPO3_MODE') or die();

$tempColumns = [
    'tx_marketingautomation_segments' => [
        'label' => 'LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_tca.xlf:tx_marketingautomation_persona.segments',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'itemsProcFunc' => \Bitmotion\MarketingAutomationMautic\UserFunctions\FormEngine\SegmentsItemsProcFunc::class . '->itemsProcFunc',
            'size' => 10,
            'autoSizeMax' => 30,
            'enableMultiSelectFilterTextfield' => true,
            'fieldControl' => [
                'editPopup' => [
                    'disabled' => false,
                ],
                'addRecord' => [
                    'disabled' => false,
                ],
                'listModule' => [
                    'disabled' => false,
                ],
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tx_marketingautomation_persona', $tempColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tx_marketingautomation_persona',
    '--div--;LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_tca.xlf:mautic,tx_marketingautomation_segments',
    '',
    'before:--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended'
);
