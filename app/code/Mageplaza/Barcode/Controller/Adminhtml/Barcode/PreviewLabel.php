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
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Barcode\Helper\Data as HelperData;
use Mageplaza\Barcode\Helper\Pdf as PdfHelper;
use Mageplaza\Barcode\Helper\Template as TemplateHelper;
use Mageplaza\Barcode\Model\System\Config\Source\PaperOrientation;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate;
use Mpdf\Output\Destination;

/**
 * Class PreviewLabel
 * @package Mageplaza\Barcode\Controller\Adminhtml\System
 */
class PreviewLabel extends AbstractPrint
{
    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * PreviewLabel constructor.
     *
     * @param Context $context
     * @param PdfHelper $pdfHelper
     * @param TemplateHelper $templateHelper
     * @param Json $json
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param PaperTemplate $paperTemplate
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Context $context,
        PdfHelper $pdfHelper,
        TemplateHelper $templateHelper,
        Json $json,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        PaperTemplate $paperTemplate,
        PriceHelper $priceHelper
    ) {
        $this->_priceHelper = $priceHelper;
        parent::__construct(
            $context,
            $pdfHelper,
            $templateHelper,
            $json,
            $helperData,
            $storeManager,
            $paperTemplate
        );
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $data   = $this->processParameters($params);
        if ($this->hasError) {
            return $this->errorMessage($this->errorMessage);
        }

        $product = $this->_helperData->getPrintProducts(1, $this->getStoreId())[0];
        $config  = $this->getCurrentConfig($data['label']);
        if ($product['barcode'] === null) {
            return $this->errorMessage(self::ERROR_EMPTY_BARCODE);
        }

        $barcode = $this->_templateHelper->getLabelBarcode(
            $product['barcode'],
            $config['type'],
            $data['barcode']['width'],
            $data['barcode']['height']
        );
        $qrCode  = $this->_templateHelper->getLabelBarcode($product['barcode'], 'QR', $data['barcode']['size']);

        $labelData = $this->_templateHelper->setVariables(
            $barcode,
            $product['barcode'],
            $product['name'],
            $product['price'],
            $product['sku'],
            $qrCode
        );

        $labelHtml      = $this->_templateHelper->processTemplate($data['html'], $labelData);
        $finalLabelHtml = $this->_templateHelper->processCustomAttribute($product['id'], $labelHtml);
        $styleData      = [
            'padding' => $data['label']['padding'],
            'css'     => $data['css'],
        ];
        $labelCss       = $this->_templateHelper->addStyle(TemplateHelper::CSS_LABEL_PREVIEW, $styleData, null, true);
        $pdfMargin      = ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0];

        try {
            $pdfString = $this->_pdfHelper->exportToPDF(
                'PreviewLabel.pdf',
                $finalLabelHtml . $labelCss,
                Destination::STRING_RETURN,
                $config['size'],
                $config['orient'],
                $pdfMargin
            );

            return $this->_json->setData(['data' => base64_encode($pdfString)]);
        } catch (Exception $e) {
            return $this->errorMessage($e->getMessage());
        }
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    public function processParameters($params)
    {
        if (isset($params['html'], $params['css'], $params['label'], $params['barcode'])) {
            $label   = $this->validateFloat($params['label'], 'Barcode Label');
            $barcode = $this->validateFloat($params['barcode'], 'Barcode');
            if ($label === false || $barcode === false) {
                return [];
            }

            return [
                'html'    => $params['html'],
                'css'     => $params['css'],
                'label'   => $label,
                'barcode' => $barcode,
            ];
        }

        return $this->raiseError(self::MISSING_PARAMETERS);
    }

    /**
     * @param array $label
     *
     * @return array
     */
    public function getCurrentConfig($label)
    {
        $barcodeType = $this->_helperData->getBarcodeType();
        $width       = (float) $label['width'] + ($label['padding'] * 2);
        $height      = (float) $label['height'] + ($label['padding'] * 2);
        $size        = [$width, $height];

        return [
            'size'   => $size,
            'type'   => $barcodeType,
            'orient' => PaperOrientation::PORTRAIT,
        ];
    }
}
