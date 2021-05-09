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

/**
 * Class PaperSize
 * @package Mageplaza\Barcode\Model\System\Config\Source
 */
class PaperSize extends OptionArray
{
    const A4     = 'A4';
    const A5     = 'A5';
    const A6     = 'A6';
    const LETTER = 'Letter';

    /**
     * @return array
     */
    public function getOptionHash()
    {
        return [
            self::A4     => __('A4'),
            self::A5     => __('A5'),
            self::A6     => __('A6'),
            self::LETTER => __('Letter'),
        ];
    }
}
