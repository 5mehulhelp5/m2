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

namespace Mageplaza\Barcode\Ui\Component\Product\Form\Element;

use Magento\Framework\Data\ValueSourceInterface;
use Magento\Ui\Component\Form\Element\Checkbox;

/**
 * Class UseConfigSettings
 * @package Mageplaza\Barcode\Ui\Component\Product\Form\Element
 */
class UseConfigSettings extends Checkbox
{
    /**
     * Prepare component default configuration data
     *
     * @return void
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if ($config['defaultConfig'] instanceof ValueSourceInterface
            && isset($config['keyInConfig'], $config['defaultConfig'])) {
            $config['defaultConfig'] = $config['defaultConfig']->getValue($config['keyInConfig']);
        }

        $this->setData('config', (array) $config);
        parent::prepare();
    }
}
