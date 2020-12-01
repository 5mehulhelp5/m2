<?php
 
namespace Custom\City\Controller\Adminhtml\State;
 
use Custom\City\Controller\Adminhtml\State;
 
class Save extends State
{
	
	private function activeLangs(){
		 /** @var \Magento\Framework\App\ObjectManager $obj */
		$obj = \Magento\Framework\App\ObjectManager::getInstance();

		/** @var \Magento\Store\Model\StoreManagerInterface|\Magento\Store\Model\StoreManager $storeManager */
		$storeManager = $obj->get('Magento\Store\Model\StoreManagerInterface');
		$stores = $storeManager->getStores($withDefault = false);

		//Get scope config
		/** @var \Magento\Framework\App\Config\ScopeConfigInterface|\Magento\Framework\App\Config $scopeConfig */
		$scopeConfig = $obj->get('Magento\Framework\App\Config\ScopeConfigInterface');

		//Locale code
		$locale = [];

		//Try to get list of locale for all stores;
		foreach($stores as $store) {
			$locale[] = $scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getStoreId());
		}
		
		return $locale;
	}
   /**
     * @return void
     */
   public function execute()
   {
      $isPost = $this->getRequest()->getPost();
	  if ($isPost) {
		  if($isPost['id']==""){
                $collection = $this->_stateFactory->create()->getCollection()
				->addFieldToFilter('default_name',$isPost['default_name'])->addFieldToFilter('country_id',$isPost['country_id']);
				if($collection->count() > 0){
                    $state = $isPost['default_name'];
                    $this->messageManager->addError('State "'.$state.'" already exists.');
                    $this->_session->setFormData($data);
                    if ($isPost['id'] > 0) {
                        $this->_redirect('*/*/edit', array('id' => $isPost['id']));
                    }else{
                        $this->_redirect('*/*/new');
                    }
                    return;
                }
            }

         $stateModel = $this->_stateFactory->create();
         $stateId = $isPost['id'];
 
         if ($stateId) {
            $stateModel->load($stateId);
         }
         $formData = $this->getRequest()->getParam('state');
		 if(isset($formData['id'])){
			 $formData['region_id'] = $formData['id'];
		 }
		 $stateModel->setData($formData);
         
         try {
            // Save state
            $stateModel->save();
			$last_id = $stateModel->getId();
			$langs = $this->activeLangs();
			if(count($langs) > 0){
					foreach($langs as $lang){
						$Statelocale = $this->_objectManager->create('Custom\City\Model\Statelocale')->getCollection()
						->addFieldToFilter('region_id',$last_id)->addFieldToFilter('locale',$lang);
						$name = $stateModel->getDefaultName();
						if($Statelocale->count()==0){
							$lang_data = array('locale'=>$lang,'region_id'=>$last_id,'name'=>$name);
							$this->_objectManager->create('Custom\City\Model\Statelocale')->setData($lang_data)->save();
						}else{
							$connection = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection');
							$conn = $connection->getConnection();
							$conn->rawQuery("UPDATE directory_country_region_name set name = '".$name."' where region_id=".$last_id);
						}
					}
			}
            // Display success message
            $this->messageManager->addSuccess(__('The state has been saved.'));
 
            // Check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
               $this->_redirect('*/*/edit', ['id' => $stateModel->getId(), '_current' => true]);
               return;
            }
 
            // Go to grid page
            $this->_redirect('*/*/');
            return;
         } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
         }
 
         $this->_getSession()->setFormData($formData);
         $this->_redirect('*/*/edit', ['id' => $stateId]);
      }
   }
}