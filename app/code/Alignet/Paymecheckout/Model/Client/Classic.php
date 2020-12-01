<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Model\Client;

class Classic extends \Alignet\Paymecheckout\Model\Client
{
    /**
     * @param Classic\Config $configHelper
     * @param Classic\Order $orderHelper
     */
    public function __construct(
        Classic\Config $configHelper,
        Classic\Order $orderHelper
    ) {
        parent::__construct(
            $configHelper,
            $orderHelper
        );
    }
}
