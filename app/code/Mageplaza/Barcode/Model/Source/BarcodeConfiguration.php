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

namespace Mageplaza\Barcode\Model\Source;

use Magento\Framework\Data\ValueSourceInterface;
use Mageplaza\Barcode\Block\Adminhtml\Product\Edit\PrintBarcode;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class BarcodeConfiguration
 * @package Mageplaza\Barcode\Model\Source
 */
class BarcodeConfiguration implements ValueSourceInterface
{
    const BARCODE_TYPE     = 'barcode_type';
    const BARCODE_TEMPLATE = 'barcode_label_template';
    const PAPER_TEMPLATE   = 'paper_template';
    const PAPER_SIZE       = 'paper_size';
    const CUSTOM_BARCODE   = 'custom_print_barcode';
    const SUBMIT_URL       = 'submitUrl';

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var PrintBarcode
     */
    protected $_printBarcode;

    /**
     * BarcodeConfiguration constructor.
     *
     * @param HelperData $helperData
     * @param PrintBarcode $printBarcode
     */
    public function __construct(
        HelperData $helperData,
        PrintBarcode $printBarcode
    ) {
        $this->_helperData   = $helperData;
        $this->_printBarcode = $printBarcode;
    }

    /**
     * Get value by name
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getValue($name)
    {
        switch ($name) {
            case self::BARCODE_TYPE:
                return $this->_helperData->getBarcodeType();
            case self::BARCODE_TEMPLATE:
                return $this->_helperData->getLabelTemplate();
            case self::PAPER_TEMPLATE:
                return $this->_helperData->getPaperTemplate();
            case self::CUSTOM_BARCODE:
                return '0';
            case self::SUBMIT_URL:
                return $this->_printBarcode->getAjaxUrl(PrintBarcode::SUBMIT_URL);
        }

        return null;
    }
}
