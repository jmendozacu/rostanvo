<?php

class Magestore_Customerreward_Adminhtml_OfferController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customerreward/offer')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Offers Manager'), Mage::helper('adminhtml')->__('Offer Manager'));
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Customer reward'))
			->_title($this->__('Manage offer'));
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('customerreward/offer')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			
			$model->getConditions()->setJsFormObject('offer_conditions_fieldset');
			Mage::register('offer_data', $model);
			
			$this->_title($this->__('Customer reward'))
				->_title($this->__('Manage offer'));
			if ($model->getId()){
				$this->_title($model->getTitle());
			}else{
				$this->_title($this->__('New offer'));
			}

			$this->loadLayout();
			$this->_setActiveMenu('customerreward/offer');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Offer Manager'), Mage::helper('adminhtml')->__('Offer Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Offer News'), Mage::helper('adminhtml')->__('Offer News'));

			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true);
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true)
				->addItem('js','tiny_mce/tiny_mce.js')
				->addItem('js','mage/adminhtml/wysiwyg/tiny_mce/setup.js')
				->addJs('mage/adminhtml/browser.js')
				->addJs('prototype/window.js')
				->addJs('lib/flex.js')
				->addJs('mage/adminhtml/flexuploader.js');

			$this->_addContent($this->getLayout()->createBlock('customerreward/adminhtml_offer_edit'))
				->_addLeft($this->getLayout()->createBlock('customerreward/adminhtml_offer_edit_tabs'));

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
			$model = Mage::getModel('customerreward/offer')->load($this->getRequest()->getParam('id'));
			//image
			$suffix_path = 'customerreward'.DS.'offer'.DS;
			if ($data['image']['delete'] == 1){
				//unlink(Mage::getBaseDir('media').DS.$data['image']['value']);
				$data['image']='';
			}elseif(is_array($data['image'])){
				$data['image']=$data['image']['value'];
			}
			$path = Mage::getBaseDir('media').DS.$suffix_path;
			if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != ''){
				try{
					$uploader = new Varien_File_Uploader('image');
					$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
					$uploader->save($path,$_FILES['image']['name']);
				}catch(Exception $e){
					Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				}
				$data['image'] = str_replace(DS,'/',$suffix_path.$_FILES['image']['name']);
			}
			//prepare data
			$data = $this->_filterDates($data, array('from_date', 'to_date'));
			if (!$data['from_date']) $data['from_date'] = null;
			if (!$data['to_date']) $data['to_date'] = null;
			$data['conditions'] = $data['rule']['conditions'];
			unset($data['rule']);
			//date and categories
			$model->loadPost($data)->setData('from_date',$data['from_date'])->setData('to_date',$data['to_date']);
			if (isset($data['category_ids'])) $model->setData('categories',$data['category_ids']);
			//product
			if(isset($data['offer_product'])){
				$productIds = array();
				parse_str($data['offer_product'], $productIds);
				$productIds = array_keys($productIds);
				$products = implode(',',$productIds);
				$model->setData('products',$products);
			}
			try {
				$model->setId($this->getRequest()->getParam('id'));
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerreward')->__('Offer was successfully saved'));
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
				$model = Mage::getModel('customerreward/offer');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Offer was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $offerIds = $this->getRequest()->getParam('offer');
        if(!is_array($offerIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($offerIds as $offerId) {
                    $offer = Mage::getModel('customerreward/offer')->load($offerId);
                    $offer->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($offerIds)
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
        $offerIds = $this->getRequest()->getParam('offer');
        if(!is_array($offerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($offerIds as $offerId) {
                    $offer = Mage::getSingleton('customerreward/offer')
                        ->load($offerId)
                        ->setIsActive($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($offerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function categoriesAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('customerreward/adminhtml_offer_edit_tab_category')->toHtml()
        );
    }	
	
    public function categoriesJsonAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('customerreward/adminhtml_offer_edit_tab_category')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }
    
    public function productsAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('program.edit.tab.product')
            ->setProducts($this->getRequest()->getPost('oproduct', null));
        $this->renderLayout();
	}
	
	public function productsGridAction()
	{
		$this->loadLayout();
		$this->getLayout()->getBlock('program.edit.tab.product')
            ->setProducts($this->getRequest()->getPost('oproduct', null));
        $this->renderLayout();
	}
}