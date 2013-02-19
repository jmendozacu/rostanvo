<?php

class Magestore_Storepickup_Adminhtml_ImportController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() 
	{
		$this->loadLayout()
			->_setActiveMenu('storepickup/stores')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Stores'), Mage::helper('adminhtml')->__('Import Stores'));
		
		return $this;
	}   
 
	public function importstoreAction() 
	{	
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){return;}
		$this->loadLayout();
		$this->_setActiveMenu('storepickup/stores');

		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Stores'), Mage::helper('adminhtml')->__('Import Stores'));
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Import Stores'), Mage::helper('adminhtml')->__('Import Stores'));

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		
		$editBlock = $this->getLayout()->createBlock('storepickup/adminhtml_store_edit');
		$editBlock->removeButton('delete');
		$editBlock->removeButton('saveandcontinue');
		$editBlock->removeButton('reset');
		$editBlock->updateButton('back','onclick','backEdit()');
		$editBlock->setData('form_action_url',$this->getUrl('*/*/save',array()));
		
		$this->_addContent($editBlock)
			->_addLeft($this->getLayout()->createBlock('storepickup/adminhtml_store_import_tabs'));

		$this->renderLayout();
	}
	
	public function saveAction()
	{
		if(!isset($_FILES['csv_store']))
		{
			Mage::getSingleton('core/session')->addError('Not selected file!');
			$this->_redirect('*/*/importstore');
			return;
		}
		
		$oFile = new Varien_File_Csv();

		$data = $oFile->getData($_FILES['csv_store']['tmp_name']);
		
		$store = Mage::getModel('storepickup/store');
		$storeData = array();
		try{
			$total = 0;
			foreach($data as $col=>$row)
			{
				if($col == 0)
				{
					$index_row = $row;
				} else {
					
					for($i=0;$i<count($row);$i++)
					{
						$storeData[$index_row[$i]] = $row[$i];
					}
					$store->setData($storeData);
					$store->setId(null);
					if($store->import())
						$total++;
				}
			}
			
			$this->_redirect('*/adminhtml_store/index');
			if($total != 0)
				Mage::getSingleton('core/session')->addSuccess('Imported successful total '.$total.' stores' );
			else 
				Mage::getSingleton('core/session')->addSuccess('Stores exist' );
		}catch(Exception $e){
			Mage::getSingleton('core/session')->addError($e->getMessage());
			$this->_redirect('*/*/importstore');
		}
	}

}