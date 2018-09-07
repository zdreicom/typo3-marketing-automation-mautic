<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\Hooks;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\FormRepository;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
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
     * @var LoggerInterface
     */
    protected $logger;

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
    protected $ALLOWED_FIELD_TYPES = [
        'checkbox' => true,
        'checkboxgrp' => true,
        'date' => true,
        'hidden' => true,
        'text' => true,
        'radiogrp' => true,
        'textarea' => true,
        'password' => true,
        'select' => true,
    ];

    /**
     * @var array
     */
    protected $ALLOWED_MULTI_ANSWER_FORM_FIELDS = [
        'optionlist' => true,
        'list' => true,
    ];

    /**
     * MauticFormHook constructor.
     *
     * @param FormPersistenceManagerInterface|null $formPersistenceManager
     * @param FormRepository|null                  $formRepository
     * @param LoggerInterface|null                 $logger
     */
    public function __construct(
        FormPersistenceManagerInterface $formPersistenceManager = null,
        FormRepository $formRepository = null,
        LoggerInterface $logger = null
    ) {
        if ($formPersistenceManager === null) {
            $formPersistenceManager = GeneralUtility::makeInstance(ObjectManager::class)->get(FormPersistenceManagerInterface::class);
        }
        $this->formPersistenceManager = $formPersistenceManager;
        $this->formRepository = $formRepository ?: GeneralUtility::makeInstance(FormRepository::class);
        $this->logger = $logger ?: GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * Creates the form in Mautic
     *
     * @param string $formPersistenceIdentifier
     * @param array  $formDefinition
     *
     * @return array
     */
    public function beforeFormCreate(string $formPersistenceIdentifier, array $formDefinition): array
    {
        $form = $this->formRepository->createForm($this->convertFormStructure($formDefinition));
        $formDefinition['renderingOptions']['mauticId'] = $form['form']['id'];

        // Predefined defaults seem not to be working, temporary workaround
        if (!isset($formDefinition['renderingOptions']['mauticFormType'])) {
            $formDefinition['renderingOptions']['mauticFormType'] = 'mautic_finisher_standalone_prototype';
        }

        return $this->setMauticFieldIds($form, $formDefinition);
    }

    /**
     * Updates the form in Mautic
     *
     * @param string $formPersistenceIdentifier
     * @param array  $formDefinition
     *
     * @return array
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
     *
     * @param string $formPersistenceIdentifier
     * @param array  $formDefinition
     *
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
     *
     * @return string
     */
    public function beforeFormDelete(string $formPersistenceIdentifier): string
    {
        $configuration = $this->formPersistenceManager->load($formPersistenceIdentifier);

        $this->formRepository->deleteForm((int)$configuration['renderingOptions']['mauticId']);

        return $formPersistenceIdentifier;
    }

    /**
     * Converts the TYPO3 form structure to a Mautic form structure
     *
     * @param array $formDefinition
     *
     * @return array
     */
    protected function convertFormStructure(array &$formDefinition): array
    {
        $returnFormStructure = [];
        $returnFormStructure['name'] = $formDefinition['label'];
        $returnFormStructure['alias'] = $formDefinition['identifier'];
        $returnFormStructure['isPublished'] = true;
        $returnFormStructure['postAction'] = 'return';
        $returnFormStructure['formType'] = $this->MAUTIC_FORM_PROTOTYPES[$formDefinition['renderingOptions']['mauticFormType']];
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
                if (isset($this->ALLOWED_FIELD_TYPES[$formElement['properties']['mauticFieldType']])) {
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
                    if (!empty($formElement['properties']['elementDescription'])) {
                        $formField['helpMessage'] = $formElement['properties']['elementDescription'];
                    }

                    $formField['type'] = $formElement['properties']['mauticFieldType'];

                    foreach ((array)$formElement['validators'] as $validator) {
                        if ($validator['identifier'] === 'NotEmpty') {
                            $formField['isRequired'] = true;
                        }
                    }

                    // If the form field has options (e.g. RadioButton or a CheckList)
                    if (isset($formElement['properties']['mauticListIdentifier'])) {
                        if (isset($this->ALLOWED_MULTI_ANSWER_FORM_FIELDS[$formElement['properties']['mauticListIdentifier']])) {
                            $listIdentifier = $formElement['properties']['mauticListIdentifier'];

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
                        } else {
                            $this->logger->notice('Unknow Mautic multi-answer list type encountered.', [
                                'fieldType' => $formElement['type'],
                                'listType' => $formElement['mauticFieldTypeMapping'],
                            ]);
                        }
                    }

                    $returnFormStructure['fields'][] = $formField;
                } else {
                    $this->logger->notice('Unknow Mautic form field type encountered.', ['fieldType' => $formElement['type']]);
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
     *
     * @return array
     */
    protected function setMauticFieldIds(array $mauticForm, array $formDefinition): array
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
