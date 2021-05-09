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

namespace Mageplaza\Barcode\Model\System\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as ProductAttribute;

/**
 * Class AttributeBarcode
 * @package Mageplaza\Barcode\Model\System\Config\Source
 */
class AttributeBarcode extends OptionArray
{
    /**
     * @var ProductAttribute
     */
    protected $_attribute;

    /**
     * AttributeBarcode constructor.
     *
     * @param ProductAttribute $attribute
     */
    public function __construct(ProductAttribute $attribute)
    {
        $this->_attribute = $attribute;
    }

    /**
     * @return array
     */
    public function getOptionHash()
    {
        $exceptAttributes  = [
            'meta_title',
            'image_label',
            'small_image_label',
            'url_key',
            'thumbnail_label',
            'category_ids'
        ];
        $productAttributes = ['sku' => 'SKU'];
        $attributes        = $this->_attribute->addFieldToFilter('backend_type', ['varchar', 'static', 'int'])
            ->addFieldToFilter('frontend_input', 'text')
            ->getData();

        foreach ($attributes as $attribute) {
            if ($attribute['frontend_label'] && !in_array($attribute['attribute_code'], $exceptAttributes, true)) {
                $productAttributes[$attribute['attribute_code']] = __($attribute['frontend_label']);
            }
        }

        return $productAttributes;
    }
}
