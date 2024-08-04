<?php
/**
 * FRM Static Updater
 */

OW::getRouter()->addRoute(new OW_Route('update-all_static_files', 'update-all-static-files/:fileCode', 'FRMSTATICUPDATER_CTRL_Updater', 'updateStaticFiles'));
OW::getRouter()->addRoute(new OW_Route('update-languages', 'update-languages/:languageCode', 'FRMSTATICUPDATER_CTRL_Updater', 'updateLanguages'));
OW::getRouter()->addRoute(new OW_Route('frmstaticupdater.admin', 'frmstaticupdater/admin', "FRMSTATICUPDATER_CTRL_Admin", 'index'));