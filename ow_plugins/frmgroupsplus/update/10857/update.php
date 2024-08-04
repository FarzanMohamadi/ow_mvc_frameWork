<?php
if (!OW::getConfig()->configExists('frmgroupsplus', 'groupFileAndJoinFeed')){
    OW::getConfig()->saveConfig('frmgroupsplus', 'groupFileAndJoinFeed', '["fileFeed","joinFeed"]');
}

