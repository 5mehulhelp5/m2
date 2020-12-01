<?php
 
namespace Custom\City\Model\Resource\City;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Custom\City\Model\City',
            'Custom\City\Model\Resource\City'
        );
    }
	protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
                ['states' => $this->getTable('directory_country_region')],
                'main_table.state_id = states.region_id',
                ['region_id as state_id','country_id']
            );
    }
}