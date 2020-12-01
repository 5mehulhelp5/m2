<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Service;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Sales\Model\Order;
use Vexsoluciones\Credix\Api\Data\TransactionInterface;
use Vexsoluciones\Credix\Api\TransactionRepositoryInterface;
use Vexsoluciones\Credix\Gateway\Response\AuthTransaction;
use Vexsoluciones\Credix\Gateway\Response\GetTransaction;

class TransactionService
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository
    )
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param AuthTransaction $authorization
     * @param GetTransaction $verification
     * @param Order $order
     * @return TransactionInterface
     * @throws CouldNotSaveException
     */
    public function createFromAuthenticationAndSave(AuthTransaction $authorization, GetTransaction $verification, Order $order)
    {
        $transaction = $this->transactionRepository->create();

        $transaction->setOrderId($order->getRealOrderId());
        $transaction->setAuthorizationNumber($authorization->authorizationNumber);
        $transaction->setType($authorization->type);
        $transaction->setMessage($authorization->message);

        $transaction->setVerificationNumReference($verification->numReference);
        $transaction->setVerificationMessage($verification->message);

        $this->transactionRepository->save($transaction);

        return $transaction;
    }
}
