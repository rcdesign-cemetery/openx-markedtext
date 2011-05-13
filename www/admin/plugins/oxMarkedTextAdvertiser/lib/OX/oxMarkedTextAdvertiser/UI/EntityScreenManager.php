<?php

class OX_oxMarkedTextAdvertiser_UI_EntityScreenManager
{
    private $oMarkedTextAdvertiserComponent;
    
    public function  __construct( Plugins_admin_oxMarkedTextAdvertiser_oxMarkedTextAdvertiser $oMarkedTextAdvertiserComponent )
    {
        $this->oMarkedTextAdvertiserComponent = $oMarkedTextAdvertiserComponent;
    }

    public function beforePageHeader(OX_Admin_UI_Event_EventContext $oEventContext)
    {
        $pageId = $oEventContext->data['pageId'];
        $pageData = $oEventContext->data['pageData'];
        $oHeaderModel = $oEventContext->data['headerModel'];
        $agencyId = $pageData['agencyid'];
        $campaignId = $pageData['campaignid'];
        $advertiserId = $pageData['clientid'];
        $oEntityHelper = $this->oMarkedTextAdvertiserComponent->getEntityHelper();
              
        if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) 
        {
            switch($pageId) {  
    
                case 'campaign-banners' : 
                {

                    $oDalZones = OA_Dal::factoryDAL('zones');
                    $linkedWebsites = $oDalZones->getWebsitesAndZonesListByCategory($agencyId, null, $campaignId, true);
                    $arraylinkedWebsitesKeys = array_keys( $linkedWebsites );
                    $linkedWebsitesKey = $arraylinkedWebsitesKeys[0];
                    $arraylinkedZonesKeys = array_keys( $linkedWebsites[$linkedWebsitesKey]['zones'] );
                    $zoneId = $arraylinkedZonesKeys[0];

                    $aZone = Admin_DA::getZone($zoneId);

                    if ( $aZone['type'] == 3 ) 
                    {
                        if ( OA_Permission::hasAccessToObject('clients', $clientid) && OA_Permission::hasAccessToObject('campaigns', $campaignid) )
                        {
                            OX_Admin_Redirect::redirect('plugins/' . $this->oMarkedTextAdvertiserComponent->group . "/oxMarkedTextAdvertiser-index.php?campaignid=$campaignId&clientid=$advertiserId" );
                        }
                    }

                break;

                }
            }        
        } 
    }
}
