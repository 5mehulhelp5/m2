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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class PrintSample
 * @package Mageplaza\Barcode\Controller\Adminhtml\System
 */
class PrintSample extends AbstractPrint
{
    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $paperData = $this->getPaperData();
        $maxItems  = $this->getPageMaxItem($paperData);
        $products  = $this->_helperData->getPrintProducts($maxItems, $this->getStoreId());

        try {
            $pdfString = $this->printBarcode(
                1,
                $products,
                $this->getBarcodeData(),
                $paperData,
                $this->getLabelData()
            );

            if ($pdfString) {
                return $this->_json->setData(['data' => base64_encode($pdfString)]);
            }

            return $this->errorMessage(self::ERROR_EMPTY_BARCODE);
        } catch (Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }
}
