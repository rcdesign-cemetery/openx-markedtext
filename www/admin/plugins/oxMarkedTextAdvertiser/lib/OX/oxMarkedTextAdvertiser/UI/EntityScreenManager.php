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
        $oEntityHelper = $this->oMarkedTextAdvertiserComponent->getEntityHelper();
        //$oUI = OA_Admin_UI::getInstance();
              
        if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) 
        {
            switch($pageId) {  
    
                case 'campaign-banners' : {

                    if ( OA_Permission::hasAccessToObject('clients', $clientid) && OA_Permission::hasAccessToObject('campaigns', $campaignid) )
                    {
                        OX_Admin_Redirect::redirect('plugins/' . $this->oMarkedTextAdvertiserComponent->group . '/oxMarkedTextAdvertiser-index.php' );

                     }

                break;
                }

            }        
        } 
        else
        {
			
			return null;
			
		}
    }

}
