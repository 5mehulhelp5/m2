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

namespace Mageplaza\Barcode\Controller\Adminhtml\Barcode;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\Barcode\Helper\Data as HelperData;
use Mageplaza\Barcode\Model\ResourceModel\Barcode\Generate as BarcodeGenerate;

/**
 * Class Run
 * @package Mageplaza\Barcode\Controller\Adminhtml\Barcode
 */
class Run extends Action
{
    const REDIRECT_URL = 'adminhtml/system_config/edit/section/mpbarcode';

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var BarcodeGenerate
     */
    protected $_barcodeGenerate;

    /**
     * Generate constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param BarcodeGenerate $barcodeGenerate
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        BarcodeGenerate $barcodeGenerate
    ) {
        $this->_helperData      = $helperData;
        $this->_barcodeGenerate = $barcodeGenerate;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        try {
            $productIds = $this->_helperData->getProductIdsForBarcode();
            $this->_barcodeGenerate->generateBarcodeAttribute($productIds);
            $this->messageManager->addSuccessMessage(
                __('%1 Barcode Number(s) Generated Successfully.', count($productIds))
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('Fail To Generate Barcode Number.'));
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $this->_redirect(self::REDIRECT_URL);
    }
}
