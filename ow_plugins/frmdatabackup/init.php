<?php
/**
 * 
 * All rights reserved.
 */

OW::getRouter()->addRoute(new OW_Route('frmdatabackup.admin', 'frmdatabackup/admin', 'FRMDATABACKUP_CTRL_Admin', 'index'));
OW::getRouter()->addRoute(new OW_Route('frmdatabackup.admin.data', 'frmdatabackup/admin/data', 'FRMDATABACKUP_CTRL_Admin', 'data'));