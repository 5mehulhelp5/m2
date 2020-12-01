<?php
declare(strict_types=1);

namespace Vexsoluciones\Credix\Model\ResourceModel\Transaction;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vexsoluciones\Credix\Api\Data\TransactionInterface;
use Vexsoluciones\Credix\Api\Data\TransactionSearchResultsInterface;
use Vexsoluciones\Credix\Model\Transaction;
use Vexsoluciones\Credix\Model\ResourceModel\Transaction as TransactionResource;

class Collection extends AbstractCollection implements TransactionSearchResultsInterface
{
    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteria;

    protected function _construct()
    {
        $this->_init(Transaction::class, TransactionResource::class);
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function addIsActiveFilter($isActive)
    {
        $this->addFieldToFilter(Transaction::IS_ACTIVE, ['eq' => $isActive]);
        return $this;
    }

    /**
     * @return SearchCriteriaInterface
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        $this->searchCriteria = $searchCriteria;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * @param int $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    public function setItems(array $items)
    {
        if (!$items) {
            return $this;
        }

        foreach ($items as $item) {
            $this->addItem($item);
        }

        return $this;
    }

    /**
     * Adding item to item array
     *
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\Data\Collection
     * @throws \Exception
     */
    public function addItem(\Magento\Framework\DataObject $item)
    {
        $itemId = $this->_getItemId($item);

        if ($itemId !== null) {
            if (isset($this->_items[$itemId])) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception(
                    'Item (' . get_class($item) . ') with the same ID "' . $item->getId() . '" already exists.'
                );
            }
            $this->_items[$itemId] = $item;
        } else {
            $this->_addItem($item);
        }
        return $this;
    }

    /**
     * Add item that has no id to collection
     *
     * @param \Magento\Framework\DataObject $item
     * @return $this
     */
    protected function _addItem($item)
    {
        $this->_items[] = $item;
        return $this;
    }

    /**
     * Retrieve item id
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     */
    protected function _getItemId(\Magento\Framework\DataObject $item)
    {
        return $item->getId();
    }
}
