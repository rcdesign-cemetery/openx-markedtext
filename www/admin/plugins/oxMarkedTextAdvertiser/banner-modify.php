<?php

require_once '../../../../init.php';

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/www/admin/lib-storage.inc.php';
require_once MAX_PATH . '/www/admin/lib-zones.inc.php';
require_once MAX_PATH . '/www/admin/lib-banner.inc.php';
require_once MAX_PATH . '/www/admin/lib-statistics.inc.php';
require_once MAX_PATH . '/lib/OA/Maintenance/Priority.php';

phpAds_registerGlobal('bannerid', 'campaignid', 'clientid', 'returnurl', 'duplicate', 'moveto', 'moveto_x', 'applyto', 'applyto_x');

OA_Permission::enforceAccount(OA_ACCOUNT_MANAGER, OA_ACCOUNT_ADVERTISER);
OA_Permission::enforceAccessToObject('clients',   $clientid);
OA_Permission::enforceAccessToObject('campaigns', $campaignid);

if (!empty($bannerid)) {
    OA_Permission::enforceAccessToObject('banners', $bannerid, true);

    if (isset($duplicate) && $duplicate == 'true') 
      {
        $doBanners = OA_Dal::factoryDO('banners');
        $doBanners->get($bannerid);
        $oldName = $doBanners->description;
        $new_bannerid = $doBanners->duplicate();

        OA_Maintenance_Priority::scheduleRun();

        $newName = $doBanners->description;
        $translation = new OX_Translation();
        $translated_message = $translation->translate ( $GLOBALS['strBannerHasBeenDuplicated'],
            array(MAX::constructURL(MAX_URL_ADMIN, "plugins/oxMarkedTextAdvertiser/banner-edit.php?clientid=$clientid&campaignid=$campaignid&bannerid=$bannerid"),
                htmlspecialchars($oldName),
                MAX::constructURL(MAX_URL_ADMIN, "plugins/oxMarkedTextAdvertiser/banner-edit.php?clientid=$clientid&campaignid=$campaignid&bannerid=$new_bannerid"),
                htmlspecialchars($newName))
        );
        OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);

        Header ("Location: {$returnurl}?clientid={$clientid}&campaignid={$campaignid}&bannerid=".$new_bannerid);
    }
    else {
        Header ("Location: {$returnurl}?clientid={$clientid}&campaignid={$campaignid}&bannerid=".$bannerid);
    }
}

?>
