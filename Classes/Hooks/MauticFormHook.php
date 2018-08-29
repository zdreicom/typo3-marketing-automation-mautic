<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Hooks;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\FormRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Form\Mvc\Persistence\FormPersistenceManagerInterface;

class MauticFormHook
{
    /**
     * @var FormPersistenceManagerInterface
     */
    protected $formPersistenceManager;

    /**
     * @var FormRepository
     */
    protected $formRepository;

    /**
     * @var array
     */
    protected $MAUTIC_FORM_PROTOTYPES = [
        'mautic_finisher_campaign_prototype' => 'campaign',
        'mautic_finisher_standalone_prototype' => 'standalone',
    ];

    /**
     * @var array
     */
    protected $MAUTIC_FIELD_MAP = [
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
     * @var array
     */
    protected $MULTI_ANSWER_FORM_FIELDS = [
        'RadioButton' => 'optionlist',
        'MultiSelect' => 'list',
        'SingleSelect' => 'list',
        'MultiCheckbox' => 'optionlist',
    ];

    public function __construct(
        FormPersistenceManagerInterface $formPersistenceManager = null,
        FormRepository $formRepository = null
    ) {
        if ($formPersistenceManager === null) {
            $formPersistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManagerInterface::class);
        }
        $this->formPersistenceManager = $formPersistenceManager;
        $this->formRepository = $formRepository ?: GeneralUtility::makeInstance(FormRepository::class);
    }

    /**
     * Creates the form in Mautic
     */
    public function beforeFormCreate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $form = $this->formRepository->createForm($this->convertFormStructure($formDefinition));
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        return $this->setMauticFieldIds($form, $formDefinition);
    }

    /**
     * @throws \TYPO3\CMS\Form\Mvc\Persistence\Exception\PersistenceManagerException
     */
    public function beforeFormSave(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $configuration = $this->formPersistenceManager->load($formPersistenceIdentifier);

        // In case the Mautic id is not present (can happen if create call failed earlier)
        if (empty($configuration['renderingOptions']['mauticId'])) {
            return $this->beforeFormCreate($formPersistenceIdentifier, $formDefinition);
        }

        $form = $this->formRepository->editForm((int)$configuration['renderingOptions']['mauticId'], $this->convertFormStructure($formDefinition), true);

        return $this->setMauticFieldIds($form, $formDefinition);
    }

    /**
     * Creates the duplicated form in Mautic. Duplicate form is treated as a new form
     */
    public function beforeFormDuplicate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        return $this->beforeFormCreate($formPersistenceIdentifier, $formDefinition);
    }

    /**
     * Deletes the form in Mautic
     *
     * @throws \TYPO3\CMS\Form\Mvc\Persistence\Exception\PersistenceManagerException
     */
    public function beforeFormDelete(string $formPersistenceIdentifier): string
    {
        $configuration = $this->formPersistenceManager->load($formPersistenceIdentifier);

        $this->formRepository->deleteForm((int)$configuration['renderingOptions']['mauticId']);

        return $formPersistenceIdentifier;
    }

    /**
     * Converts the TYPO3 form structure to a Mautic form structure
     */
    private function convertFormStructure(array &$formDefinition): array
    {
        $returnFormStructure = [];
        $returnFormStructure['name'] = $formDefinition['label'];
        $returnFormStructure['alias'] = $formDefinition['identifier'];
        $returnFormStructure['isPublished'] = true;
        $returnFormStructure['postAction'] = 'return';
        $returnFormStructure['formType'] = $this->MAUTIC_FORM_PROTOTYPES[$formDefinition['mauticFormType']];
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
                if (isset($this->MAUTIC_FIELD_MAP[$formElement['type']])) {
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

                    $formField['type'] = $this->MAUTIC_FIELD_MAP[$formElement['type']];

                    foreach ((array)$formElement['validators'] as $validator) {
                        if ($validator['identifier'] === 'NotEmpty') {
                            $formField['isRequired'] = true;
                        }
                    }

                    // If the form field has options (e.g. RadioButton or a CheckList)
                    if (isset($this->MULTI_ANSWER_FORM_FIELDS[$formElement['type']])) {
                        $listIdentifier = $this->MULTI_ANSWER_FORM_FIELDS[$formElement['type']];

                        $formField['properties'] = [];
                        $formField['properties'][$listIdentifier] = [];
                        $formField['properties'][$listIdentifier]['list'] = [];

                        if ($formElement['type'] === 'MultiSelect') {
                            $formField['properties']['multiple'] = 1;
                        }

                        foreach ((array)$formElement['properties']['options'] as $value => $label) {
                            $formField['properties'][$listIdentifier]['list'][] = [
                                'label' => $label,
                                'value' => $value,
                            ];
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
