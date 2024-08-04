<?php
/**
 * Created by PhpStorm.
 * User: Mohammadi
 * Date: 08/05/2018
 * Time: 19:43
 */
try {
    OW::getNavigation()->deleteMenuItem('frmmainpage', 'settings');
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'frmmainpage.settings', 'frmmainpage', 'settings', OW_Navigation::VISIBLE_FOR_MEMBER);
}
catch(Exception $e){

}