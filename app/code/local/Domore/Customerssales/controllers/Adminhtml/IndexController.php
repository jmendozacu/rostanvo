<?php

class Domore_Customerssales_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	
	protected function _initAction()
	{
		$this->loadLayout();
		return $this;
	}
	
	public function indexAction()
	{
		$this->_initAction();
		$this->renderLayout();
	
	}	
	
    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'customers-sales.csv';
        $content    = $this->getLayout()->createBlock('customerssales/map_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'customers-sales.xml';
        $content    = $this->getLayout()->createBlock('customerssales/map_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
