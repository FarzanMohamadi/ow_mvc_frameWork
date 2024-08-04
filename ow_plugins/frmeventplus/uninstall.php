<?php
/**
 * frmeventplus
 */

BOL_ComponentAdminService::getInstance()->deleteWidget('FRMEVENTPLUS_CMP_FileListWidget');

$eventIisEventsPlusFiles = new OW_Event('frmeventplus.delete.files', array('allFiles'=>true));
OW::getEventManager()->trigger($eventIisEventsPlusFiles);