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

namespace Mageplaza\Barcode\Model\ResourceModel\Barcode;

use Exception;
use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Mageplaza\Barcode\Helper\Data as HelperData;

/**
 * Class Generate
 * @package Mageplaza\Barcode\Model\ResourceModel\Barcode
 */
class Generate extends ProductAction
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @param array $entityIds
     * @param bool $import
     *
     * @return $this
     * @throws Exception
     */
    public function generateBarcodeAttribute($entityIds, $import = false)
    {
        $object = new DataObject();
        $object->setStoreId(0);
        $this->getConnection()->beginTransaction();
        $attribute = $this->getAttribute(HelperData::ATTRIBUTE_CODE);
        $i         = 0;

        try {
            foreach ($entityIds as $key => $value) {
                $i++;
                $entityId = $import ? $key : $value;
                $object->setId($entityId);
                $object->setEntityId($entityId);
                $barcode = $import ? $value : $this->getHelper()->generateBarcode();
                /** @var AbstractModel $object */
                $this->_saveAttributeValue($object, $attribute, $barcode);
                if ($i % 1000 === 0) {
                    $this->_processAttributeValues();
                }
            }

            $this->_processAttributeValues();
            $this->getConnection()->commit();
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * @return HelperData|mixed
     */
    protected function getHelper()
    {
        if (!$this->_helperData) {
            $objectManager     = ObjectManager::getInstance();
            $this->_helperData = $objectManager->create(HelperData::class);
        }

        return $this->_helperData;
    }
}
