<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vexsoluciones\Credix\Api\Data\TransactionInterface;
use Vexsoluciones\Credix\Api\Data\TransactionSearchResultsInterface;

interface TransactionRepositoryInterface
{
    /**
     * Retrieve transaction data by id
     *
     * @param int $id
     * @return TransactionInterface
     * @throws NoSuchEntityException
     */
    public function getById($id);

    /**
     * Create transaction instance
     *
     * @return TransactionInterface
     */
    public function create();

    /**
     * Save transaction data
     *
     * @param TransactionInterface $transaction
     * @param array $arguments
     * @return TransactionInterface
     * @throws CouldNotSaveException
     */
    public function save(TransactionInterface $transaction, $arguments = []);

    /**
     * Delete transaction
     *
     * @param TransactionInterface $transaction
     * @return mixed
     */
    public function delete(TransactionInterface $transaction);

    /**
     * Retrieve transaction matching the specified criteria
     *
     * @param SearchCriteriaInterface $criteria
     * @return TransactionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);
}
