<?php

require_once MAX_PATH .'/lib/max/Dal/DataObjects/Campaigns.php';

class OX_oxMarkedTextAdvertiser_UI_EntityHelper
{
    public static $MARKET_CAMPAIGN_REMNANT = 'remnant';
    public static $MARKET_CAMPAIGN_CONTRACT = 'contract';
    
    /**
     * @var Plugins_admin_oxMarkedTextAdvertiser_oxMarkedTextAdvertiser
     */
    private $markedComponent;
    
    public function __construct(Plugins_admin_oxMarkedTextAdvertiser_oxMarkedTextAdvertiser $markedComponent = null)
    {
        $this->oMarkedTextAdvertiserComponent = $markedComponent;
    }

    public function hasAccessToObject($entityTable, $entityId, 
                        $operationAccessType, $accountId, $accountType)
    {
        if (empty($entityId)) {
             return NULL;
        }
        
        $hasAccess = null;    
        switch ($entityTable) {
            case 'clients': {

                switch ($operationAccessType) {
                    case OA_Permission::OPERATION_VIEW : {
                        $hasAccess = true;
                        break;
                    }
                    default: {
                        $hasAccess = false;     
                    }
                }
                break;
            }
            
            case 'campaigns': {
                
                switch ($operationAccessType) {
                    case OA_Permission::OPERATION_MOVE :
                    case OA_Permission::OPERATION_ADD_CHILD:     
                    case OA_Permission::OPERATION_VIEW_CHILDREN: {
                        $hasAccess = false;
                        break;
                    }
                    
                    
                    default: {
                        $hasAccess = true;
                    }
                }
                break;
            }
            
            case 'banners' : {

                $hasAccess = true;
                break;
            }            
        }
        
        /*OA::debug("Access check: ". $entityTable . ":" . $entityId 
            . "@" .  $operationAccessType . " AC:" . $accountId . "/" 
            . $accountType.' = '.($hasAccess === null ? 'null' : $hasAccess));*/
        
        return $hasAccess;
    }
    
    
    protected function getEntity($entityTable, $entityId)
    {
        $do = OA_Dal::factoryDO($entityTable);
        $aEntity = null;
                
        if ($do->get($entityId)) {
            $aEntity = $do->toArray();
        }
        
        return $aEntity;
    }
    
    
}
