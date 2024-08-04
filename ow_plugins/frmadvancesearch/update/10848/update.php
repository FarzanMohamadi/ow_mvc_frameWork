<?php
if (!OW::getConfig()->configExists('frmadvancesearch', 'show_entity_author')){
    OW::getConfig()->saveConfig('frmadvancesearch', 'show_entity_author', true);
}
