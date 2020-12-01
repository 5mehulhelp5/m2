<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Gateway;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;
use Vexsoluciones\Credix\Exceptions\ClientException;
use Vexsoluciones\Credix\Gateway\Response\AuthTransaction;
use Vexsoluciones\Credix\Gateway\Response\GetTransaction;
use Vexsoluciones\Credix\Helper\Config;
use Vexsoluciones\Credix\Logger\Logger;
use Vexsoluciones\Credix\Observer\AdditionalDataReader;

class Client extends AbstractClient
{
    const MOVEMENT_TYPE_CONSUME = 1;
    const MOVEMENT_TYPE_REVERSAL_CONSUME = 16;
    const MOVEMENT_TYPE_PAYMENT = 14;
    const MOVEMENT_TYPE_REVERSAL_PAYMENT = 26;

    const CURRENCY_COLON = 188;
    const CURRENCY_USD = 840;

    protected $integrationUrl = 'https://qaconectividadv2.credix.com';
    protected $productionUrl = 'https://conectividadv2.credix.com';

    public function __construct(
        Logger $logger,
        Config $config
    )
    {
        parent::__construct(
            $logger,
            $config
        );
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return AuthTransaction
     * @throws ClientException
     */
    public function authTransaction(InfoInterface $payment, float $amount)
    {
        /** @var Order $order */
        $order = $payment->getOrder();
        $address = $order->getBillingAddress();

        $requestData = [
            'user' => $this->config->getUser(),
            'pass' => $this->config->getHashedPassword(),
            'cardNum' => $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_CARD_NUMBER),
            'expDate' => $this->_parseExpDate($payment),
            'cvv' => $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_CVV),
            'amount' => $this->_parseAmount($amount),
            'quota' => $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_QUOTA),
            'currencyType' => $this->config->getCurrencyTypeByCode($order->getOrderCurrency()->getCurrencyCode()),
            'movementType' => self::MOVEMENT_TYPE_CONSUME,
            'cashUser' => sprintf('%s %s', $address->getFirstname(), $address->getLastname()),
            'channel' => 109,
            'pin' => '',
        ];

        $this->logger->error('pass', ['pass' => $this->config->getPassword(), 'hashed' => $this->config->getHashedPassword()]);

        $data = $this->performPost(
            '/transaction/authtransaction',
            $requestData,
            []
        );

        return AuthTransaction::create($data);
    }

    /**
     * @param InfoInterface $payment
     * @return string
     */
    private function _parseExpDate(InfoInterface $payment)
    {
        return $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_MONTH)
            . '/20'
            . $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_YEAR);
    }

    /**
     * @param float $amount
     * @return int
     */
    private function _parseAmount(float $amount)
    {
        return (int)($amount * 100);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @param AuthTransaction $authentication
     * @throws ClientException
     */
    public function verifyTransaction(InfoInterface $payment, float $amount, AuthTransaction $authentication)
    {
        /** @var Order $order */
        $order = $payment->getOrder();
        $address = $order->getBillingAddress();

        $data = $this->performPost(
            '/transaction/gettransaction',
            [
                'user' => $this->config->getUser(),
                'pass' => $this->config->getHashedPassword(),
                'cardNum' => $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_CARD_NUMBER),
                'expDate' => $this->_parseExpDate($payment),
                'amount' => $this->_parseAmount($amount),
                'quota' => $payment->getAdditionalInformation(AdditionalDataReader::ADDITIONAL_DATA_QUOTA),
                'currencyType' => $this->config->getCurrencyTypeByCode($order->getOrderCurrency()->getCurrencyCode()),
                'authorizationCode' => $authentication->authorizationNumber,
                'movementType' => self::MOVEMENT_TYPE_CONSUME,
                'pin' => '',
            ],
            []
        );

        return GetTransaction::create($data);
    }

    public function deleteTransaction()
    {
        // Not supported yet
    }

    /**
     * @throws ClientException
     */
    public function generateLogicalClosure()
    {
        $result = $this->performPost(
            '/transaction/closuelogic',
            [
                'user' => $this->config->getUser(),
                'pass' => $this->config->getHashedPassword(),
            ],
            []
        );
    }
}
