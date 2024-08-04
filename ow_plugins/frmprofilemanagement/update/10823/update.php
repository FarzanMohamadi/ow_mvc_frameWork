<?php
try{
    OW::getNavigation()->addMenuItem(OW_Navigation::MOBILE_BOTTOM, 'base_user_profile_redirection', 'base', 'main_menu_my_profile', OW_Navigation::VISIBLE_FOR_ALL);
}catch(Exception $e){}
