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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepo;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class Template
 * @package Mageplaza\Barcode\Helper
 */
class Template
{
    const HTML                = 'html';
    const CSS                 = 'css';
    const TEXT                = 'txt';
    const CUSTOM_ATTRIBUTE    = 'custom_attribute';
    const CSS_LABEL_PREVIEW   = 'label_preview';
    const CSS_PAPER_PREVIEW   = 'paper_preview';
    const CSS_LABEL_PRINT     = 'label_print';
    const VARS_BARCODE        = '{{barcode}}';
    const VARS_BARCODE_NUMBER = '{{barcode_number}}';
    const VARS_LOGO           = '{{logo_url}}';
    const VARS_PRODUCT_NAME   = '{{product_name}}';
    const VARS_PRODUCT_PRICE  = '{{product_price}}';
    const VARS_PRODUCT_SKU    = '{{product_sku}}';
    const VARS_QR_CODE        = '{{qrcode}}';
    const CSS_WIDTH           = '{{width}}';
    const CSS_HEIGHT          = '{{height}}';
    const CSS_PADDING         = '{{padding}}';
    const CSS_CUSTOM          = '{{customCss}}';
    const CSS_HORIZONTAL      = '{{horizontal}}';
    const CSS_VERTICAL_PITCH  = '{{vertical_pitch}}';

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var EavConfig
     */
    protected $_eavConfig;

    /**
     * @var AssetRepo
     */
    protected $_assetRepo;

    /**
     * Template constructor.
     *
     * @param Data $helperData
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param EavConfig $eavConfig
     * @param AssetRepo $assetRepo
     */
    public function __construct(
        HelperData $helperData,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        EavConfig $eavConfig,
        AssetRepo $assetRepo
    ) {
        $this->_helperData     = $helperData;
        $this->_productFactory = $productFactory;
        $this->_storeManager   = $storeManager;
        $this->_eavConfig      = $eavConfig;
        $this->_assetRepo      = $assetRepo;
    }

    /**
     * @param string $file
     * @param array $data
     * @param null $paperTemplate
     * @param bool $previewLabel
     * @param string $inputCss
     *
     * @return string
     */
    public function addStyle(
        $file,
        $data,
        $paperTemplate = null,
        $previewLabel = false,
        $inputCss = ''
    ) {
        $labelCss = $this->_helperData->getDefaultTemplate($file, self::TEXT, $paperTemplate);

        $cssData  = $previewLabel
            ? $this->setCssVariables(
                $data['padding'],
                null,
                null,
                null,
                null,
                $data['css']
            )
            : $this->setCssVariables(
                $data['padding'],
                $data['horizontal'],
                $data['vertical_pitch'],
                $data['width'],
                $data['height'],
                $inputCss
            );
        $labelCss = $this->processTemplate($labelCss, $cssData, self::CSS);

        return $labelCss;
    }

    /**
     * @param float $padding
     * @param null $horizontal
     * @param null $verticalPitch
     * @param null $width
     * @param null $height
     * @param null $inputCss
     *
     * @return array
     */
    public function setCssVariables(
        $padding,
        $horizontal = null,
        $verticalPitch = null,
        $width = null,
        $height = null,
        $inputCss = null
    ) {
        $cssVariables = [self::CSS_PADDING => $padding];
        if ($horizontal !== null) {
            $cssVariables[self::CSS_HORIZONTAL] = $horizontal;
        }
        if ($verticalPitch !== null) {
            $cssVariables[self::CSS_VERTICAL_PITCH] = $verticalPitch;
        }
        if ($width !== null) {
            $cssVariables[self::CSS_WIDTH] = $width;
        }
        if ($height !== null) {
            $cssVariables[self::CSS_HEIGHT] = $height;
        }
        if ($inputCss !== null) {
            $cssVariables[self::CSS_CUSTOM] = $inputCss;
        }

        return $cssVariables;
    }

    /**
     * @param string $template
     * @param array $data
     * @param string $type
     *
     * @return string
     */
    public function processTemplate($template, $data, $type = self::HTML)
    {
        foreach ($data as $key => $value) {
            $template = str_replace($key, $value, $template);
        }

        if ($type === self::CSS) {
            return '<style>' . $template . '</style>';
        }

        if ($type === self::CUSTOM_ATTRIBUTE) {
            return $template;
        }

        return '<div class="paper-label-wrapper"><div class="paper-label">' . $template . '</div></div>';
    }

    /**
     * @param string $code
     * @param string $type
     * @param null $size
     * @param null $height
     *
     * @return string
     */
    public function getLabelBarcode($code, $type, $size = null, $height = null)
    {
        if ($type === 'QR') {
            return "<barcode code='{$code}' type='QR' class='qrcode' size='{$size}' error='M' disableborder='1' />";
        }

        return "<barcode code='{$code}' type='{$type}' class='barcode' text='0' size='{$size}' height='{$height}' />";
    }

    /**
     * @param string $barcode
     * @param string $number
     * @param string $name
     * @param string $price
     * @param string $sku
     * @param string $qrCode
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function setVariables(
        $barcode,
        $number,
        $name,
        $price,
        $sku,
        $qrCode
    ) {
        $logoUrl = $this->getLogoUrl();

        return [
            self::VARS_BARCODE        => $barcode,
            self::VARS_BARCODE_NUMBER => $number,
            self::VARS_LOGO           => $logoUrl,
            self::VARS_PRODUCT_NAME   => $name,
            self::VARS_PRODUCT_PRICE  => $price,
            self::VARS_PRODUCT_SKU    => $sku,
            self::VARS_QR_CODE        => $qrCode,
        ];
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getLogoUrl()
    {
        $baseUrl    = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        $modulePath = 'mageplaza/barcode/';

        return $baseUrl . $modulePath . $this->_helperData->getLabelLogo();
    }

    /**
     * @param string $productId
     * @param string $labelHtml
     *
     * @return string
     * @throws LocalizedException
     */
    public function processCustomAttribute($productId, $labelHtml)
    {
        $attributes = [];
        $product    = $this->_productFactory->create()->load($productId);
        preg_match_all('/\{\{.*?\}\}/', $labelHtml, $matches);

        foreach ($matches[0] as $match) {
            $attributeCode      = str_replace(['{{', '}}'], '', $match);
            $attributes[$match] = $this->getAttributeValue($product, $attributeCode);
        }

        return $this->processTemplate($labelHtml, $attributes, 'custom_attribute');
    }

    /**
     * @param Product $product
     * @param string $code
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function getAttributeValue($product, $code)
    {
        $attribute = $this->_eavConfig->getAttribute('catalog_product', $code);
        if ($attribute->getId()) {
            $productAttribute = $product->getDataByKey($code);
            if ($attribute->getFrontendInput() === 'text') {
                return $productAttribute;
            }

            $productAttributeOptions = explode(',', $productAttribute);
            foreach ($productAttributeOptions as $key => $option) {
                $productAttributeOptions[$key] = $attribute->getSource()
                    ->getOptionText($option);
            }

            return implode(',', $productAttributeOptions);
        }

        return null;
    }

    /**
     * @param int $repeat
     *
     * @return string
     */
    public function getPaperHtml($repeat)
    {
        $template = '<!doctype html><html lang="en"><body>';
        for ($i = 1; $i <= $repeat; $i++) {
            $template .= '<div class="paper-label-wrapper"><div class="paper-label"></div></div>';
        }
        $template .= '</body></html>';

        return $template;
    }
}
