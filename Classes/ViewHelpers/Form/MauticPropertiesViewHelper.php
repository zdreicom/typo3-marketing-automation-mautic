<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\ViewHelpers\Form;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\CompanyRepository;
use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\ContactRepository;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;
use TYPO3\CMS\Lang\LanguageService;

class MauticPropertiesViewHelper extends SelectViewHelper
{
    /**
     * @var ContactRepository
     */
    protected $contactRepository;

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    public function __construct(ContactRepository $contactRepository, CompanyRepository $companyRepository)
    {
        parent::__construct();

        $this->contactRepository = $contactRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     * Fills the form engine dropdown with all known Mautic contact and company field types
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $options = array_replace(['' => 'None'], $options);

        $contactFields = $this->contactRepository->findContactFields();
        $companyFields = $this->companyRepository->findCompanyFields();

        $languageService = $this->getLanguageServer();
        $contactsLang = $languageService->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_tca.xlf:mautic.contact');
        $companiesLang = $languageService->sL('LLL:EXT:marketing_automation_mautic/Resources/Private/Language/locallang_tca.xlf:mautic.company');

        $contactOptions = [];
        foreach ($contactFields as $field) {
            $contactOptions[$field['alias']] = $contactsLang.': '.$field['label'];
        }
        sort($contactOptions);
        $companyOptions = [];
        foreach ($companyFields as $field) {
            $companyOptions[$field['alias']] = $companiesLang.': '.$field['label'];
        }
        sort($companyOptions);

        $options = array_merge($options, $contactOptions, $companyOptions);

        return $options;
    }

    protected function getLanguageServer(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
