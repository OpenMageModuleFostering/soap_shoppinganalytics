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
 * @package     Mage_ShoppingAnalytics
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ShoppingAnalytics data helper
 *
 * @category   SOAP
 * @package    SOAP_ShoppingAnalytics
 */   
class SOAP_ShoppingAnalytics_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_SHOPPING_ACTIVE  = 'shopping/analytics/active';
    const XML_PATH_SHOPPING_ACCOUNT = 'shopping/analytics/account';
    
    const XML_PATH_TRACKING_ACTIVE  = 'shopping/tracking/active';
    const XML_PATH_TRACKING_ACCOUNT = 'shopping/tracking/account';
    
    const XML_PATH_MSTRACKING_ACTIVE  = 'shopping/mstracking/active';
    const XML_PATH_MSTRACKING_ACCOUNT = 'shopping/mstracking/domain';
    
    const XML_PATH_ASTRACKING_ACTIVE  = 'shopping/astracking/active';
    const XML_PATH_ASTRACKING_ACCOUNT  = 'shopping/astracking/account';

    public function isShoppingAnalyticsAvailable($store = null)
    {
        $accountId = Mage::getStoreConfig(self::XML_PATH_SHOPPING_ACCOUNT, $store);
        return $accountId && Mage::getStoreConfigFlag(self::XML_PATH_SHOPPING_ACTIVE, $store);
    }
    
    public function isGoogleTrackingAvailable($store = null)
    {
        $accountId = Mage::getStoreConfig(self::XML_PATH_TRACKING_ACCOUNT, $store);
        return $accountId && Mage::getStoreConfigFlag(self::XML_PATH_TRACKING_ACTIVE, $store);
    }
    
    public function isMicrosoftTrackingAvailable($store = null)
    {
        $accountId = Mage::getStoreConfig(self::XML_PATH_MSTRACKING_ACCOUNT, $store);
        return $accountId && Mage::getStoreConfigFlag(self::XML_PATH_MSTRACKING_ACTIVE, $store);
    }
    
    public function isAddShoppersTrackingAvailable($store = null)
    {
        $accountId = Mage::getStoreConfig(self::XML_PATH_ASTRACKING_ACCOUNT, $store);
        return $accountId && Mage::getStoreConfigFlag(self::XML_PATH_ASTRACKING_ACTIVE, $store);
    }
}
