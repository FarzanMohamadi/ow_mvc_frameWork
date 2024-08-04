<?php
FRMREPORT_MCLASS_EventHandler::getInstance()->init();
OW::getRouter()->addRoute(new OW_Route('report_index','reports/:groupId','FRMREPORT_MCTRL_Report','index'));
OW::getRouter()->addRoute(new OW_Route('report_add','addreport/:groupId','FRMREPORT_MCTRL_Report','add'));
OW::getRouter()->addRoute(new OW_Route('report_detail','reportdetail/:reportId','FRMREPORT_MCTRL_Report','detail'));
OW::getRouter()->addRoute(new OW_Route('report_edit','reportedit/:reportId','FRMREPORT_MCTRL_Report','edit'));
OW::getRouter()->addRoute(new OW_Route('report_overall','overallreports','FRMREPORT_MCTRL_Report','overallreports'));
