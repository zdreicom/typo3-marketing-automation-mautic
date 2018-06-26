<?php
declare(strict_types = 1);
namespace Bitmotion\MarketingAutomationMautic\FormEngine\FieldControl;

use TYPO3\CMS\Backend\Form\AbstractNode;

class UpdateSegmentsControl extends AbstractNode
{
    public function render(): array
    {
        $onClick = [];
        $onClick[] = 'this.blur();';
        $onClick[] = 'return !TBE_EDITOR.isFormChanged();';

        $result = [
            'iconIdentifier' => 'actions-refresh',
            'title' => 'updateSegmentsControl',
            'linkAttributes' => [
                'onClick' => implode('', $onClick),
                'href' => $this->data['returnUrl'] . '&tx_marketingautomation_segments[updateSegments]=1',
            ],
        ];

        return $result;
    }
}
