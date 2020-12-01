<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class Processor
{
    /**
     * @var \Alignet\Paymecheckout\Model\Order
     */
    protected $orderHelper;

    /**
     * @var \Alignet\Paymecheckout\Model\Transaction\Service
     */
    protected $transactionService;

    /**
     * @param \Alignet\Paymecheckout\Model\Order $orderHelper
     * @param \Alignet\Paymecheckout\Model\Transaction\Service $transactionService
     */
    public function __construct(
        \Alignet\Paymecheckout\Model\Order $orderHelper,
        \Alignet\Paymecheckout\Model\Transaction\Service $transactionService
    ) {
        $this->orderHelper = $orderHelper;
        $this->transactionService = $transactionService;
    }

    /**
     * @param string $paymecheckoutOrderId
     * @param string$status
     * @param bool $close
     * @throws LocalizedException
     */
    public function processOld($paymecheckoutOrderId, $status, $close = false)
    {
        $this->transactionService->updateStatus($paymecheckoutOrderId, $status, $close);
    }

    /**
     * @param string $paymecheckoutOrderId
     * @param string $status
     * @throws LocalizedException
     */
    public function processPending($paymecheckoutOrderId, $status)
    {
        $this->transactionService->updateStatus($paymecheckoutOrderId, $status);
    }

    /**
     * @param string $paymecheckoutOrderId
     * @param string $status
     * @throws LocalizedException
     */
    public function processHolded($paymecheckoutOrderId, $status)
    {
        $order = $this->loadOrderByPayuplOrderId($paymecheckoutOrderId);
        $this->orderHelper->setHoldedOrderStatus($order, $status);
        $this->transactionService->updateStatus($paymecheckoutOrderId, $status, true);
    }

    /**
     * @param string $paymecheckoutOrderId
     * @param string $status
     * @throws LocalizedException
     * @todo Implement some additional logic for transaction confirmation by store owner.
     */
    public function processWaiting($paymecheckoutOrderId, $status)
    {
        $this->transactionService->updateStatus($paymecheckoutOrderId, $status);
    }

    /**
     * @param string $paymecheckoutOrderId
     * @param string $status
     * @param float $amount
     * @throws LocalizedException
     */
    public function processCompleted($paymecheckoutOrderId, $status, $amount)
    {
        $order = $this->loadOrderByPayuplOrderId($paymecheckoutOrderId);
        $this->orderHelper->completePayment($order, $amount, $paymecheckoutOrderId);
        $this->transactionService->updateStatus($paymecheckoutOrderId, $status, true);
    }

    /**
     * @param string $paymecheckoutOrderId
     * @return \Alignet\Paymecheckout\Model\Sales\Order
     * @throws LocalizedException
     */
    protected function loadOrderByPayuplOrderId($paymecheckoutOrderId)
    {
        $order = $this->orderHelper->loadOrderByPayuplOrderId($paymecheckoutOrderId);
        if (!$order) {
            throw new LocalizedException(new Phrase('Order not found.'));
        }
        return $order;
    }
}
