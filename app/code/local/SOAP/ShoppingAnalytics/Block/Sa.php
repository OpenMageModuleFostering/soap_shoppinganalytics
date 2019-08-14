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

                return '
<!-- Google Tracking Code -->
<script type="text/javascript">
/* <![CDATA[ */
var google_conversion_id = "'.$id.'";
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
<!-- END Google Tracking Code -->
                    ';
            }
        }
    }

    protected function _addMicrosoftTrackingCode() {
        $domainid = Mage::getStoreConfig('shopping/mstracking/domain');
        $cp       = Mage::getStoreConfig('shopping/mstracking/cp');
        
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
            if (!Mage::helper('shoppinganalytics')->isMicrosoftTrackingAvailable())
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

                return '
                
<!-- Microsoft adCenter Tracking -->
<SCRIPT>
    microsoft_adcenterconversion_domainid = '.$domainid.';
    microsoft_adcenterconversion_cp = 5050; 
    microsoft_adcenterconversionparams = new Array();
    microsoft_adcenterconversionparams[0] = "dedup=1";
</SCRIPT>
<SCRIPT SRC="https://0.r.msn.com/scripts/microsoft_adcenterconversion.js"></SCRIPT>
<NOSCRIPT>
    <IMG width=1 height=1 SRC="https://'.$domainid.'.r.msn.com/?type=1&cp=1&dedup=1"/>
</NOSCRIPT>
<a href="http://advertising.msn.com/MSNadCenter/LearningCenter/adtracking.asp" target="_blank"></a>
<!-- END Microsoft adCenter Tracking -->
                    
                ';
            }
        }
    }

    protected function _addAddShoppersTrackingCode() {        
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds))
        ;
        $accountId = Mage::getStoreConfig(SOAP_ShoppingAnalytics_Helper_Data::XML_PATH_ASTRACKING_ACCOUNT);
        foreach ($collection as $order)
        {
            $value = $order->getBaseGrandTotal();
            if (!Mage::helper('shoppinganalytics')->isAddShoppersTrackingAvailable())
            {
                return '';
            }
            else {
                                
                if ($value > 0)
                {
                    $totalValue = 'var google_conversion_value = "'.$value.'"';
                }
                else {
                    $totalValue = '';
                }

                        return '
<!-- AddShoppers ROI Tracking -->
<script type="text/javascript">
AddShoppersConversion = {
        order_id: "'.$order->getIncrementId().'",
        value: "'.$value.'"
};
var js = document.createElement("script"); js.type = "text/javascript"; js.async = true; js.id = "AddShoppers";
js.src = ("https:" == document.location.protocol ? "https://shop.pe/widget/" : "http://cdn.shop.pe/widget/") + "widget_async.js#'.$accountId.'";
document.getElementsByTagName("head")[0].appendChild(js);
</script>
<!-- END AddShoppers ROI Tracking -->
                        
                                ';
            }
        }
    }

    protected function _toHtml()
    {
        $output = '';
        
        if (Mage::helper('shoppinganalytics')->isShoppingAnalyticsAvailable())
        {
            $output .= parent::_toHtml();
        }
        elseif (Mage::helper('shoppinganalytics')->isGoogleTrackingAvailable()) {
            $output .= $this->_addGoogleTrackingCode();
        }
        
        if ((Mage::helper('shoppinganalytics')->isMicrosoftTrackingAvailable()))
        {
            $output .= $this->_addMicrosoftTrackingCode();
        }
        
        if ((Mage::helper('shoppinganalytics')->isAddShoppersTrackingAvailable()))
        {
            $output .= $this->_addAddShoppersTrackingCode();
        } 
        
        return $output;
    }
}



