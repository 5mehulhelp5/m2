<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vexsoluciones\Credix\Api\TransactionRepositoryInterface;
use Vexsoluciones\Credix\Api\Data\TransactionInterface;
use Vexsoluciones\Credix\Api\Data\TransactionInterfaceFactory;
use Vexsoluciones\Credix\Api\Data\TransactionSearchResultsInterfaceFactory;
use Vexsoluciones\Credix\Model\ResourceModel\Transaction as TransactionResource;
use Vexsoluciones\Credix\Model\ResourceModel\Transaction\CollectionFactory as TransactionCollectionFactory;

class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TransactionInterface[]
     */
    private $instancesById = [];
    /**
     * @var TransactionInterfaceFactory
     */
    private $transactionFactory;
    /**
     * @var TransactionCollectionFactory
     */
    private $transactionCollectionFactory;
    /**
     * @var TransactionSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    /**
     * @var TransactionResource
     */
    private $resource;

    public function __construct(
        TransactionResource $resource,
        TransactionInterfaceFactory $transactionFactory,
        TransactionCollectionFactory $transactionCollectionFactory,
        TransactionSearchResultsInterfaceFactory $searchResultsFactory,
        EntityManager $entityManager
    )
    {
        $this->transactionFactory = $transactionFactory;
        $this->transactionCollectionFactory = $transactionCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->entityManager = $entityManager;
        $this->resource = $resource;
    }

    /**
     *  {@inheritDoc}
     */
    public function getById($id)
    {
        if (isset($this->instancesById[$id])) {
            return $this->instancesById[$id];
        }

        $transaction = $this->create();
        $this->entityManager->load($transaction, $id);

        if (!$transaction->getMethodId()) {
            throw new NoSuchEntityException(__('Requested transaction doesn\'t exist'));
        }

        $this->instancesById[$transaction->getMethodId()] = $transaction;

        return $transaction;
    }

    /**
     *  {@inheritDoc}
     */
    public function create()
    {
        return $this->transactionFactory->create();
    }

    /**
     *  {@inheritDoc}
     */
    public function save(TransactionInterface $transaction, $arguments = [])
    {
        try {
            $this->entityManager->save($transaction, $arguments);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        $this->instancesById[$transaction->getMethodId()] = $transaction;

        return $transaction;
    }

    /**
     *  {@inheritDoc}
     */
    public function delete(TransactionInterface $transaction)
    {
        unset($this->instancesById[$transaction->getMethodId()]);

        $this->entityManager->delete($transaction);

        return true;
    }

    /**
     *  {@inheritDoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->transactionCollectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        if ($sortOrders = $criteria->getSortOrders()) {
            foreach ($sortOrders as $order) {
                $collection->addOrder($order->getField(), $order->getDirection());
            }
        }

        $searchResults->setTotalCount($collection->getSize());

        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());

        $transactions = [];
        /** @var Transaction $transactionModel */
        foreach ($collection as $transactionModel) {
            $transactions[] = $transactionModel;
        }

        $searchResults->setItems($transactions);

        return $searchResults;
    }
}
