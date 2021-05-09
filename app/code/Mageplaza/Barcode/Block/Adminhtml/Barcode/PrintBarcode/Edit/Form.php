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

namespace Mageplaza\Barcode\Block\Adminhtml\Barcode\PrintBarcode\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Barcode\Block\Adminhtml\Barcode\Element\PreviewButton;
use Mageplaza\Barcode\Helper\Data as HelperData;
use Mageplaza\Barcode\Model\System\Config\Source\BarcodeType;
use Mageplaza\Barcode\Model\System\Config\Source\LabelTemplate;
use Mageplaza\Barcode\Model\System\Config\Source\PaperSize;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate;

/**
 * Class Form
 * @package Mageplaza\Barcode\Block\Adminhtml\Barcode\PrintBarcode\Edit
 */
class Form extends Generic
{
    /**
     * @var Yesno
     */
    protected $_yesNo;

    /**
     * @var BarcodeType
     */
    protected $_barcodeType;

    /**
     * @var LabelTemplate
     */
    protected $_labelTemplate;

    /**
     * @var PaperTemplate
     */
    protected $_paperTemplate;

    /**
     * @var PaperSize
     */
    protected $_paperSize;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Yesno $yesNo
     * @param BarcodeType $barcodeType
     * @param LabelTemplate $labelTemplate
     * @param PaperTemplate $PaperTemplate
     * @param PaperSize $paperSize
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Yesno $yesNo,
        BarcodeType $barcodeType,
        LabelTemplate $labelTemplate,
        PaperTemplate $PaperTemplate,
        PaperSize $paperSize,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_yesNo         = $yesNo;
        $this->_barcodeType   = $barcodeType;
        $this->_labelTemplate = $labelTemplate;
        $this->_paperTemplate = $PaperTemplate;
        $this->_paperSize     = $paperSize;
        $this->_helperData    = $helperData;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id'     => 'mp_barcode_print_modal_form',
                'action' => $this->getUrl(
                    'mpbarcode/product/massprint',
                    ['form_key' => $this->getFormKey()]
                ),
                'method' => 'post',
            ],
        ]);

        $form->setHtmlIdPrefix('mp_barcode_');
        $form->setFieldNameSuffix('mp_barcode');

        $qtyFieldset = $form->addFieldset('mp_barcode_qty_fieldset', ['class' => 'fieldset-wide']);
        $qtyFieldset->addField(
            'qty',
            'text',
            [
                'name'     => 'qty',
                'label'    => __('Quantity'),
                'title'    => __('Quantity'),
                'class'    => 'validate-number validate-digits',
                'required' => true,
                'value'    => 50,
                'note'     => __('Print quantity is recommended to be less than 200 barcode labels.')
            ]
        );

        $customFieldset = $form->addFieldset('mp_barcode_custom_fieldset', ['legend' => __('Advanced Printing')]);
        $customFieldset->addField(
            'custom_print_barcode',
            'select',
            [
                'name'   => 'custom_print_barcode',
                'label'  => __('Custom Print Barcode'),
                'title'  => __('Custom Print Barcode'),
                'values' => $this->_yesNo->toOptionArray(),
            ]
        );
        $customFieldset->addField(
            'barcode_type',
            'select',
            [
                'name'     => 'barcode_type',
                'label'    => __('Barcode Type'),
                'title'    => __('Barcode Type'),
                'class'    => 'mp_barcode_custom_depend',
                'disabled' => true,
                'values'   => $this->_barcodeType->getOptionHash(),
                'value'    => $this->_helperData->getBarcodeType(),

            ]
        )->setAfterElementHtml(
            $this->getUseConfigHtml(
                'mp_barcode_use_config_barcode_type',
                'use_config_barcode_type'
            )
        );

        $customFieldset->addField(
            'paper_template',
            'select',
            [
                'name'     => 'paper_template',
                'label'    => __('Paper Template'),
                'title'    => __('Paper Template'),
                'class'    => 'mp_barcode_custom_depend',
                'disabled' => true,
                'values'   => $this->_paperTemplate->getOptionHash(),
                'value'    => $this->_helperData->getPaperTemplate(),
            ]
        )->setAfterElementHtml(
            $this->getUseConfigHtml(
                'mp_barcode_use_config_paper_template',
                'use_config_paper_template'
            )
        );

        $customFieldset->addField(
            'barcode_label_template',
            'select',
            [
                'name'     => 'barcode_label_template',
                'label'    => __('Barcode Label Template'),
                'title'    => __('Barcode Label Template'),
                'class'    => 'mp_barcode_custom_depend',
                'disabled' => true,
                'values'   => $this->_labelTemplate->getOptionHash(),
                'value'    => $this->_helperData->getLabelTemplate(),
            ]
        )->setAfterElementHtml(
            $this->getUseConfigHtml(
                'mp_barcode_use_config_barcode_label_template',
                'use_config_barcode_label_template'
            )
        );

        $customFieldset->addType('previewButton', PreviewButton::class);
        $customFieldset->addField(
            'preview_button',
            'previewButton',
            ['name' => '', 'label' => '',]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param string $inputId
     * @param string $inputName
     *
     * @return string
     */
    public function getUseConfigHtml($inputId, $inputName)
    {
        return '<div class="mp-barcode-use-config-settings">
                    <input name="mp_barcode[' . $inputName . ']"
                            type="checkbox"
                            id="' . $inputId . '"
                            class="mp-barcode-use-config"
                            value="1"
                            checked="checked"
                            onchange=""/>
                    <label for="' . $inputId . '"
                    class="label"><span>' . __('Use Config Settings') . '</span></label>
                </div>';
    }
}
