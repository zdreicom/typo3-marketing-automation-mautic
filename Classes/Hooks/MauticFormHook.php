<?php
declare(strict_types=1);

namespace Bitmotion\MarketingAutomationMautic\Hooks;


use Bitmotion\MarketingAutomationMautic\Mautic\AuthorizationFactory;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use Mautic\MauticApi;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManager;

class MauticFormHook
{
    /**
     * @const MAUTIC_FORM_PROTOTYPES
     */
    const MAUTIC_FORM_PROTOTYPES = [
        'mautic_finisher_campaign_prototype' => 'campaign',
        'mautic_finisher_standalone_prototype' => 'standalone',
    ];

    /**
     * @const MAUTIC_FIELD_MAP
     */
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

    /**
     * @const MULTI_ANSWER_FORM_FIELDS
     */
    const MULTI_ANSWER_FORM_FIELDS = [
        'RadioButton' => 'optionlist',
        'MultiSelect' => 'list',
        'SingleSelect' => 'list',
        'MultiCheckbox' => 'optionlist',
    ];

    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $formApi;

    /**
     * MauticFormHook constructor.
     * @throws \Mautic\Exception\ContextNotFoundException
     */
    public function __construct()
    {
        $this->authorization = AuthorizationFactory::createAuthorizationFromExtensionConfiguration();
        $api = new MauticApi();
        $this->formApi = $api->newApi('forms', $this->authorization, $this->authorization->getBaseUrl());
    }

    /**
     * Creates the form in Mautic
     *
     * @param string $formPersistenceIdentifier
     * @param array $formDefinition
     * @return array
     */
    public function beforeFormCreate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $form = $this->formApi->create($this->convertFormStructure($formDefinition));
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        return $this->setMauticFieldIds($form, $formDefinition);
    }

    /**
     * Updates the form in Mautic. If not Mautic id is present the form is treated as a new form
     *
     * @param string $formPersistenceIdentifier
     * @param array $formDefinition
     * @return array
     * @throws \InvalidArgumentException
     */
    public function beforeFormSave(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $persistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManager::class);
        $configuration = $persistenceManager->load($formPersistenceIdentifier);

        // In case the Mautic id is not present (can happen if create call failed earlier)
        if (empty($configuration['renderingOptions']['mauticId'])) {
            return $this->beforeFormCreate($formPersistenceIdentifier, $formDefinition);
        }

        $form = $this->formApi->edit($configuration['renderingOptions']['mauticId'], $this->convertFormStructure($formDefinition), true);

        return $this->setMauticFieldIds($form, $formDefinition);
    }

    /**
     * Creates the duplicated form in Mautic. Duplicate form is treated as a new form
     *
     * @param string $formPersistenceIdentifier
     * @param array $formDefinition
     * @return array
     */
    public function beforeFormDuplicate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        return $this->beforeFormCreate($formPersistenceIdentifier, $formDefinition);
    }

    /**
     * Deletes the form in Mautic
     *
     * @param string $formPersistenceIdentifier
     * @return string
     * @throws \InvalidArgumentException
     */
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
        $returnFormStructure['formType'] = self::MAUTIC_FORM_PROTOTYPES[$formDefinition['mauticFormType']];
        $returnFormStructure['fields'] = [];

        // Check for pages and other form elements that nest fields
        foreach ((array)$formDefinition['renderables'] as $formPageKey => $formPage) {
            foreach ((array)$formPage['renderables'] as $formElementKey => $formElement) {
                if ($formElement['type'] === 'Fieldset' || $formElement['type'] === 'GridRow') {
                    foreach ((array)$formElement['renderables'] as $containerElementKey => $containerElement) {
                        if ($containerElement['type'] === 'Fieldset' || $containerElement['type'] === 'GridRow') {
                            foreach ((array)$containerElement['renderables'] as $containerElementInnerKey => $containerElementInner) {
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
                    $formField['alias'] = str_replace('-', '_', $formElement['identifier']);

                    if (!empty($formElement['properties']['mauticId'])) {
                        $formField['id'] = $formElement['properties']['mauticId'];
                    }
                    if (!empty($formElement['properties']['mauticTable'])) {
                        $formField['leadField'] = $formElement['properties']['mauticTable'];
                    }
                    if (!empty($formElement['defaultValue'])) {
                        $formField['defaultValue'] = $formElement['defaultValue'];
                    }
                    if (!empty($formElement['properties']['fluidAdditionalAttributes']['placeholder'])) {
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

    /**
     * Retrieves the ids of Mautic fields so they can be saved for later use in editing
     *
     * @param array $mauticForm
     * @param array $formDefinition
     * @return array
     */
    private function setMauticFieldIds(array $mauticForm, array $formDefinition): array
    {
        // In case Mautic is not reachable, prevent warnings
        if (!\is_array($mauticForm['form']['fields'])) {
            return $formDefinition;
        }

        foreach ($mauticForm['form']['fields'] as $mauticField) {
            foreach ((array)$formDefinition['renderables'] as $formPageKey => $formPage) {
                foreach ((array)$formPage['renderables'] as $formElementKey => $formElement) {
                    if ($formElement['type'] === 'Fieldset' || $formElement['type'] === 'GridRow') {
                        foreach ((array)$formElement['renderables'] as $containerElementKey => $containerElement) {
                            if ($containerElement['type'] === 'Fieldset' || $containerElement['type'] === 'GridRow') {
                                foreach ((array)$containerElement['renderables'] as $containerElementInnerKey => $containerElementInner) {
                                    if ($mauticField['alias'] === str_replace('-', '_', $containerElementInner['identifier'])) {
                                        $formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['renderables'][$containerElementInnerKey]['properties']['mauticId'] = $mauticField['id'];
                                        $formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['renderables'][$containerElementInnerKey]['properties']['mauticAlias'] = str_replace('-', '_', $containerElementInner['identifier']);
                                    }
                                }
                            } else {
                                if ($mauticField['alias'] === str_replace('-', '_', $containerElement['identifier'])) {
                                    $formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['properties']['mauticId'] = $mauticField['id'];
                                    $formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['renderables'][$containerElementKey]['properties']['mauticAlias'] = str_replace('-', '_', $containerElement['identifier']);
                                }
                            }
                        }
                    }
                    if ($mauticField['alias'] === str_replace('-', '_', $formElement['identifier'])) {
                        $formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['properties']['mauticId'] = $mauticField['id'];
                        $formDefinition['renderables'][$formPageKey]['renderables'][$formElementKey]['properties']['mauticAlias'] = str_replace('-', '_', $formElement['identifier']);
                    }
                }
            }
        }

        return $formDefinition;
    }
}