<?php

require_once MAX_PATH . '/lib/OA.php';
require_once MAX_PATH . '/lib/max/Plugin/Common.php';
require_once LIB_PATH . '/Extension/bannerTypeText/bannerTypeText.php';
require_once MAX_PATH . '/lib/max/Plugin/Translation.php';

class Plugins_BannerTypeText_oxMarkedText_oxMarkedTextComponent extends Plugins_BannerTypeText
{
    function getContentType()
    {
        return 'text';
    }

    function getStorageType()
    {
        return 'txt';
    }
    
    function getOptionDescription()
    {
        return $this->translate("Text ads with simple markup");
    }

    function buildForm(&$form, &$row)
    {
        $header = $form->createElement('header', 'header_txt', $GLOBALS['strTextBanner']." -  banner text");
        $header->setAttribute('icon', 'icon-banner-text.gif');
        $form->addElement($header);

        $textG['textarea'] =  $form->createElement('textarea', 'bannertext', null,
            array(
                'class' =>'code', 'cols'=>'45', 'rows'=>'10', 'wrap'=>'off',
                'dir' => 'ltr', 'style'=>'width:550px;'
            ));
        $form->addGroup($textG, 'text_banner_g', null, array("<br>", ""), false);

        $form->addElement('header', 'header_b_links', "Banner link");
        $form->addElement('text', 'url', $GLOBALS['strURL']);

        $form->addElement('hidden', 'ext_bannertype', $this->getComponentIdentifier());
    }
    
    function preprocessForm($insert, $bannerid, $aFields)
    {
        return true;
    }

    function processForm($insert, $bannerid, $aFields)
    {
        return true;
    }

    function validateForm(&$form)
    {
        return true;
    }

    function getBannerCache($buffer, &$noScript, $banner)
    {
        return $buffer;
    }

}
