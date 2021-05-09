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

namespace Mageplaza\Barcode\Helper;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 * @package Mageplaza\Barcode\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpbarcode';
    const ATTRIBUTE_CODE     = 'mp_barcode';
    const MODULE_NAME        = 'Mageplaza_Barcode';

    /**
     * @var Random
     */
    protected $_random;

    /**
     * @var ProductCollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var ProductAction
     */
    protected $_productAction;

    /**
     * @var PaperTemplate
     */
    protected $_paperTemplate;

    /**
     * @var AssetRepo
     */
    protected $_assetRepo;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param ProductCollectionFactory $productCollection
     * @param PriceHelper $priceHelper
     * @param Random $random
     * @param ProductAction $productAction
     * @param PaperTemplate $paperTemplate
     * @param AssetRepo $assetRepo
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $productCollection,
        PriceHelper $priceHelper,
        Random $random,
        ProductAction $productAction,
        PaperTemplate $paperTemplate,
        AssetRepo $assetRepo
    ) {
        $this->_random                   = $random;
        $this->_productCollectionFactory = $productCollection;
        $this->_priceHelper              = $priceHelper;
        $this->_productAction            = $productAction;
        $this->_paperTemplate            = $paperTemplate;
        $this->_assetRepo                = $assetRepo;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * /////////////////////////////////////////////////////////
     * GENERAL CONFIGURATION
     * ////////////////////////////////////////////////////////
     */

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getBarcodeType($storeId = null)
    {
        return $this->getModuleConfig('barcode/type', $storeId);
    }

    /**
     * @param $rawValue
     *
     * @return string
     */
    public function toMillimetre($rawValue)
    {
        return ((float) $rawValue) . 'mm';
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelLogo($storeId = null)
    {
        return $this->getBarcodeLabelConfig('logo', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getBarcodeLabelConfig($code, $storeId)
    {
        return $this->getModuleConfig('barcode/label/' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getBarcodeWidth($storeId = null)
    {
        return $this->getBarcodeLabelConfig('width', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getBarcodeHeight($storeId = null)
    {
        return $this->getBarcodeLabelConfig('height', $storeId);
    }

    /**
     * /////////////////////////////////////////////////////////
     * BARCODE LABEL CONTENT
     * ////////////////////////////////////////////////////////
     */

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getQrCodeSize($storeId = null)
    {
        return $this->getBarcodeLabelConfig('size', $storeId);
    }

    /**
     * @param null $paper
     * @param null $label
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelContent(
        $paper = null,
        $label = null,
        $storeId = null
    ) {
        $template   = $this->checkTemplate($paper, $label);
        $configHtml = $this->getBarcodeLabelConfig('content', $storeId);

        if ($configHtml !== null && $template['label'] === $this->getLabelTemplate()) {
            return $this->getBarcodeLabelConfig('content', $storeId);
        }

        return $this->getDefaultTemplate($template['label'], 'html', $template['paper']);
    }

    /**
     * @param mixed $paper
     * @param mixed $label
     *
     * @return array
     */
    public function checkTemplate($paper, $label)
    {
        return [
            'paper' => $paper === null ? $this->getPaperTemplate() : $paper,
            'label' => $label === null ? $this->getLabelTemplate() : $label,
        ];
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getPaperTemplate($storeId = null)
    {
        return $this->getBarcodePaperConfig('paper_template', $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getBarcodePaperConfig($code, $storeId)
    {
        return $this->getModuleConfig('barcode/paper/' . $code, $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelTemplate($storeId = null)
    {
        return $this->getBarcodeLabelConfig('template', $storeId);
    }

    /**
     * @param string $label
     * @param string $type
     * @param null $paper
     *
     * @return bool|string
     */
    public function getDefaultTemplate($label, $type, $paper = null)
    {
        $path         = $paper !== null
            ? self::MODULE_NAME . "::templates/{$paper}/{$label}.{$type}"
            : self::MODULE_NAME . "::templates/{$label}.{$type}";
        $templateFile = $this->_assetRepo->createAsset($path);

        return $templateFile->getContent();
    }

    /**
     * @param null $paper
     * @param null $label
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelCss($paper = null, $label = null, $storeId = null)
    {
        $template  = $this->checkTemplate($paper, $label);
        $configCss = $this->getBarcodeLabelConfig('css', $storeId);

        if ($configCss !== null && $template['label'] === $this->getLabelTemplate()) {
            return $this->getBarcodeLabelConfig('css', $storeId);
        }

        return $this->getDefaultTemplate($template['label'], 'css', $template['paper']);
    }

    /**
     * @param string $paperTemplate
     *
     * @return array
     */
    public function getBasePaperSpecs($paperTemplate)
    {
        $paper                   = $this->_paperTemplate->getTemplateBaseSpecs($paperTemplate);
        $paper['vertical_pitch'] = $paper['height'] + $paper['vertical'];
        $paper['template']       = $paperTemplate;

        $margin           = $this->getPaperMargin($paper['margin']);
        $margin['bottom'] -= $paper['vertical'];
        $margin['left']   -= $paper['horizontal'];
        $paper['margin']  = $margin;

        return $paper;
    }

    /**
     * @param null $inputMargin
     * @param null $storeId
     * @param bool $previewPaper
     *
     * @return array
     */
    public function getPaperMargin($inputMargin = null, $storeId = null, $previewPaper = false)
    {
        $marginData           = [];
        $configMargin         = $this->getBarcodePaperConfig('margin', $storeId);
        $margin               = $inputMargin !== null ? explode(';', $inputMargin) : explode(';', $configMargin);
        $marginData['top']    = $previewPaper ? $margin['0'] : (float) $margin['0'];
        $marginData['right']  = $previewPaper ? $margin['1'] : (float) $margin['1'];
        $marginData['bottom'] = $previewPaper ? $margin['2'] : (float) $margin['2'];
        $marginData['left']   = $previewPaper ? $margin['3'] : (float) $margin['3'];

        return $marginData;
    }

    /**
     * /////////////////////////////////////////////////////////
     * BARCODE PRINTING PAPER
     * ////////////////////////////////////////////////////////
     */

    /**
     * @param null $storeId
     *
     * @return array
     */
    public function getCustomPaper($storeId = null)
    {
        return [
            'width'          => (float) $this->getLabelWidth($storeId),
            'height'         => (float) $this->getLabelHeight($storeId),
            'padding'        => (float) $this->getLabelPadding($storeId),
            'margin'         => $this->getPaperMargin($storeId),
            'vertical'       => (float) $this->getVerticalSpacing($storeId),
            'horizontal'     => (float) $this->getHorizontalSpacing($storeId),
            'vertical_pitch' => (float) $this->getLabelHeight($storeId) + (float) $this->getVerticalSpacing($storeId),
            'size'           => $this->getPaperSize($storeId),
            'orient'         => $this->getPaperOrientation($storeId),
            'template'       => PaperTemplate::CUSTOM,
        ];
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelWidth($storeId = null)
    {
        return $this->getBarcodePaperConfig('width', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelHeight($storeId = null)
    {
        return $this->getBarcodePaperConfig('height', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getLabelPadding($storeId = null)
    {
        return $this->getBarcodePaperConfig('padding', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getVerticalSpacing($storeId = null)
    {
        return $this->getBarcodePaperConfig('vertical', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getHorizontalSpacing($storeId = null)
    {
        return $this->getBarcodePaperConfig('horizontal', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getPaperSize($storeId = null)
    {
        return $this->getBarcodePaperConfig('paper_size', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getPaperOrientation($storeId = null)
    {
        return $this->getBarcodePaperConfig('orientation', $storeId);
    }

    /**
     * @param mixed $productIds
     * @param int $storeId
     *
     * @return array
     * @throws Exception
     */
    public function getPrintProducts($productIds, $storeId = 0)
    {
        $data = [];

        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*');

        if (is_array($productIds)) {
            $products = $productCollection->addAttributeToFilter('entity_id', $productIds);
        }

        if (is_int($productIds)) {
            $products = $productCollection->setPageSize($productIds);
        }

        /** @var ProductCollection $products */
        foreach ($products as $product) {
            $data[] = $this->prepareProductData($product, $storeId);
        }

        return $data;
    }

    /**
     * @param Product $product
     * @param int $storeId
     *
     * @return array
     * @throws Exception
     */
    public function prepareProductData($product, $storeId)
    {
        $barcodeAttr  = $this->getAttributeBarcode($storeId);
        $barcodeValue = $product->getDataByKey($barcodeAttr);

        if ($barcodeAttr === self::ATTRIBUTE_CODE && $barcodeValue === null) {
            $barcodeValue = $this->generateBarcode();
            $this->_productAction->updateAttributes(
                [$product->getId()],
                [self::ATTRIBUTE_CODE => $barcodeValue],
                $storeId
            );
        }

        return [
            'id'      => $product->getId(),
            'name'    => $product->getName(),
            'price'   => $this->_priceHelper->currency($product->getFinalPrice()),
            'barcode' => $barcodeValue,
            'sku'     => $product->getSku()
        ];
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getAttributeBarcode($storeId = null)
    {
        return $this->getConfigGeneral('attribute_barcode', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return string|string[]|null
     */
    public function generateBarcode($storeId = null)
    {
        $pattern       = $this->getBarcodeNumberTemplate($storeId);
        $patternString = '/\[([1-9]|1[\d]|2[0-5])([AN]{1,2})\]/';
        $code          = preg_replace_callback(
            $patternString,
            function ($param) {
                $pool = strpos($param[2], 'A') === false ? '' : Random::CHARS_UPPERS;
                $pool .= strpos($param[2], 'N') === false ? '' : Random::CHARS_DIGITS;

                return $this->_random->getRandomString($param[1], $pool);
            },
            $pattern
        );

        return $code;
    }

    /**
     * /////////////////////////////////////////////////////////
     * PRODUCT DATA
     * ////////////////////////////////////////////////////////
     */

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getBarcodeNumberTemplate($storeId = null)
    {
        return $this->getConfigGeneral('barcode_template', $storeId);
    }

    /**
     * @return array
     */
    public function getProductIdsForBarcode()
    {
        $isRegenerate      = $this->isRegenerate();
        $productCollection = $this->_productCollectionFactory->create();
        if ($isRegenerate) {
            return $productCollection->getAllIds();
        }

        return $productCollection->addAttributeToFilter(self::ATTRIBUTE_CODE, ['null' => true], 'left')->getAllIds();
    }

    /**
     * @return mixed
     */
    public function isRegenerate()
    {
        return $this->getConfigGeneral('btn_generate');
    }
}
