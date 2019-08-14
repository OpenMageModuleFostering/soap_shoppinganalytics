<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mage
 * @package     SOAP_ShoppingAnalytics
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ShoppingAnalytics Page Block
 *
 * @category   Mage
 * @package    SOAP_ShoppingAnalytics
 * @author     SOAP Media
 */
class SOAP_ShoppingAnalytics_Block_Sa extends Mage_Core_Block_Template
{
    public function getPageName()
    {
        return $this->_getData('page_name');
    }

    protected function _getPageTrackingCode($accountId)
    {
        return "
            _roi.push(['_setMerchantId', '{$this->jsQuoteEscape($accountId)}']);
            ";
    }

    protected function _getOrdersTrackingCode($accountId = 0)
    {
        $categoryModel = Mage::getModel('catalog/category');
        
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $result = array();
        foreach ($collection as $order) {
            if ($order->getIsVirtual()) {
                $address = $order->getBillingAddress();
            } else {
                $address = $order->getShippingAddress();
            }
            $result[] = sprintf("_roi.push(['_setMerchantId', '%s']);",
                $this->jsQuoteEscape($accountId)
                );
            $result[] = sprintf("_roi.push(['_setOrderId', '%s']);",
                $order->getIncrementId()
                );
            $result[] = sprintf("_roi.push(['_setOrderAmount', '%s']);",
                $order->getBaseGrandTotal()
                );
            $result[] = sprintf("_roi.push(['_setOrderNotes', '%s']);",
                'Notes'
                );
                
            foreach ($order->getAllVisibleItems() as $item) {
                //Cat IDs                
                $catIDs = $item->getProduct()->getCategoryIds();
                $category = $categoryModel->load($catIDs[0]);
                $catName = $category->getName();

                $result[] = sprintf("_roi.push(['_addItem', '%s', '%s', '%s', '%s', '%s', '%s']);",
                    $this->jsQuoteEscape($item->getSku()),
                    $this->jsQuoteEscape($item->getName()), 
                    $catIDs[0] ? $catIDs[0] : '',
                    $catName ? $catName : '',
                    $item->getBasePrice(), 
                    $item->getQtyOrdered()
                );
            }
            $result[] = "_roi.push(['_trackTrans']);";
        }
        return implode("\n", $result);
    }

    protected function _addGoogleTrackingCode()
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        
        foreach ($collection as $order)
        {
            $value = $order->getBaseGrandTotal();
            if (!Mage::helper('shoppinganalytics')->isGoogleTrackingAvailable())
            {
                return '';
            }
            else {
                $id         = Mage::getStoreConfig('shopping/tracking/account');
                $format   = Mage::getStoreConfig('shopping/tracking/format');
                $language   = Mage::getStoreConfig('shopping/tracking/language');
                $colour     = Mage::getStoreConfig('shopping/tracking/colour');
                $label      = Mage::getStoreConfig('shopping/tracking/label');
                
                if ($value > 0)
                {
                    $totalValue = 'var google_conversion_value = "'.$value.'"';
                }
                else {
                    $totalValue = '';
                }

                return '<!-- Google Tracking Code -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = '.$id.';
var google_conversion_language = "'.$language.'";
var google_conversion_format = "'.$format.'";
var google_conversion_color = "'.$colour.'";
var google_conversion_label = "'.$label.'";
'.$totalValue.'

/* ]]> */ 
</script>
<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js"></script>

<noscript>
    <img height=1 width=1 border=0
    src="http://www.googleadservices.com/pagead/
    conversion/'.$id.'/?value='.$value.'
    &label='.$label.'&script=0">
</noscript>
<!-- END Google Tracking Code -->';
            }
        }
    }

    protected function _toHtml()
    {
        if (!Mage::helper('shoppinganalytics')->isShoppingAnalyticsAvailable() && !Mage::helper('shoppinganalytics')->isGoogleTrackingAvailable()) {
            return '';
        }
        elseif (Mage::helper('shoppinganalytics')->isShoppingAnalyticsAvailable())
        {
            return parent::_toHtml();
        }
        elseif (Mage::helper('shoppinganalytics')->isGoogleTrackingAvailable()) {
            return $this->_addGoogleTrackingCode();
        }
    }
}



