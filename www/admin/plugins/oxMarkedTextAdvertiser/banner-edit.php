<?php
require_once '../../../../init.php';

require_once MAX_PATH . '/lib/OA/Dal.php';
require_once MAX_PATH . '/lib/OA/Creative/File.php';
require_once MAX_PATH . '/www/admin/config.php';
require_once MAX_PATH . '/lib/max/other/common.php';
require_once MAX_PATH . '/lib/max/other/html.php';
require_once MAX_PATH . '/lib/OA/Admin/UI/component/Form.php';
require_once MAX_PATH . '/lib/OA/Maintenance/Priority.php';

require_once LIB_PATH . '/Plugin/Component.php';

$htmltemplate = MAX_commonGetValueUnslashed('htmltemplate');

phpAds_registerGlobalUnslashed(
     'alink'
    ,'alink_chosen'
    ,'alt'
    ,'alt_imageurl'
    ,'asource'
    ,'atar'
    ,'adserver'
    ,'bannertext'
    ,'campaignid'
    ,'checkswf'
    ,'clientid'
    ,'comments'
    ,'description'
    ,'ext_bannertype'
    ,'height'
    ,'imageurl'
    ,'keyword'
    ,'message'
    ,'replaceimage'
    ,'replacealtimage'
    ,'status'
    ,'statustext'
    ,'type'
    ,'submit'
    ,'target'
    ,'transparent'
    ,'upload'
    ,'url'
    ,'weight'
    ,'width'
);

OA_Permission::enforceAccessToObject('clients',   $clientid);
OA_Permission::enforceAccessToObject('campaigns', $campaignid);
OA_Permission::enforceAccessToObject('banners', $bannerid, true);

$session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['clientid'] = $clientid;
$session['prefs']['inventory_entities'][OA_Permission::getEntityId()]['campaignid'][$clientid] = $campaignid;

phpAds_SessionDataStore();

if ($bannerid != '') {
    $doBanners = OA_Dal::factoryDO('banners');
    if ($doBanners->get($bannerid)) {
        $aBanner = $doBanners->toArray();
    }

    $type               = $aBanner['storagetype'];
    $ext_bannertype     = $aBanner['ext_bannertype'];
    $hardcoded_links    = array();
    $hardcoded_targets  = array();
    $hardcoded_sources  = array();

    if (empty($ext_bannertype)) {
        if ($type == 'html') {
            $ext_bannertype = 'bannerTypeHtml:oxHtml:genericHtml';
        } elseif ($type == 'txt') {
            $ext_bannertype = 'bannerTypeText:oxText:genericText';
        }
    }
    
    if (!empty($aBanner['parameters'])) {
        $aSwfParams = unserialize($aBanner['parameters']);
        if (!empty($aSwfParams['swf'])) {
            foreach ($aSwfParams['swf'] as $iKey => $aSwf) {
                $hardcoded_links[$iKey]   = $aSwf['link'];
                $hardcoded_targets[$iKey] = $aSwf['tar'];
                $hardcoded_sources[$iKey] = '';
            }
        }
    }

    $aBanner['hardcoded_links'] = $hardcoded_links;
    $aBanner['hardcoded_targets'] = $hardcoded_targets;
    $aBanner['hardcoded_sources'] = $hardcoded_sources;
    $aBanner['clientid']   = $clientid;

}
else 
{
    $aBanner['bannerid']     = '';
    $aBanner['campaignid']   = $campaignid;
    $aBanner['clientid']     = $clientid;
    $aBanner['alt']          = '';
    $aBanner['status']       = '';
    $aBanner['bannertext']   = '';
    $aBanner['url']          = "http://";
    $aBanner['target']       = '';
    $aBanner['imageurl']     = "http://";
    $aBanner['width']        = '';
    $aBanner['height']       = '';
    $aBanner['htmltemplate'] = '';
    $aBanner['description']  = '';
    $aBanner['comments']     = '';
    $aBanner['contenttype']  = '';
    $aBanner['adserver']     = '';
    $aBanner['keyword']      = '';
    $aBanner["weight"]       = $pref['default_banner_weight'];

    $aBanner['hardcoded_links'] = array();
    $aBanner['hardcoded_targets'] = array();

}
if ($ext_bannertype)
{
    list($extension, $group, $plugin) = explode(':', $ext_bannertype);
    $oComponent = &OX_Component::factory($extension, $group, $plugin);
    
    if (!$oComponent)
    {
        $oComponent = OX_Component::getFallbackHandler($extension);
    }
    $formDisabled = (!$oComponent || !$oComponent->enabled);
}
if ((!$ext_bannertype) && $type && (!in_array($type, array('sql','web','url','html','txt'))))
{
    list($extension, $group, $plugin) = explode('.',$type);
    $oComponent = &OX_Component::factoryByComponentIdentifier($extension,$group,$plugin);
    $formDisabled = (!$oComponent || !$oComponent->enabled);
    if ($oComponent)
    {
        $ext_bannertype = $type;
        $type = $oComponent->getStorageType();
    }
    else
    {
        $ext_bannertype = '';
        $type = '';
    }
}


$show_txt   = $conf['allowedBanners']['text'];

if (isset($type) && $type == "txt")      $show_txt     = true;

$bannerTypes = array();

if ($show_txt) {
    $aBannerTypeText = OX_Component::getComponents('bannerTypeText');
    foreach ($aBannerTypeText AS $tmpComponent)
    {
        $componentIdentifier = $tmpComponent->getComponentIdentifier();
        $bannerTypes['text'][$componentIdentifier] = $tmpComponent->getOptionDescription();
    }
}

if (!$type)
{
    if ($show_txt)     $type = "txt";
}

$form = buildBannerForm($type, $aBanner, $oComponent, $formDisabled);

$valid = $form->validate();
if ($valid && $oComponent && $oComponent->enabled)
{
    $valid = $oComponent->validateForm($form);
}
if ($valid)
{
    processForm($bannerid, $form, $oComponent, $formDisabled);
}
else {
    displayPage($bannerid, $campaignid, $clientid, $bannerTypes, $aBanner, $type, $form, $ext_bannertype, $formDisabled);
}


function displayPage($bannerid, $campaignid, $clientid, $bannerTypes, $aBanner, $type, $form, $ext_bannertype, $formDisabled=false)
{
    $pageName = 'oxmarkedtext-banners';
    $aEntities = array('clientid' => $clientid, 'campaignid' => $campaignid, 'bannerid' => $bannerid);

    $entityId = OA_Permission::getEntityId();

    $entityType = 'advertiser_id';

    $aOtherCampaigns = Admin_DA::getPlacements(array($entityType => $entityId));
    $aOtherBanners = Admin_DA::getAds(array('placement_id' => $campaignid), false);
    MAX_displayNavigationBanner( 'advertiser-campaigns', $aOtherCampaigns, $aOtherBanners, $aEntities);

    $oTpl = new OA_Admin_Template('banner-edit.html');

    $oTpl->assign('clientId',  $clientid);
    $oTpl->assign('campaignId',  $campaignid);
    $oTpl->assign('bannerId',  $bannerid);
    $oTpl->assign('bannerTypes', $bannerTypes);
    $oTpl->assign('bannerType', 'bannerTypeText:oxMarkedText:oxMarkedTextComponent');
    $oTpl->assign('bannerHeight', $aBanner["height"]);
    $oTpl->assign('bannerWidth', $aBanner["width"]);
    $oTpl->assign('disabled', $formDisabled);
    $oTpl->assign('form', $form->serialize());

    $oTpl->display();
    phpAds_PageFooter();
}


function buildBannerForm($type, $aBanner, &$oComponent=null, $formDisabled=false)
{
    $form = new OA_Admin_UI_Component_Form("bannerForm", "POST", $_SERVER['SCRIPT_NAME'], null, array("enctype"=>"multipart/form-data"));
    $form->forceClientValidation(true);
    $form->addElement('hidden', 'clientid', $aBanner['clientid']);
    $form->addElement('hidden', 'campaignid', $aBanner['campaignid']);
    $form->addElement('hidden', 'bannerid', $aBanner['bannerid']);
    $form->addElement('hidden', 'type', 'txt');
    $form->addElement('hidden', 'status', $aBanner['status']);

    $translation = new OX_Translation();
    $pluginConf = $GLOBALS['conf']['oxMarkedText'];
    $bannerTextMaxLength = $pluginConf['textMaxLength'];
    $regularLinkMaxLength = $pluginConf['anchor1MaxLength'];
    $highlightedLinkMaxLength = $pluginConf['anchor2MaxLength'];

        $form->addElement('header', 'header_b_links', "Banner content");
        $form->addElement('text', 'description', $GLOBALS['strName'] );

        $fieldwithdescr[] = $form->createElement('textarea', 'bannertext', '<label for="bannertext" style="display: block; float: left; width: 170px;">Banner text</label>' );
        $fieldwithdescr[] = $form->createElement('static',
           'descr',
           str_replace( array( 'XX', 'YY', 'ZZ' ), array( $bannerTextMaxLength, $regularLinkMaxLength, $highlightedLinkMaxLength ), '<p>Type your ad text, mark any blocks with brackets to add link. Example: <i>My [super ads] for only [[123$]]</i>.</p> Single brackets will show regular link and double brackets means highlighted link. You can use <ul><li>- up to XX chars for whole ad text</li><li>- 1 regular link, up to YY chars </li><li>- 1 highlighted link, up to ZZ chars</li></ul>' ) );

        $form->addGroup($fieldwithdescr, 'bannertext', null, '<br />');
        
        $form->addElement('text', 'url', $GLOBALS['strURL']);

        $form->addElement('hidden', 'ext_bannertype', 'bannerTypeText:oxMarkedText:oxMarkedTextComponent' );

	$weightfield[] = $form->createElement( 'text', 'weight', '<label for="weight" style="display: block; float: left; width: 170px;">'.$GLOBALS['strWeight'].'</label>' );
        $weightfield[] = $form->createElement('static',
           'weightdescr',
           '<p>If you want to show this ad more frequently, adjust its weight.</p>');

        $form->addGroup($weightfield, 'weight', null, '<br />');

	$weightPositiveRule = $translation->translate($GLOBALS['strXPositiveWholeNumberField'], array($GLOBALS['strWeight']));
	$form->addRule('weight', $weightPositiveRule, 'numeric');

        $form->addRule('bannertext', "Maximum $bannerTextMaxLength characters", 'maxlength', $bannerTextMaxLength, 'client' );

    $form->addElement('controls', 'form-controls');
    $form->addElement('submit', 'submit', 'Save changes');

    $form->setDefaults($aBanner);

    if ($formDisabled)
    {
        $form->freeze();
    }

    return $form;
}

function processForm($bannerid, $form, &$oComponent, $formDisabled=false)
{
    $aFields = $form->exportValues();

    $doBanners = OA_Dal::factoryDO('banners');
    
    if (!empty($bannerid)) {
        if ($doBanners->get($bannerid)) {
            $aBanner = $doBanners->toArray();
        }
    }

    $aVariables = array();
    $aVariables['campaignid']      = $aFields['campaignid'];
    $aVariables['target']          = isset($aFields['target']) ? $aFields['target'] : '';
    $aVariables['height']          = isset($aFields['height']) ? $aFields['height'] : 0;
    $aVariables['width']           = isset($aFields['width'])  ? $aFields['width'] : 0;
    $aVariables['weight']          = !empty($aFields['weight']) ? $aFields['weight'] : 0;
    $aVariables['adserver']        = !empty($aFields['adserver']) ? $aFields['adserver'] : '';
    $aVariables['alt']             = !empty($aFields['alt']) ? $aFields['alt'] : '';
    $aVariables['bannertext']      = !empty($aFields['bannertext']) ? $aFields['bannertext'] : '';
    $aVariables['htmltemplate']    = !empty($aFields['htmltemplate']) ? $aFields['htmltemplate'] : '';
    $aVariables['description']     = !empty($aFields['description']) ? $aFields['description'] : '';
    $aVariables['imageurl']        = (!empty($aFields['imageurl']) && $aFields['imageurl'] != 'http://') ? $aFields['imageurl'] : '';
    $aVariables['url']             = (!empty($aFields['url']) && $aFields['url'] != 'http://') ? $aFields['url'] : '';
    $aVariables['status']          = ($aFields['status'] != '') ? $aFields['status'] : '';
    $aVariables['storagetype']     = $aFields['type'];
    $aVariables['ext_bannertype']  = 'bannerTypeText:oxMarkedText:oxMarkedTextComponent';
    $aVariables['comments']        = $aFields['comments'];
    $aVariables['contenttype'] = 'txt';

    if (isset($aFields['keyword']) && $aFields['keyword'] != '') {
        $keywordArray = split('[ ,]+', $aFields['keyword']);
        $aVariables['keyword'] = implode(' ', $keywordArray);
    } else {
        $aVariables['keyword'] = '';
    }

    $editSwf = false;

    $parameters = null;


    $aVariables['parameters'] = serialize($parameters);

    $insert = (empty($bannerid)) ? true : false;


    $doBanners->setFrom($aVariables);
    if ($insert) {
        $bannerid = $doBanners->insert();
        OA_Maintenance_Priority::scheduleRun();
    } else {
        $doBanners->update();
        
        if ($aVariables['width'] != $aBanner['width'] || $aVariables['height'] != $aBanner['height']) {
            MAX_adjustAdZones($bannerid);
            MAX_addDefaultPlacementZones($bannerid, $aVariables['campaignid']);
        }
    }


    $translation = new OX_Translation ();
    if ($insert) {
        $translated_message = $translation->translate ( $GLOBALS['strBannerHasBeenAdded'], array(
            MAX::constructURL(MAX_URL_ADMIN, 'banner-edit.php?clientid=' .  $aFields['clientid'] . '&campaignid=' . $aFields['campaignid'] . '&bannerid=' . $bannerid),
            htmlspecialchars($aFields['description'])
        ));
        OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);

        $nextPage = "oxMarkedTextAdvertiser-index.php?clientid=".$aFields['clientid']."&campaignid=".$aFields['campaignid'];
    }
    else {
        $translated_message = $translation->translate($GLOBALS['strBannerHasBeenUpdated'],
            array (
                MAX::constructURL ( MAX_URL_ADMIN, 'banner-edit.php?clientid='.$aFields['clientid'].'&campaignid='.$aFields['campaignid'].'&bannerid='.$aFields['bannerid'] ),
                htmlspecialchars ( $aFields ['description'])
            ));
            OA_Admin_UI::queueMessage($translated_message, 'local', 'confirm', 0);
            $nextPage = "banner-edit.php?clientid=".$aFields['clientid']."&campaignid=".$aFields['campaignid']."&bannerid=$bannerid";
    }

    Header("Location: $nextPage");
    exit;
}

?>
