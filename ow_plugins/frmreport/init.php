<?php
OW::getRouter()->addRoute(new OW_Route('frmreport.admin','frmreport/admin','FRMREPORT_CTRL_Admin','index'));

OW::getRouter()->addRoute(new OW_Route('report_index','reports/:groupId','FRMREPORT_CTRL_Report','index'));
OW::getRouter()->addRoute(new OW_Route('report_add','addreport/:groupId','FRMREPORT_CTRL_Report','add'));
OW::getRouter()->addRoute(new OW_Route('report_detail','reportdetail/:reportId','FRMREPORT_CTRL_Report','detail'));
OW::getRouter()->addRoute(new OW_Route('report_edit','reportedit/:reportId','FRMREPORT_CTRL_Report','edit'));
OW::getRouter()->addRoute(new OW_Route('report_overall','overallreports','FRMREPORT_CTRL_Report','overallreports'));

FRMREPORT_CLASS_EventHandler::getInstance()->init();


