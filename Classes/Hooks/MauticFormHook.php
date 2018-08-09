<?php
declare(strict_types=1);

namespace Bitmotion\MarketingAutomationMautic\Hooks;


use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManager;

class MauticFormHook
{
    const MAUTIC_FORM_PROTOTYPES = [
        'mautic_finisher_campaign_prototype' => 'campaign',
        'mautic_finisher_standalone_prototype' => 'standalone',
    ];

    const MAUTIC_FIELD_MAP = [
        'Text' => 'text',
        'RadioButton' => 'radiogrp',
        'Textarea' => 'textarea',
        'Checkbox' => 'checkbox',
        'MultiSelect' => 'select',
        'SingleSelect' => 'select',
        'MultiCheckbox' => 'checkboxgrp',
        'DatePicker' => 'date',
        'Hidden' => 'hidden',
        'Password' => 'password',
        'AdvancedPassword' => 'password',
    ];

    const MULTI_ANSWER_FORM_FIELDS = [
        'RadioButton' => 'optionlist',
        'MultiSelect' => 'list',
        'SingleSelect' => 'list',
        'MultiCheckbox' => 'optionlist',
    ];

    /**
     * @var Segments
     */
    protected $formApi;

    public function __construct()
    {
        $this->authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->formApi = $api->newApi('forms', $this->authorization, $this->authorization->getBaseUrl());
    }

    public function beforeFormCreate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $form = $this->formApi->create($this->convertFormStructure($formDefinition));
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        return $formDefinition;
    }

    public function beforeFormSave(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $persistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManager::class);
        $configuration = $persistenceManager->load($formPersistenceIdentifier);

        $this->formApi->edit($configuration['renderingOptions']['mauticId'], $this->convertFormStructure($formDefinition), true);

        return $formDefinition;
    }

    public function beforeFormDuplicate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        return $this->beforeFormCreate($formPersistenceIdentifier, $formDefinition);
    }

    public function beforeFormDelete(string $formPersistenceIdentifier): string
    {
        $persistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManager::class);
        $configuration = $persistenceManager->load($formPersistenceIdentifier);

        $this->formApi->delete($configuration['renderingOptions']['mauticId']);

        return $formPersistenceIdentifier;
    }

    /**
     * Converts the TYPO3 form structure to a Mautic form structure
     *
     * @param array $formDefinition
     * @return array
     */
    private function convertFormStructure(array &$formDefinition): array
    {

        $returnFormStructure = [];
        $returnFormStructure['name'] = $formDefinition['label'];
        $returnFormStructure['alias'] = $formDefinition['identifier'];
        $returnFormStructure['isPublished'] = true;
        $returnFormStructure['postAction'] = 'return';
        $returnFormStructure['formType'] = self::MAUTIC_FORM_PROTOTYPES[$formDefinition['prototypeName']];
        $returnFormStructure['fields'] = [];

        // Check for pages and other form elements that nest fields
        foreach ((array)$formDefinition['renderables'] as $formPage) {
            foreach ((array)$formPage['renderables'] as $formElement) {
                if ($formElement['type'] === 'Fieldset' || $formElement['type'] === 'GridRow') {
                    foreach ((array)$formElement['renderables'] as $containerElement) {
                        if ($containerElement['type'] === 'Fieldset' || $containerElement['type'] === 'GridRow') {
                            foreach ((array)$containerElement['renderables'] as $containerElementInner) {
                                $formPage['renderables'][] = $containerElementInner;
                            }
                        } else {
                            $formPage['renderables'][] = $containerElement;
                        }
                    }
                }
            }

            // For each form element on the page
            foreach ((array)$formPage['renderables'] as $formElement) {
                if (isset(self::MAUTIC_FIELD_MAP[$formElement['type']])) {
                    $formField = [];
                    $formField['label'] = $formElement['label'] ?? $formElement['identifier'];

                    if (!empty($formElement['properties']['mauticAlias'])) {
                        $formField['alias'] = $formElement['properties']['mauticAlias'];
                    } else {
                        $formField['alias'] = str_replace('-', '_', $formElement['identifier']);
                        $formElement['properties']['mauticAlias'] = str_replace('-', '_', $formElement['identifier']);
                    }
                    if (!empty($formElement['properties']['mauticTable'])) {
                        $formField['leadField'] = $formElement['properties']['mauticTable'];
                    }
                    if (!empty($formElement['defaultValue'])) {
                        $formField['defaultValue'] = $formElement['defaultValue'];
                    }
                    if (!empty($formElement['properties']['fluidAdditionalAttributes']['placeholder'])) {
                        $formField['properties'] = [];
                        $formField['properties']['placeholder'] = $formElement['properties']['fluidAdditionalAttributes']['placeholder'];
                    }

                    $formField['type'] = self::MAUTIC_FIELD_MAP[$formElement['type']];

                    foreach ((array)$formElement['validators'] as $validator) {
                        if ($validator['identifier'] === 'NotEmpty') {
                            $formField['isRequired'] = true;
                        }
                    }

                    // If the form field has options (e.g. RadioButton or a CheckList)
                    if (isset(self::MULTI_ANSWER_FORM_FIELDS[$formElement['type']])) {

                        $listIdentifier = self::MULTI_ANSWER_FORM_FIELDS[$formElement['type']];

                        $formField['properties'] = [];
                        $formField['properties'][$listIdentifier] = [];
                        $formField['properties'][$listIdentifier]['list'] = [];

                        if ($formElement['type'] === 'MultiSelect') {
                            $formField['properties']['multiple'] = 1;
                        }

                        foreach ((array)$formElement['properties']['options'] as $key => $value) {
                            $formField['properties'][$listIdentifier]['list'][] = ['label' => $key, 'value' => $value];
                        }
                    }

                    $returnFormStructure['fields'][] = $formField;
                }
            }
        }

        return $returnFormStructure;
    }
}