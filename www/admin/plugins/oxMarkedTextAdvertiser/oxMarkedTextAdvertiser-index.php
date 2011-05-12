<?php

require_once '../../../../init.php';
require_once '../../config.php';


require_once MAX_PATH . '/lib/OA/Admin/TemplatePlugin.php';
require_once MAX_PATH . '/lib/OA/Admin/UI/component/Form.php';
require_once MAX_PATH . '/lib/OX/Admin/Redirect.php';
require_once 'oxMarkedTextAdvertiser.class.php';

phpAds_registerGlobal('hideinactive', 'listorder', 'orderdirection');

OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER, OA_ACCOUNT_ADVERTISER);

$oMarkedTextComponent = OX_Component::factory('admin', 'oxMarkedTextAdvertiser');

if (!empty($clientid) && !OA_Permission::hasAccessToObject('clients', $clientid)) 
{
    $page = basename($_SERVER['SCRIPT_NAME']);
    OX_Admin_Redirect::redirect($page);
}
if (!empty($campaignid) && !OA_Permission::hasAccessToObject('campaigns', $campaignid)) 
{
    $page = basename($_SERVER['SCRIPT_NAME']);
    OX_Admin_Redirect::redirect("$page?clientid=$clientid");
}

$aAdvertisers = getAdvertiserMap();

if (empty($clientid)) 
{
    $campaignid = null;
    if ($session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid']) 
    {
        $sessionClientId = $session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid'];
        if (isset($aAdvertisers[$sessionClientId])) 
        {
            $clientid = $sessionClientId;
        }
    }
    if (empty($clientid)) 
    {
        $ids = array_keys($aAdvertisers);
        if (!empty($ids)) 
        {
            $clientid = $ids[0];
        }
        else {
            $clientid = -1;
            $campaignid = -1;
        }
    }
}
else 
{
    if (!isset($aAdvertisers[$clientid])) 
    {
        $page = basename($_SERVER['SCRIPT_NAME']);
        OX_Admin_Redirect::redirect($page);
    }
}

if ($clientid > 0) 
{
    $aCampaigns = getCampaignMap($clientid);
    if (empty($campaignid)) 
    { 
        if ($session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['campaignid'][$clientid]) 
        {
            $sessionCampaignId = $session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['campaignid'][$clientid];
            if (isset($aCampaigns[$sessionCampaignId])) 
            { 
                $campaignid = $sessionCampaignId;
            }
        }

        if (empty($campaignid)) 
        { 
            $ids = array_keys($aCampaigns);
            $campaignid = !empty($ids) ? $ids[0] : -1; 
        }
    }
    else 
    {
        if (!isset($aCampaigns[$campaignid])) 
        {
            $page = basename($_SERVER['SCRIPT_NAME']);
            OX_Admin_Redirect::redirect("$page?clientid=$clientid");
        }
    }
}

$pageName = 'oxmarkedtext-banners';
$tabindex = 1;
$agencyId = OA_Permission::getAgencyId();
$aEntities = array('clientid' => $clientid, 'campaignid' => $campaignid);
$oTrans = new OX_Translation();

$oHeaderModel = buildHeaderModel($aEntities);
phpAds_PageHeader('advertiser-campaigns', $oHeaderModel);

if (!isset($hideinactive)) {
    if (isset($session['prefs']['campaign-banners.php'][$campaignid]['hideinactive'])) {
        $hideinactive = $session['prefs']['campaign-banners.php'][$campaignid]['hideinactive'];
    } else {
        $pref =& $GLOBALS['_MAX']['PREF'];
        $hideinactive = ($pref['ui_hide_inactive'] == true);
    }
}

if (!isset($listorder)) {
    if (isset($session['prefs']['campaign-banners.php'][$campaignid]['listorder'])) {
        $listorder = $session['prefs']['campaign-banners.php'][$campaignid]['listorder'];
    } else {
        $listorder = '';
    }
}

if (!isset($orderdirection)) {
    if (isset($session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'])) {
        $orderdirection = $session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'];
    } else {
        $orderdirection = '';
    }
}

require_once MAX_PATH . '/lib/OA/Admin/Template.php';
//$oUI = OA_Admin_UI::getInstance();


//$oTpl = new OA_Plugin_Template('oxMarkedTextAdvertiser.html', 'oxMarkedTextAdvertiser'); 
$oTpl = new OA_Admin_Template('banner-index.html');


$doBanners = OA_Dal::factoryDO('banners');
$doBanners->campaignid = $campaignid;
$doBanners->addListorderBy($listorder, $orderdirection);
$doBanners->selectAdd('storagetype AS type');
$doBanners->find();

$countActive = 0;

while ($doBanners->fetch() && $row = $doBanners->toArray()) {
    $banners[$row['bannerid']] = $row;
	$banners[$row['bannerid']]['active'] = $banners[$row['bannerid']]["status"] == OA_ENTITY_STATUS_RUNNING;

    $banners[$row['bannerid']]['description'] = $strUntitled;
    if (isset($banners[$row['bannerid']]['alt']) && $banners[$row['bannerid']]['alt'] != '') {
		$banners[$row['bannerid']]['description'] = $banners[$row['bannerid']]['alt'];
    }

    $campaign_details = Admin_DA::getPlacement($row['campaignid']);
    $campaignAnonymous = $campaign_details['anonymous'] == 't' ? true : false;
    $banners[$row['bannerid']]['description'] = MAX_getAdName($row['description'], null, null, $campaignAnonymous, $row['bannerid']);

    $banners[$row['bannerid']]['expand'] = 0;
    if ($row['status'] == OA_ENTITY_STATUS_RUNNING) {
        $countActive++;
    }
}

$aCount = array(
    'banners'        => 0,
    'banners_hidden' => 0,
);


$bannersHidden = 0;
if (isset($banners) && is_array($banners) && count($banners) > 0) {
    reset ($banners);
    while (list ($key, $banner) = each ($banners)) {
		$aCount['banners']++;
        if (($hideinactive == true) && ($banner['status'] != OA_ENTITY_STATUS_RUNNING)) {
            $bannersHidden++;
			$aCount['banners_hidden']++;
            unset($banners[$key]);
        }
    }
}

$oTpl->assign('clientId', $clientid);
$oTpl->assign('campaignId', $campaignid);
$oTpl->assign('aBanners', $banners);
$oTpl->assign('aCount', $aCount);
$oTpl->assign('hideinactive', $hideinactive);
$oTpl->assign('listorder', $listorder);
$oTpl->assign('orderdirection', $orderdirection);
$oTpl->assign('isManager', false);

$oTpl->assign('canACL', !OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER));
$oTpl->assign('canEdit', !OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER) || OA_Permission::hasPermission(OA_PERM_BANNER_EDIT));
$oTpl->assign('canActivate', !OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER) || OA_Permission::hasPermission(OA_PERM_BANNER_ACTIVATE));
$oTpl->assign('canDeactivate', !OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER) || OA_Permission::hasPermission(OA_PERM_BANNER_DEACTIVATE));
$oTpl->assign('canDelete', !OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER));

$session['prefs']['campaign-banners.php'][$campaignid]['hideinactive'] = $hideinactive;
$session['prefs']['campaign-banners.php'][$campaignid]['listorder'] = $listorder;
$session['prefs']['campaign-banners.php'][$campaignid]['orderdirection'] = $orderdirection;
$session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid'] = $clientid;
$session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['campaignid'][$clientid] = $campaignid;

phpAds_SessionDataStore();

$oTpl->display();

phpAds_PageFooter();

function buildHeaderModel($aEntities)
{
    global $phpAds_TextDirection;
    $aConf = $GLOBALS['_MAX']['CONF'];

    $advertiserId = $aEntities['clientid'];
    $campaignId = $aEntities['campaignid'];
    $agencyId = OA_Permission::getAgencyId();

    $entityString = _getEntityString($aEntities);
    $aOtherEntities = $aEntities;
    unset($aOtherEntities['campaignid']);
    $otherEntityString = _getEntityString($aOtherEntities);

    $advertiser = phpAds_getClientDetails ($advertiserId);
    $advertiserName = $advertiser ['clientname'];
    $campaignDetails = Admin_DA::getPlacement($campaignId);
    $campaignName = $campaignDetails['name'];

    $builder = new OA_Admin_UI_Model_InventoryPageHeaderModelBuilder();
    $oHeaderModel = $builder->buildEntityHeader(array(
        array ('name' => $advertiserName, 'url' => '',
               'id' => $advertiserId, 'entities' => getAdvertiserMap($agencyId),
               'htmlName' => 'clientid'
              ),
        array ('name' => $campaignName, 'url' => $campaignEditUrl,
               'id' => $campaignId, 'entities' => getCampaignMap($advertiserId),
               'htmlName' => 'campaignid'
              ),
        array('name' => '')
    ), 'banners', 'list');

    return $oHeaderModel;
}


function getAdvertiserMap()
{
    $aAdvertisers = array();
    $dalClients = OA_Dal::factoryDAL('clients');

    if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
        $advertiserId = OA_Permission::getEntityId();
        $aAdvertiser = $dalClients->getAdvertiserDetails($advertiserId);
        $aAdvertisers[$advertiserId] = $aAdvertiser;
    }

    $aAdvertiserMap = array();
    foreach ($aAdvertisers as $clientid => $aClient) 
    {
        $aAdvertiserMap[$clientid] = array('name' => $aClient['clientname'],
            'url' => "advertiser-campaigns.php?clientid=".$clientid);
    }

    return $aAdvertiserMap;
}


function getCampaignMap($advertiserId)
{
    $aCampaigns = Admin_DA::getPlacements(array('advertiser_id' => $advertiserId));

    $aCampaignMap = array();
    foreach ($aCampaigns as $campaignId => $aCampaign) {
        $campaignName = $aCampaign['name'];
        $campaign_details = Admin_DA::getPlacement($campaignId);
        $campaignName = MAX_getPlacementName($campaign_details);
        $aCampaignMap[$campaignId] = array('name' => $campaignName);
    }

    return $aCampaignMap;
}


?>
