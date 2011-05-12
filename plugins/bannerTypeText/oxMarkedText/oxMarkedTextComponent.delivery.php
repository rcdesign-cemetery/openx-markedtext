<?php

function Plugin_BannerTypeText_oxMarkedText_oxMarkedTextComponent_delivery_adRender(&$aBanner, $zoneId=0, $source='', $ct0='', $withText=false, $logClick=true, $logView=true, $useAlt=false, $loc, $referer)
{
    $conf = $GLOBALS['_MAX']['CONF'];
    $pluginConf = $GLOBALS['conf']['oxMarkedText'];
    $linkStyle1 = $pluginConf['link1Style'];
    $linkStyle2 = $pluginConf['link2Style'];
    $prepend = !empty($aBanner['prepend']) ? $aBanner['prepend'] : '';
    $append = !empty($aBanner['append']) ? $aBanner['append'] : '';
    $aBanner['bannerContent'] = $aBanner['bannertext'];
    $find = array( '[[', '[', ']]', ']' );
    $clickUrl = _adRenderBuildClickUrl($aBanner, $zoneId, $source, $ct0, $logClick);

    if (!empty($clickUrl)) {
        $status = _adRenderBuildStatusCode($aBanner);
        $target = !empty($aBanner['target']) ? $aBanner['target'] : '_blank';
        $clickTag1 = "<a href='$clickUrl' $linkStyle1 target='$target'$status>";
        $clickTag2 = "<a href='$clickUrl' $linkStyle2 target='$target'$status>";
        $clickTagEnd = '</a>';

    } else {
        $clickTag1 = '';
        $clickTag2 = '';
        $clickTagEnd = '';
    }

    $replace = array( $clickTag2, $clickTag1, $clickTagEnd, $clickTagEnd );
    $bannerText = str_replace( $find, $replace, $aBanner['bannertext'] );
    $beaconTag = ($logView && $conf['logging']['adImpressions']) ? _adRenderImageBeacon($aBanner, $zoneId, $source, $loc, $referer) : '';
    return $prepend . $bannerText . $beaconTag . $append;
}

?>
