<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Model\Client;

interface ConfigInterface
{
    public function setConfig();

    public function getConfig($key);
}
