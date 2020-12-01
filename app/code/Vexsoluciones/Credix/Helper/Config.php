<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Helper;

use Vexsoluciones\Credix\Gateway\Client;
use Vexsoluciones\Credix\Model\Payment\Credix;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    /**
     * @return bool
     */
    public function isEnvironmentProductionMode()
    {
        return $this->getEnvironmentMode() === Credix::ENVIRONMENT_PRODUCTION_CODE;
    }

    /**
     * @return string
     */
    public function getEnvironmentLabel()
    {
        return $this->isEnvironmentProductionMode() ? 'Production' : 'Integration';
    }

    /**
     * @return string
     */
    public function getEnvironmentMode()
    {
        return $this->getValue('environment_mode');
    }

    /**
     * @return string|null
     */
    public function getUser()
    {
        return $this->getValue('username');
    }

    /**
     * @return string
     */
    public function getHashedPassword()
    {
        return hash('sha256', $this->getPassword());
    }

    /**
     * @return string|null
     */
    public function getPassword()
    {
        return $this->getValue('password');
    }

    /**
     * @param string $code
     * @return string
     */
    public function getCurrencyTypeByCode(string $code)
    {
        if (!$this->isAllowedCurrencyCode($code)) return false;
        if ($code === 'USD') return Client::CURRENCY_USD;

        return Client::CURRENCY_COLON;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function isAllowedCurrencyCode(string $code)
    {
        return in_array($code, ['USD', 'CRC']);
    }
}
