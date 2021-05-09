<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Barcode
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Barcode\Block\Adminhtml\Barcode\ImportBarcode\Edit;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Barcode\Block\Adminhtml\Barcode\Element\CheckButton;

/**
 * Class Form
 * @package Mageplaza\Barcode\Block\Adminhtml\Barcode\ImportBarcode\Edit
 */
class Form extends Generic
{
    /**
     * @return Generic|void
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'      => 'mp_barcode_import_modal_form',
                    'action'  => $this->getUrl(
                        'mpbarcode/import/validate',
                        ['form_key' => $this->getFormKey()]
                    ),
                    'method'  => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        $form->setHtmlIdPrefix('mp_barcode_');
        $form->setFieldNameSuffix('mp_barcode');

        $importFieldSet = $form->addFieldset('mp_barcode_import_fieldset', ['class' => 'fieldset-wide']);
        $importFieldSet->addField(
            'import',
            'file',
            [
                'name'              => 'import',
                'label'             => __('Import'),
                'title'             => __('Import'),
                'class'             => 'required-entry',
                'required'          => true,
                'note'              => $this->getInputNote(),
                'allowedExtensions' => ['csv'],
            ]
        )->setAfterElementHtml($this->getDownloadSampleFileHtml());

        $importFieldSet->addType('checkButton', CheckButton::class);
        $importFieldSet->addField('check_button', 'checkButton', [
            'name'  => '',
            'label' => ''
        ]);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function getInputNote()
    {
        $html = '<span>' . __('Allow csv file type.') . '</span><br>';
        $html .= '<a href="http://docs.mageplaza.com/barcode/" target="_blank" rel="noopener noreferrer">';
        $html .= __('Learn more ');
        $html .= '</a>';

        return $html;
    }

    /**
     * @return string
     */
    public function getDownloadSampleFileHtml()
    {
        $downloadUrl = $this->getUrl('mpbarcode/import/download');

        return "<a href='{$downloadUrl}'>" . __('Download sample file') . '</a>';
    }
}
