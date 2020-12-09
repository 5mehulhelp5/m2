<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Block\Payment\Info;

class Buttons extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'payment/info/buttons.phtml';

    function getOrderId()
    {
        return $this->getParentBlock()->getInfo()->getOrder()->getId();
    }
}
