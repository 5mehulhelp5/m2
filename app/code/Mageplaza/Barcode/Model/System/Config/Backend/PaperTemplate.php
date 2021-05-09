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

namespace Mageplaza\Barcode\Model\System\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\Barcode\Model\System\Config\Source\PaperTemplate as Template;

/**
 * Class PaperTemplate
 * @package Mageplaza\Barcode\Model\System\Config\Backend
 */
class PaperTemplate extends Value
{
    /**
     * @var WriterInterface
     */
    protected $_configWriter;

    /**
     * @var Template
     */
    protected $_template;

    /**
     * PaperTemplate constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param WriterInterface $configWriter
     * @param Template $template
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        WriterInterface $configWriter,
        Template $template,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_configWriter = $configWriter;
        $this->_template     = $template;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value
     */
    public function beforeSave()
    {
        $savedValue     = $this->getValue();
        $paperTemplates = $this->_template->getOptionHash();
        unset($paperTemplates[Template::CUSTOM]);
        $templates = array_keys($paperTemplates);

        if (in_array($savedValue, $templates, true)) {
            $templateSpecs = $this->_template->getTemplateBaseSpecs($savedValue);
            $configs       = $this->_template->getPaperConfigPath();
            foreach ($configs as $key => $config) {
                $this->_configWriter->save($config, $templateSpecs[$key]);
            }
        }

        return parent::beforeSave();
    }
}
