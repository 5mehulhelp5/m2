<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Block\Adminhtml\Payment;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Sales\Model\Order;
use Vexsoluciones\Credix\Model\Payment\Credix;
use Vexsoluciones\Credix\Model\ResourceModel\Transaction\Collection;
use Vexsoluciones\Credix\Model\Transaction;

class Info extends ConfigurableInfo
{
    private $transaction = null;
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var Collection
     */
    private $transactionCollection;
    /**
     * @var \Magento\Payment\Model\Info
     */
    private $info;

    public function __construct(
        Context $context,
        ConfigInterface $config,
        Registry $registry,
        Collection $transactionCollection,
        \Magento\Payment\Model\Info $info,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $config,
            $data
        );
        $this->registry = $registry;
        $this->transactionCollection = $transactionCollection;
        $this->info = $info;
    }

    /**
     * @return bool
     */
    public function shouldDisplayPaymentSection()
    {
        $this->transaction = $this->getTransaction();

        return $this->isCredixPaymentMethod() && $this->transaction !== null;
    }

    /**
     * @return Transaction|null
     */
    public function getTransaction()
    {
        if (!$this->isCredixPaymentMethod())
            return null;

        if (null !== $this->transaction)
            return $this->transaction;

        /** @var Transaction $transaction */
        $transaction = $this->transactionCollection->addFieldToFilter(
            Transaction::ORDER_ID,
            $this->getOrder()->getIncrementId()
        )->getFirstItem();

        if (null !== $transaction) {
            $this->transaction = $transaction;
        }

        return $this->transaction;
    }

    /**
     * @return bool
     */
    private function isCredixPaymentMethod()
    {
        $method = $this->getMethod()->getMethod();

        return $method === Credix::PAYMENT_CODE;
    }

    /**
     * @return MethodInterface
     */
    public function getMethod()
    {
        $order = $this->registry->registry('current_order');

        return $order->getPayment();
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }

    /**
     * @return \Magento\Payment\Model\Info|InfoInterface
     */
    public function getInfo()
    {
        $payment = $this->getMethod();
        $this->info->setData($payment->getData());

        return $this->info;
    }
}
