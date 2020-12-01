<?php
 
namespace Custom\City\Controller\Adminhtml\City;
 
use Custom\City\Controller\Adminhtml\City;
 
class Save extends City
{
   /**
     * @return void
     */
   public function execute()
   {
      $isPost = $this->getRequest()->getPost();
 
      if ($isPost) {
         $cityModel = $this->_cityFactory->create();
         $cityId = $this->getRequest()->getParam('id');
 
         if ($cityId) {
            $cityModel->load($cityId);
         }
         $formData = $this->getRequest()->getParam('city');
		 $cityModel->setData($formData);
         
         try {
            // Save city
            $cityModel->save();
 
            // Display success message
            $this->messageManager->addSuccess(__('The city has been saved.'));
 
            // Check if 'Save and Continue'
            if ($this->getRequest()->getParam('back')) {
               $this->_redirect('*/*/edit', ['id' => $cityModel->getId(), '_current' => true]);
               return;
            }
 
            // Go to grid page
            $this->_redirect('*/*/');
            return;
         } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
         }
 
         $this->_getSession()->setFormData($formData);
         $this->_redirect('*/*/edit', ['id' => $cityId]);
      }
   }
}