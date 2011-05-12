<?php

class oxMarkedText_processSettings
{

    function validate(&$aErrorMessage)
    {
        $storeSettings = array();
        
        if (isset($GLOBALS['oxMarkedText_link1Style'])) 
        {
            $replaced1 = str_replace( array( '\"', '\"' ), array( "\'", "'" ), $GLOBALS['oxMarkedText_link1Style'] );
            $GLOBALS['_POST']['oxMarkedText_link1Style'] = $replaced1;
        }

        if (isset($GLOBALS['oxMarkedText_link2Style'])) 
        {
            $replaced2 = str_replace( array( '\"', '\"' ), array( "\'", "'" ), $GLOBALS['oxMarkedText_link2Style'] );
            $GLOBALS['_POST']['oxMarkedText_link2Style'] = $replaced2;
        }
        
        return true;
    }
}

?>
