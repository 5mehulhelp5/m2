<?php
namespace Custom\City\Controller\Index;
 
use Custom\City\Controller\City;
 
class Cities extends City
{
    public function execute()
    {
        $state_id = $this->getRequest()->getParam('state');
		$cities = array();
		$cities_options = $this->_cityFactory->create()->getCollection()->addFieldToFilter('state_id',$state_id)
		->addFieldToFilter('status',1);
		 $cities_options->getSelect()
         ->order('id DESC');
		if($cities_options->count() > 0){
			foreach($cities_options as $city){
				$cities[] = ucfirst($city->getCity());
			}
		}
		echo json_encode($cities);
		die();
    }
}