<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\ViewHelpers\Form;

use Bitmotion\MarketingAutomationMautic\Domain\Model\Repository\ContactRepository;
use Mautic\Api\Segments;
use Mautic\Auth\AuthInterface;
use TYPO3\CMS\Fluid\ViewHelpers\Form\SelectViewHelper;

class MauticPropertiesViewHelper extends SelectViewHelper
{
    /**
     * @var AuthInterface
     */
    protected $authorization;

    /**
     * @var Segments
     */
    protected $contactRepository;

    public function __construct(ContactRepository $contactRepository)
    {
        parent::__construct();

        $this->contactRepository = $contactRepository;
    }

    /**
     * Fills the form engine dropdown with all known Mautic contact field types
     */
    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $options = array_replace(['' => 'None'], $options);

        $contactFields = $this->contactRepository->findContactFields();

        foreach ($contactFields as $field) {
            $options[$field['alias']] = $field['label'];
        }

        return $options;
    }
}
