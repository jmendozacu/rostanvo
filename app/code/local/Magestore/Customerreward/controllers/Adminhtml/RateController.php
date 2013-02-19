<?php

class Magestore_Customerreward_Adminhtml_RateController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customerreward/rate')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Rates Manager'), Mage::helper('adminhtml')->__('Rate Manager'));
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Customer reward'))
			->_title($this->__('Manage rate'));
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('customerreward/rate')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('rate_data', $model);
			
			$this->_title($this->__('Customer reward'))
				->_title($this->__('Manage rate'));
			if ($model->getId()){
				$this->_title($model->getId());
			}else{
				$this->_title($this->__('New rate'));
			}

			$this->loadLayout();
			$this->_setActiveMenu('customerreward/rate');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rate Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Rate News'), Mage::helper('adminhtml')->__('Rate News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customerreward/adminhtml_rate_edit'))
				->_addLeft($this->getLayout()->createBlock('customerreward/adminhtml_rate_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreward')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()){
			$model = Mage::getModel('customerreward/rate');
			if (is_array($data['website_ids'])) $data['website_ids'] = implode(',',$data['website_ids']);
			if (is_array($data['customer_group_ids'])) $data['customer_group_ids'] = implode(',',$data['customer_group_ids']);
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerreward')->__('Rate was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreward')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('customerreward/rate');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Rate was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
}