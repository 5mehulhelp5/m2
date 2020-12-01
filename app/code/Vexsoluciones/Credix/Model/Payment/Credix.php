<?php

declare(strict_types=1);

namespace Vexsoluciones\Credix\Model\Payment;

use Exception;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\CartInterface;
use Vexsoluciones\Credix\Exceptions\ClientException;
use Vexsoluciones\Credix\Gateway\Client;
use Vexsoluciones\Credix\Gateway\Response\GetTransaction;
use Vexsoluciones\Credix\Service\TransactionService;

class Credix extends AbstractMethod
{
    const PAYMENT_CODE = 'credix';

    const ENVIRONMENT_INTEGRATION_CODE = 'integration';
    const ENVIRONMENT_PRODUCTION_CODE = 'production';

    const ENVIRONMENT_INTEGRATION = 'Integration';
    const ENVIRONMENT_PRODUCTION = 'Production';

    protected $_code = self::PAYMENT_CODE;

    protected $_canCapture = true;
    protected $_canAuthorize = true;

    /**
     * @var Client
     */
    private $client;
    /**
     * @var TransactionService
     */
    private $service;

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        Client $client,
        TransactionService $service,
        AbstractDb $resourceCollection = null,
        AbstractResource $resource = null,
        array $data = [],
        DirectoryHelper $directory = null
    )
    {
        $this->client = $client;
        $this->service = $service;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
    }

    public function isAvailable(CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return Credix
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        try {
            $authentication = $this->client->authTransaction($payment, $amount);
            $verified = $this->client->verifyTransaction($payment, $amount, $authentication);

            $this->guardVerifiedTransaction($verified);

            $this->service->createFromAuthenticationAndSave($authentication, $verified, $payment->getOrder());
        } catch (ClientException $e) {
            throw new ValidatorException(__($e->getMessage()));
        } catch (Exception $exception) {
            throw new ValidatorException(__($exception->getMessage()));
        }

        return $this;
    }

    /**
     * @param GetTransaction $verified
     */
    private function guardVerifiedTransaction(GetTransaction $verified): void
    {
        if ($verified->type === 'error') {
            ClientException::transactionException(
                sprintf('Transaction error: %s.', $verified->message)
            );
        }
    }
}
