<?php

require_once LIB_PATH . '/Plugin/Component.php';
require_once LIB_PATH . '/Plugin/PluginManager.php';

require_once LIB_PATH . '/Admin/Redirect.php';

require_once MAX_PATH . '/lib/OA/Admin/TemplatePlugin.php';
require_once MAX_PATH . '/lib/OA/Admin/UI/NotificationManager.php';
require_once MAX_PATH . '/lib/OA.php';
require_once MAX_PATH . '/lib/OX/Admin/UI/Hooks.php';

define('OX_MARKEDTEXTADVERTISER_LIB_PATH', dirname(__FILE__) . '/lib');
require_once OX_MARKEDTEXTADVERTISER_LIB_PATH . '/OX/oxMarkedTextAdvertiser/UI/EntityFormManager.php';
require_once OX_MARKEDTEXTADVERTISER_LIB_PATH . '/OX/oxMarkedTextAdvertiser/UI/EntityHelper.php';
require_once OX_MARKEDTEXTADVERTISER_LIB_PATH . '/OX/oxMarkedTextAdvertiser/UI/EntityScreenManager.php';

class Plugins_admin_oxMarkedTextAdvertiser_oxMarkedTextAdvertiser extends OX_Component
{

    private $oEntityFormManager;

    private $oEntityScreenManager;        
    

    public function __construct()
    {

        $this->oFormManager = new OX_oxMarkedTextAdvertiser_UI_EntityFormManager($this);
        //$this->preferenceDal = new OX_oxMarkedTextAdvertiser_Dal_PreferenceVariable($this);
    }
    

    public function afterLogin()
    {

        //OX_oxMarkedTextAdvertiser_UI_CampaignsSettings::removeSessionCookies($this->getCookiePath());
      
        return;
    }    
    
    
    public function onEnable()
    {
 
        return true; // we allow to enable plugin
    }

    public function onDisable()
    {

        return true;
    }
    
    
    /**
     * RegisterUiHooks plugin hook
     */
    public function registerUiListeners()
    {
        $oViewListener = $this->getViewListener();
        
        OX_Admin_UI_Hooks::registerBeforePageHeaderListener(
            array($oViewListener,
                'beforePageHeader'
            ));
            
        OX_Admin_UI_Hooks::registerBeforePageContentListener(
            array($oViewListener,
                'beforeContent'
            ));
            
        OX_Admin_UI_Hooks::registerAfterPageContentListener(
            array($oViewListener,
                'afterContent'
            ));
    }
    

    public function hasAccessToObject($entityTable, $entityId, $operationAccessType, $accountId, $accountType)
    {
            
        $hasAccess = $this->getEntityHelper()->hasAccessToObject($entityTable, $entityId, $operationAccessType, $accountId, $accountType);
            
        return $hasAccess;
    }
    
    
    public function getViewListener() 
    {
        if (empty($this->oEntityScreenManager)) {
            $this->oEntityScreenManager = new OX_oxMarkedTextAdvertiser_UI_EntityScreenManager($this);
        }
            
        return $this->oEntityScreenManager;    
    }
    


    /**
     * @return OX_oxMarkedTextAdvertiser_UI_EntityHelper
     */
    public function getEntityHelper()
    {
        if (empty($this->entityHelper)) {
            $this->entityHelper = new OX_oxMarkedTextAdvertiser_UI_EntityHelper($this);
        }
            
        return $this->entityHelper;        
    }
    

    function getAgencyDetails($agencyId = null)
    {
        if (is_null($agencyId)) {
           $agencyId = OA_Permission::getAgencyId();
        }
        $doAgency = & OA_Dal::factoryDO('agency');
        $doAgency->get($agencyId);
        $aResult = $doAgency->toArray();

        return $aResult;
    }


    function getConfigValue($configKey)
    {
        return $GLOBALS['_MAX']['CONF']['oxMarkedTextAdvertiser'][$configKey];
    }

    //UI actions
    function indexAction()
    {

        echo 'Im here';

    }
    

    public function getCookiePath()
    {
        require_once MAX_PATH .'/lib/Max.php';
        return parse_url(MAX::constructUrl(MAX_URL_ADMIN), PHP_URL_PATH);
    }

}

?>
