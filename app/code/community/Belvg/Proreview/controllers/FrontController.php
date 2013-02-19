<?php
/**
 * BelVG LLC.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 *
 /***************************************
 *         MAGENTO EDITION USAGE NOTICE *
 *****************************************/
 /* This package designed for Magento COMMUNITY edition
 * BelVG does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BelVG does not provide extension support in case of
 * incorrect edition usage.
 /***************************************
 *         DISCLAIMER   *
 *****************************************/
 /* Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future.
 *****************************************************
 * @category   Belvg
 * @package    Belvg_Proreview
 * @copyright  Copyright (c) 2010 - 2011 BelVG LLC. (http://www.belvg.com)
 * @license    http://store.belvg.com/BelVG-LICENSE-COMMUNITY.txt
 */
?>
<?php
class Belvg_Proreview_FrontController extends Mage_Core_Controller_Front_Action
{

    protected function _initAction() {
       $this->loadLayout();        
       return $this;
    }

    public function indexAction(){
      
    }
    public function addAction(){
       if (!$this->getRequest()->getParam('name')) $this->_redirect('/');
       $post = $this->getRequest()->getParams();
       Mage::getModel('faq/state')->add($post);
       $this->_redirect('faq/');
      // exit;
    }

    public function delAction(){

       if (!$this->getRequest()->getParam('id')) $this->_redirect('/');
       $id = $this->getRequest()->getParam('id');
       Mage::getModel('faq/state')->del($id);
       $this->_redirect('*/*/');
      // exit;
    }    
    public function chpageAction(){
       if (!$this->getRequest()->getParam('product_id')){ echo 'Product is undefined'; exit; }
       $_product = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('product_id'));
       $reviewCollectin = Mage::getModel('review/review')->getCollection()->addStatusFilter('approved')
            ->addEntityFilter('product', $_product->getId());
       $rcount = $reviewCollectin->count()/10;
       $reviewCollectin = Mage::getModel('review/review')->getCollection();
            $reviewCollectin->setPagesize(10)
            ->setCurpage($this->getRequest()->getParam('page'))
            ->addStatusFilter('approved')
            ->addEntityFilter('product', $_product->getId())
            ->setDateOrder()
            ->addRateVotes();
       $ratings = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->load()
            ->addOptionToItems();
       $result = '';
       foreach ($reviewCollectin->getItems() as $_review){
          $result.= '<div class="Reviews-box">
              <div class="ratings">
                <div class="rating-box">
                      ';
                $_votes = $_review->getRatingVotes();
                if (count($_votes)){
                         foreach ($_votes as $_vote){
                                    $result.='<div class="rating" style="width:'.$_vote->getPercent().'%;"></div>';
                         }
                }

                  $result.='</div><!--rating-box-->
                  <span class="drp"> '.date("M d,Y",strtotime($_review->getCreatedAt())).' by '.$_review->getNickname().'<br /> <b>'.$_review->getTitle().'</b> </span>
                      </div><!--ratings-->
                <div class="rev">
                <p>
                  '.nl2br($_review->getDetail()).'
            <a href="'.Mage::getBaseUrl().'review/product/view/id/'.$_review->getId().'/"> read more</a>
                </p>
            </div><!--rev-->
              </div>';
             
        }
        $result.='Page:';
             for ($i=1;$i<=ceil($rcount);$i++){
                    $result.='<a ';
                    if ($this->getRequest()->getParam('page') == $i) $result.=' class="current" ';
                     $result.='href="javascript:void(0);" onclick="changePage('.$i.')">'.$i.'</a>';
             }
        echo $result;
    }

}
?>