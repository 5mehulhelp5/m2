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

namespace Mageplaza\Barcode\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Barcode\Controller\Adminhtml\Barcode\AbstractPrint;
use Mageplaza\Barcode\Helper\Data as HelperData;
use Mageplaza\Barcode\Helper\Pdf as PdfHelper;
use Mageplaza\Barcode\Helper\Template as TemplateHelper;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate;

/**
 * Class PrintAction
 * @package Mageplaza\Barcode\Controller\Adminhtml\Import
 */
class MassPrint extends AbstractPrint
{
    /**
     * @var JsHelper
     */
    protected $_jsHelper;

    /**
     * @var Session
     */
    protected $_catalogSession;

    /**
     * MassPrint constructor.
     *
     * @param Context $context
     * @param PdfHelper $pdfHelper
     * @param TemplateHelper $templateHelper
     * @param Json $json
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param PaperTemplate $paperTemplate
     * @param JsHelper $jsHelper
     * @param Session $catalogSession
     */
    public function __construct(
        Context $context,
        PdfHelper $pdfHelper,
        TemplateHelper $templateHelper,
        Json $json,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        PaperTemplate $paperTemplate,
        JsHelper $jsHelper,
        Session $catalogSession
    ) {
        $this->_jsHelper       = $jsHelper;
        $this->_catalogSession = $catalogSession;
        parent::__construct($context, $pdfHelper, $templateHelper, $json, $helperData, $storeManager, $paperTemplate);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $params       = $this->getRequest()->getParams();
        $importCol    = $this->_catalogSession->getBarcodeProducts();
        $customQtyCol = [];
        if (!empty($params['products'])) {
            $newQty = $this->_jsHelper->decodeGridSerializedInput($params['products']);
            foreach ($newQty as $id => $product) {
                $customQtyCol[$id] = $product['qty'];
            }
        }

        $finalQty   = array_replace($importCol, $customQtyCol);
        $productIds = array_keys($finalQty);
        $products   = $this->_helperData->getPrintProducts($productIds, $this->getStoreId());

        try {
            $pdfString = $this->printBarcode(
                $finalQty,
                $products,
                $this->getBarcodeData(),
                $this->getPaperData(),
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
