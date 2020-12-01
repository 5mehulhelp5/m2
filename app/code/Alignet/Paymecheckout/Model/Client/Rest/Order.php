<?php
/**
 * @copyright Copyright (c)  2019 Alignet  (https://www.pay-me.com)
 */

namespace Alignet\Paymecheckout\Model\Client\Rest;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Alignet\Paymecheckout\Model\Client\OrderInterface;
use Alignet\Paymecheckout\Model\Client\Rest\MethodCaller;

class Order implements OrderInterface
{
    const STATUS_NEW        = 'NEW';
    const STATUS_PENDING    = 'PENDING';
    const STATUS_WAITING    = 'WAITING_FOR_CONFIRMATION';
    const STATUS_CANCELLED  = 'CANCELED';
    const STATUS_REJECTED   = 'REJECTED';
    const STATUS_COMPLETED  = 'COMPLETED';

    /**
     * @var string[]
     */
    protected $statusDescription = [
        self::STATUS_NEW => 'New',
        self::STATUS_PENDING => 'Pending',
        self::STATUS_WAITING => 'Waiting for acceptance',
        self::STATUS_CANCELLED => 'Cancelled',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_COMPLETED => 'Completed'
    ];

    /**
     * @var Order\DataValidator
     */
    protected $dataValidator;

    /**
     * @var Order\DataGetter
     */
    protected $dataGetter;

    /**
     * @var MethodCaller
     */
    protected $methodCaller;

    /**
     * @var Order\Processor
     */
    protected $orderProcessor;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $rawResultFactory;

    /**
     * @var \Alignet\Paymecheckout\Model\ResourceModel\Transaction
     */
    protected $transactionResource;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param Order\DataValidator $dataValidator
     * @param Order\DataGetter $dataGetter
     * @param \Alignet\Paymecheckout\Model\Client\Rest\MethodCaller $methodCaller
     * @param \Alignet\Paymecheckout\Model\ResourceModel\Transaction $transactionResource
     * @param Order\Processor $orderProcessor
     * @param \Magento\Framework\Controller\Result\RawFactory $rawResultFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Order\DataValidator $dataValidator,
        Order\DataGetter $dataGetter,
        MethodCaller $methodCaller,
        \Alignet\Paymecheckout\Model\ResourceModel\Transaction $transactionResource,
        Order\Processor $orderProcessor,
        \Magento\Framework\Controller\Result\RawFactory $rawResultFactory,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->dataValidator = $dataValidator;
        $this->dataGetter = $dataGetter;
        $this->methodCaller = $methodCaller;
        $this->transactionResource = $transactionResource;
        $this->orderProcessor = $orderProcessor;
        $this->rawResultFactory = $rawResultFactory;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function validateCreate(array $data = [])
    {
        return
            $this->dataValidator->validateEmpty($data) &&
            $this->dataValidator->validateBasicData($data) &&
            $this->dataValidator->validateProductsData($data);
    }

    /**
     * @inheritdoc
     */
    public function validateRetrieve($paymecheckoutOrderId)
    {
        return $this->dataValidator->validateEmpty($paymecheckoutOrderId);
    }

    /**
     * @inheritdoc
     */
    public function validateCancel($paymecheckoutOrderId)
    {
        return $this->dataValidator->validateEmpty($paymecheckoutOrderId);
    }

    /**
     * @inheritdoc
     */
    public function validateStatusUpdate(array $data = [])
    {
        return
            $this->dataValidator->validateEmpty($data) &&
            $this->dataValidator->validateStatusUpdateData($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function addSpecialDataToOrder(array $data = [])
    {
        return array_merge($data, [
            'continueUrl' => $this->dataGetter->getContinueUrl(),
            'notifyUrl' => $this->dataGetter->getNotifyUrl(),
            'customerIp' => $this->dataGetter->getCustomerIp(),
            'merchantPosId' => $this->dataGetter->getMerchantPosId()
        ]);
    }

    /**
     * @inheritdoc
     */
    public function create(array $data)
    {
        /**
         * @var $result \OpenPayU_Result
         */
        $response = $this->methodCaller->call('orderCreate', [$data]);
        if ($response) {
            return [
                'orderId' => $response->orderId,
                'redirectUri' => $response->redirectUri,
                'extOrderId' => $data['extOrderId']
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function retrieve($paymecheckoutOrderId)
    {
        $response = $this->methodCaller->call('orderRetrieve', [$paymecheckoutOrderId]);
        if ($response) {
            return [
                'status' => $response->orders[0]->status,
                'amount' => $response->orders[0]->totalAmount / 100
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function cancel($paymecheckoutOrderId)
    {
        return (bool) ($this->methodCaller->call('orderCancel', [$paymecheckoutOrderId]));
    }

    /**
     * @inheritdoc
     */
    public function statusUpdate(array $data = [])
    {
        return (bool) ($this->methodCaller->call('orderStatusUpdate', [$data]));
    }

    /**
     * @inheritdoc
     */
    public function consumeNotification(\Magento\Framework\App\Request\Http $request)
    {
        if (!$request->isPost()) {
            throw new LocalizedException(new Phrase('POST request is required.'));
        }
        $response = $this->methodCaller->call('orderConsumeNotification', [$request->getContent()]);
        if ($response) {
            return [
                'paymecheckoutOrderId' => $response->order->orderId,
                'status' => $response->order->status,
                'amount' => (float) $response->order->totalAmount / 100
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getDataForOrderCreate(\Magento\Sales\Model\Order $order)
    {
        $data = ['products' => $this->dataGetter->getProductsData($order)];
        $shippingData = $this->dataGetter->getShippingData($order);
        if ($shippingData) {
            $data['products'][] = $shippingData;
        }
        $buyerData = $this->dataGetter->getBuyerData($order);
        if ($buyerData) {
            $data['buyer'] = $buyerData;
        }
        $basicData = $this->dataGetter->getBasicData($order);
        return array_merge($basicData, $data);
    }

    /**
     * @inheritdoc
     */
    public function getNewStatus()
    {
        return self::STATUS_NEW;
    }

    /**
     * @inheritdoc
     */
    public function paymentSuccessCheck()
    {
        return is_null($this->request->getParam('error'));
    }

    /**
     * @inheritdoc
     */
    public function canProcessNotification($paymecheckoutOrderId)
    {
        return !in_array(
            $this->transactionResource->getStatusByPayuplOrderId($paymecheckoutOrderId),
            [self::STATUS_COMPLETED, self::STATUS_CANCELLED]
        );
    }

    /**
     * @inheritdoc
     */
    public function processNotification($paymecheckoutOrderId, $status, $amount)
    {
        /**
         * @var $result \Magento\Framework\Controller\Result\Raw
         */
        $newest = $this->transactionResource->checkIfNewestByPayuplOrderId($paymecheckoutOrderId);
        $this->orderProcessor->processStatusChange($paymecheckoutOrderId, $status, $amount, $newest);
        $result = $this->rawResultFactory->create();
        $result->setHttpResponseCode(200);
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getStatusDescription($status)
    {
        if (isset($this->statusDescription[$status])) {
            return (string) __($this->statusDescription[$status]);
        }
        return false;
    }
}
