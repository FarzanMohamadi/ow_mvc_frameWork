<?php
if ( !OW::getConfig()->configExists('photo', 'list_view_type') )
{
    OW::getConfig()->saveConfig('photo', 'list_view_type', 'photos');
}

