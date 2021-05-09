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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Barcode\Helper\Data as HelperData;
use Mageplaza\Barcode\Helper\Pdf as PdfHelper;
use Mageplaza\Barcode\Helper\Template as TemplateHelper;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;

/**
 * Class AbstractPrint
 * @package Mageplaza\Barcode\Controller\Adminhtml\Barcode
 */
abstract class AbstractPrint extends Action
{
    const ERROR_EMPTY_HTML         = 'Barcode Label Content Is Empty';
    const ERROR_EMPTY_BARCODE      = 'Attribute Selected For Barcode Is Empty';
    const ERROR_POSITIVE_FLOAT     = ' Must Be A Positive Float Number';
    const ERROR_PAPER_MARGIN       = 'Incorrect Paper Margin Format';
    const ERROR_VERTICAL_SPACING   = 'Vertical Spacing Cannot Be Greater Than Paper Bottom Margin';
    const ERROR_HORIZONTAL_SPACING = 'Horizontal Spacing Cannot Be Greater Than Paper Left Margin';
    const MISSING_PARAMETERS       = 'Missing Parameters';

    /**
     * @var array
     */
    public $config = [
        'width'      => 'Barcode Label Width',
        'height'     => 'Barcode Label Height',
        'padding'    => 'Barcode Label Padding',
        'vertical'   => 'Vertical Spacing',
        'horizontal' => 'Horizontal Spacing',
    ];

    /**
     * @var bool
     */
    public $hasError = false;

    /**
     * @var string
     */
    public $errorMessage = '';

    /**
     * @var PdfHelper
     */
    protected $_pdfHelper;

    /**
     * @var TemplateHelper
     */
    protected $_templateHelper;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Json
     */
    protected $_json;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PaperTemplate
     */
    protected $_paperTemplate;

    /**
     * AbstractPrint constructor.
     *
     * @param Context $context
     * @param PdfHelper $pdfHelper
     * @param TemplateHelper $templateHelper
     * @param Json $json
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param PaperTemplate $paperTemplate
     */
    public function __construct(
        Context $context,
        PdfHelper $pdfHelper,
        TemplateHelper $templateHelper,
        Json $json,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        PaperTemplate $paperTemplate
    ) {
        $this->_pdfHelper      = $pdfHelper;
        $this->_templateHelper = $templateHelper;
        $this->_helperData     = $helperData;
        $this->_json           = $json;
        $this->_storeManager   = $storeManager;
        $this->_paperTemplate  = $paperTemplate;

        parent::__construct($context);
    }

    /**
     * @param null $inputPaper
     *
     * @return array
     */
    public function getPaperData($inputPaper = null)
    {
        $configPaper = $this->_helperData->getPaperTemplate();
        if ($inputPaper === PaperTemplate::CUSTOM && $configPaper === PaperTemplate::CUSTOM) {
            return $this->_helperData->getCustomPaper();
        }
        $paperTemplate = $inputPaper !== null ? $inputPaper : $configPaper;

        return $this->_helperData->getBasePaperSpecs($paperTemplate);
    }

    /**
     * @param null $paper
     * @param null $label
     *
     * @return array
     */
    public function getLabelData($paper = null, $label = null)
    {
        $labelHtml = $this->_helperData->getLabelContent($paper, $label);
        $labelCss  = $this->_helperData->getLabelCss($paper, $label);

        return [
            'html' => $labelHtml,
            'css'  => $labelCss,
        ];
    }

    /**
     * @param null $inputType
     * @param null $paper
     * @param null $label
     *
     * @return array
     */
    public function getBarcodeData(
        $inputType = null,
        $paper = null,
        $label = null
    ) {
        $configLabel = $this->_helperData->getLabelTemplate();
        $inputPaper  = $paper !== null ? $paper : $this->_helperData->getPaperTemplate();
        $inputLabel  = $label !== null ? $label : $configLabel;
        $barcodeSpec = $this->_paperTemplate->getBarcodeBaseSpecs($inputPaper, $inputLabel);

        return [
            'type'   => $inputType === null ? $this->_helperData->getBarcodeType() : $inputType,
            'width'  => $inputLabel === $configLabel ? $this->_helperData->getBarcodeWidth() : $barcodeSpec['width'],
            'height' => $inputLabel === $configLabel ? $this->_helperData->getBarcodeHeight() : $barcodeSpec['height'],
            'qrSize' => $inputLabel === $configLabel ? $this->_helperData->getQrCodeSize() : $barcodeSpec['size'],
        ];
    }

    /**
     * @param mixed $qtyPerItem
     * @param array $items
     * @param array $barcodeData
     * @param array $paperData
     * @param array $labelData
     *
     * @return bool|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws MpdfException
     */
    public function printBarcode($qtyPerItem, $items, $barcodeData, $paperData, $labelData)
    {
        $templateHtml = '';
        $totalItems   = is_array($qtyPerItem) ? array_sum($qtyPerItem) : $qtyPerItem * count($items);
        $maxItems     = $this->getPageMaxItem($paperData);

        $index = 0;
        foreach ($items as $item) {
            if ($item['barcode'] === null) {
                return false;
            }

            $barcode = $this->_templateHelper->getLabelBarcode(
                $item['barcode'],
                $barcodeData['type'],
                $barcodeData['width'],
                $barcodeData['height']
            );

            $qrCode      = $this->_templateHelper->getLabelBarcode($item['barcode'], 'QR', $barcodeData['qrSize']);
            $productData = $this->_templateHelper->setVariables(
                $barcode,
                $item['barcode'],
                $item['name'],
                $item['price'],
                $item['sku'],
                $qrCode
            );

            $firstTemplateHtml = $this->_templateHelper->processTemplate($labelData['html'], $productData);
            $finalTemplateHtml = $this->_templateHelper->processCustomAttribute($item['id'], $firstTemplateHtml);
            $qtyEachItem       = is_array($qtyPerItem) ? $qtyPerItem[$item['id']] : $qtyPerItem;

            for ($i = 1; $i <= $qtyEachItem; $i++) {
                $index++;
                $templateHtml .= $finalTemplateHtml;
                if ($index % $maxItems === 0 && $index !== $totalItems) {
                    $templateHtml .= '<pagebreak />';
                }
            }
        }

        $templateCss = $this->_templateHelper->addStyle(
            TemplateHelper::CSS_LABEL_PRINT,
            $paperData,
            null,
            false,
            $labelData['css']
        );

        return $this->_pdfHelper->exportToPDF(
            'BarcodeLabels.pdf',
            $templateHtml . $templateCss,
            Destination::STRING_RETURN,
            $paperData['size'],
            $paperData['orient'],
            $paperData['margin']
        );
    }

    /**
     * @param array $data
     *
     * @return float|int
     */
    public function getPageMaxItem($data)
    {
        return $this->_pdfHelper->getMaxItemsPerPage(
            $data['size'],
            $data['width'],
            $data['height'],
            $data['padding'],
            $data['margin'],
            $data['vertical'],
            $data['horizontal'],
            $data['orient']
        );
    }

    /**
     * @param int $maxItems
     * @param array $paperData
     *
     * @return string
     * @throws MpdfException
     */
    public function previewConfigPaper($maxItems, $paperData)
    {
        $templateHtml = $this->_templateHelper->getPaperHtml($maxItems);
        $templateCss  = $this->_templateHelper->addStyle(TemplateHelper::CSS_PAPER_PREVIEW, $paperData);

        return $this->_pdfHelper->exportToPDF(
            'PreviewPaper.pdf',
            $templateHtml . $templateCss,
            Destination::STRING_RETURN,
            $paperData['size'],
            $paperData['orient'],
            $paperData['margin']
        );
    }

    /**
     * @param string $message
     *
     * @return Json
     */
    public function errorMessage($message)
    {
        return $this->_json->setData([
            'error'   => true,
            'message' => __($message),
        ]);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param array $data
     * @param null $prefix
     * @param null $suffix
     *
     * @return mixed
     */
    public function validateFloat($data, $prefix = null, $suffix = null)
    {
        foreach ($data as $key => $input) {
            if (!is_numeric($input) || (float) $input < 0) {
                $labelError = $prefix . ' ' . ucfirst($key) . ' ' . $suffix . ' ' . self::ERROR_POSITIVE_FLOAT;
                if (array_key_exists($key, $this->config)) {
                    $labelError = $this->config[$key] . self::ERROR_POSITIVE_FLOAT;
                }

                return $this->raiseError($labelError);
            }
            $data[$key] = (float) $input;
        }

        return $data;
    }

    /**
     * @param string $message
     *
     * @return bool
     */
    public function raiseError($message)
    {
        $this->hasError     = true;
        $this->errorMessage = $message;

        return false;
    }
}
