<?php

require_once(MAX_PATH . '/lib/OA/Admin/Menu/IChecker.php');

class Plugins_admin_oxMarkedTextAdvertiser_oxMarkedTextAdvertiserEntityChecker 
    implements OA_Admin_Menu_IChecker
{

    public function check($oSection) 
    {
        phpAds_registerGlobal('clientid', 'campaignid');
        global $clientid, $campaignid;
        
        $sectionId = $oSection->getId();
        
        static $cache = array();
        
        $oMarkedTextAdvertiserComponent = OX_Component::factory('admin', 'oxMarkedTextAdvertiser');
        $oEntityHelper = $oMarkedTextAdvertiserComponent->getEntityHelper();
        
        $enabled = true;
        switch($sectionId) {
            case 'advertiser-edit':
            case 'advertiser-trackers':
            case 'advertiser-access':
            case 'campaign-edit_new':
            case 'campaign-edit':
            case 'campaign-trackers': 
            case 'campaign-banners':
            {
                if (isset($cache[$clientid])) {
                    return $cache[$clientid];
                }        

                break;
            }
            
            case 'banner-edit':
            {
                if (isset($cache[$clientid])) {
                    return $cache[$clientid];
                }        

                break;
            }
            
            case 'banner-acl':
            case 'banner-zone':
            case 'banner-advanced': 
            case 'campaign-zone':
            
        }

        $sessionClientId = $this->getSessionClientId();
        if (isset($sessionClientId) ) {
            $this->clearMarketEntitiesInSession();
        }
        
        
        return $enabled;
    }
    
    
    
    
    private function getSessionClientId()
    {
        return $session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid'];         
    }

    private function clearEntitiesInSession()
    {
        global $session;
        
        $clientid = $session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid'];
        unset($session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid']);
        if ($clientid) {
            unset($session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['campaignid'][$clientid]);
        }
        phpAds_SessionDataStore();
    }
    
}

?>
