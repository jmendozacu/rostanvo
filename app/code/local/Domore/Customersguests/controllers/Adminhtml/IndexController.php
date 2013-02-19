<?php


class Domore_Customersguests_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
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
        $fileName   = 'customers-guests.csv';
        $content    = $this->getLayout()->createBlock('customersguests/map_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'customers-guests.xml';
        $content    = $this->getLayout()->createBlock('customersguests/map_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

}
