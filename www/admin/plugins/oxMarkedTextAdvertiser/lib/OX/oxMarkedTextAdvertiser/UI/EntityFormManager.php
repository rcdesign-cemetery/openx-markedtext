<?php

require_once MAX_PATH . '/lib/OA/Admin/UI/component/Form.php';

class OX_oxMarkedTextAdvertiser_UI_EntityFormManager
{

    private $oMarkedTextAdvertiserComponent;

    public function __construct(Plugins_admin_oxMarkedTextAdvertiser_oxMarkedTextAdvertiser $markedComponent = null)
    {
        $this->oMarkedTextAdvertiserComponent = $markedComponent;
    }
    
    
    public function buildCampaignFormPart(&$form, $aCampaign, $isNewCampaign)
    {
        if (!$this->oMarkedTextAdvertiserComponent->getEntityHelper()->isMarketAdvertiser($aCampaign['clientid'])) {
            $this->buildCampaignOptInPart($form, $aCampaign, $isNewCampaign);
        }
    }
    
       
    public function processCampaignForm(&$aFields)
    {
        $aConf = $GLOBALS['_MAX']['CONF'];

        $oExt_market_campaign_pref = OA_Dal::factoryDO('ext_market_campaign_pref');
        $oExt_market_campaign_pref->updateCampaignStatus($aFields['campaignid'], 
            $aFields['mkt_is_enabled'] == 't', $aFields['floor_price']);
            
        // invalidate campaign-market delivery cache
        if (!function_exists('OX_cacheInvalidateGetCampaignMarketInfo')) {
            require_once MAX_PATH . $aConf['pluginPaths']['plugins'] . 'deliveryAdRender/oxMarkedTextAdvertiserDelivery/oxMarkedTextAdvertiserDelivery.delivery.php';
        }
        OX_cacheInvalidateGetCampaignMarketInfo($aFields['campaignid']);
    }    
    
    
}
