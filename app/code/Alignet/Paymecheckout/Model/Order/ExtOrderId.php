<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Model\Order;

class ExtOrderId
{
    /**
     * @var \Alignet\Paymecheckout\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param \Alignet\Paymecheckout\Model\ResourceModel\Transaction $transactionResource
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    function __construct(
        \Alignet\Paymecheckout\Model\ResourceModel\Transaction $transactionResource,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        $this->transactionResource = $transactionResource;
        $this->dateTime = $dateTime;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    function generate(\Magento\Sales\Model\Order $order)
    {
        $try = $this->transactionResource->getLastTryByOrderId($order->getId()) + 1;
        return $order->getIncrementId() . ':' . $this->dateTime->timestamp() . ':' . $try;
    }
}
