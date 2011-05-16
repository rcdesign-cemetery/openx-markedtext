<?php

require_once '../../../../init.php';

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/OA/Dll.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-statistics.inc.php';
require_once MAX_PATH . '/www/admin/lib-zones.inc.php';
require_once MAX_PATH . '/lib/OA/Maintenance/Priority.php';

phpAds_registerGlobal ('value');

if ($value == OA_ENTITY_STATUS_RUNNING) {
    $value = OA_ENTITY_STATUS_PAUSED;
} else {
    $value = OA_ENTITY_STATUS_RUNNING;
}

OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER, OA_ACCOUNT_ADVERTISER);
OA_Permission::enforceAccessToObject('clients',   $clientid);
OA_Permission::enforceAccessToObject('campaigns', $campaignid);
OA_Permission::enforceAccessToObject('banners',   $bannerid, true);

if (OA_Permission::isAccount(OA_ACCOUNT_ADVERTISER)) {
    if ($value == OA_ENTITY_STATUS_RUNNING) {
        OA_Permission::enforceAllowed(OA_PERM_BANNER_ACTIVATE);
    } else {
        OA_Permission::enforceAllowed(OA_PERM_BANNER_DEACTIVATE);
    }
}


if (!empty($bannerid))
{
    $doBanners = OA_Dal::factoryDO('banners');
    $doBanners->get($bannerid);
    $bannerName = $doBanners->description;
    
    $translation = new OX_Translation();
    $message = ($value == OA_ENTITY_STATUS_PAUSED) ? $GLOBALS ['strBannerHasBeenDeactivated'] : $GLOBALS ['strBannerHasBeenActivated'];
    $translated_message = $translation->translate($message, array (
        "banner-edit.php?clientid=$clientid&campaignid=$campaignid&bannerid=$bannerid", 
        htmlspecialchars($bannerName)
    ));
    OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);
    
    $doBanners->status = $value;
    $doBanners->update();
}
elseif (!empty($campaignid))
{
    $doBanners = OA_Dal::factoryDO('banners');
    $doBanners->status = $value;
    $doBanners->whereAdd('campaignid = ' . $campaignid);

    $doBanners->update(DB_DATAOBJECT_WHEREADD_ONLY);
}

OA_Maintenance_Priority::scheduleRun();

header("Location: oxMarkedTextAdvertiser-index.php?clientid=".$clientid."&campaignid=".$campaignid);

?>
