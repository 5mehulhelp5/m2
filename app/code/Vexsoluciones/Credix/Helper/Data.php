<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Helper;

use Vexsoluciones\Credix\Gateway\Client;

final class Data
{
    /**
     * @param $currencyCode
     * @return int|null
     */
    public function getCurrencyExternalCode($currencyCode)
    {
        if (!$this->isCurrencyAllowed($currencyCode)) {
            return null;
        }

        if ($currencyCode === 'CRC') {
            return Client::CURRENCY_COLON;
        }

        return Client::CURRENCY_USD;
    }

    /**
     * @param $currencyCode
     * @return bool
     */
    public function isCurrencyAllowed($currencyCode)
    {
        return !in_array($currencyCode, ['USD', 'CRC']);
    }

    /**
     * @param float $amount
     * @return int
     */
    public function convertOrderAmount(float $amount)
    {
        return (int)($amount * 100);
    }
}
