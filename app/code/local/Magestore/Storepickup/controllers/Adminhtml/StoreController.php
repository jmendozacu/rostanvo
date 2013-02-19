<?php

class Magestore_Storepickup_Adminhtml_StoreController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('storepickup/stores')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Stores Manager'), Mage::helper('adminhtml')->__('Stores Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){return;}
		$this->_initAction()
			->renderLayout();
	}
	
	public function relatedordersAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('storepickup.edit.tab.relatedorders')
			->setOrders($this->getRequest()->getPost('relatedorders',null));
		$this->renderLayout();
	}

	public function relatedordersgridAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('storepickup.edit.tab.relatedorders')
			->setOrders($this->getRequest()->getPost('relatedorders',null));
		$this->renderLayout();
	}
	
	public function editAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){return;}
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('storepickup/store')->load($id);

		if ($model->getId() || $id == 0) {
		
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('store_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('storepickup/stores');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store Manager'), Mage::helper('adminhtml')->__('Store Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store News'), Mage::helper('adminhtml')->__('Store News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('storepickup/adminhtml_store_edit'))
				->_addLeft($this->getLayout()->createBlock('storepickup/adminhtml_store_edit_tabs'));

			$this->renderLayout();
		} else {
		
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storepickup')->__('Store does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
	
		$this->_forward('edit');
	}
 
	public function saveAction() {

		if ($data = $this->getRequest()->getPost()) {	
			$model = Mage::getModel('storepickup/store');
			
			//setStateValue
			if (isset($data['state_id']))
			{
				$state = Mage::getModel('directory/region')->load($data['state_id']); 
				$data['state'] = $state->getName();
			}
			
			$data['status'] = $data['store_status'];
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {								
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('storepickup')->__('Store was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('storepickup')->__('Unable to find store to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('storepickup/store');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				
				$holidays = Mage::getModel('storepickup/holiday')->getCollection()
								->addFieldToFilter('store_id',$this->getRequest()->getParam('id'));
				foreach ($holidays as $holiday) {					
					$holiday->delete();
				}	 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $storeIds = $this->getRequest()->getParam('storepickup');
	
        if(!is_array($storeIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($storeIds as $storeId) {
                    $store = Mage::getModel('storepickup/store')->load($storeId);
                    $store->delete();
					
					$holidays = Mage::getModel('storepickup/holiday')->getCollection()
								->addFieldToFilter('store_id',$storeId);
					foreach ($holidays as $holiday) {					
						$holiday->delete();
					}
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($storeIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $storepickupIds = $this->getRequest()->getParam('storepickup');
        if(!is_array($storepickupIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($storepickupIds as $storepickupId) {
                    $storepickup = Mage::getSingleton('storepickup/store')
                        ->load($storepickupId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($storepickupIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'store.csv';
        //$content    = $this->getLayout()->createBlock('storepickup/adminhtml_store_grid')
        //    ->getCsv();

		$content = Mage::getModel('storepickup/exporter')
					->exportStore();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'store.xml';
       // $content    = $this->getLayout()->createBlock('storepickup/adminhtml_store_grid')
       //     ->getXml();
			
		$content = Mage::getModel('storepickup/exporter')
					->getXmlStore();			
		
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}