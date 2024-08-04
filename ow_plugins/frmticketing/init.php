<?php
/**
 * FRMTICKETING
 */
$plugin = OW::getPluginManager()->getPlugin('frmticketing');
$bolDir = $plugin->getBolDir();
#include services
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketService', $plugin->getBolDir() . 'service' . DS . 'ticket_service.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketAttachmentService', $plugin->getBolDir() . 'service' . DS . 'ticket_attachment_service.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketCategoryService', $plugin->getBolDir() . 'service' . DS . 'ticket_category_service.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketCategoryUserService', $plugin->getBolDir() . 'service' . DS . 'ticket_category_user_service.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketOrderService', $plugin->getBolDir() . 'service' . DS . 'ticket_order_service.php');

# include dao
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketDao', $bolDir . 'dao' . DS . 'ticket_dao.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketAttachmentDao', $bolDir . 'dao' . DS . 'ticket_attachment_dao.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketCategoryDao', $bolDir . 'dao' . DS . 'ticket_category_dao.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketCategoryUserDao', $bolDir . 'dao' . DS . 'ticket_category_user_dao.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketEditPostDao', $bolDir . 'dao' . DS . 'ticket_edit_post_dao.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketOrderDao', $bolDir . 'dao' . DS . 'ticket_order_dao.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketPostDao', $bolDir . 'dao' . DS . 'ticket_post_dao.php');

# include dto
OW::getAutoloader()->addClass('FRMTICKETING_BOL_Ticket', $bolDir . 'dto' . DS . 'ticket.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketAttachment', $bolDir . 'dto' . DS . 'ticket_attachment.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketCategory', $bolDir . 'dto' . DS . 'ticket_category.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketCategoryUser', $bolDir . 'dto' . DS . 'ticket_category_user.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketEditPost', $bolDir . 'dto' . DS . 'ticket_edit_post.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketOrder', $bolDir . 'dto' . DS . 'ticket_order.php');
OW::getAutoloader()->addClass('FRMTICKETING_BOL_TicketPost', $bolDir . 'dto' . DS . 'ticket_post.php');

OW::getRouter()->addRoute(new OW_Route('frmticketing.view_tickets', 'frmticketing/tickets', 'FRMTICKETING_CTRL_Ticket', 'viewList'));
OW::getRouter()->addRoute(new OW_Route('frmticketing.view_ticket', 'frmticketing/ticket/:ticketId', 'FRMTICKETING_CTRL_Ticket', 'view'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.add-ticket', 'frmticketing/addTicket', 'FRMTICKETING_CTRL_AddTicket', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.add-post', 'frmticketing/addPost/:ticketId/:uid', 'FRMTICKETING_CTRL_Ticket', 'addPost'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.delete-post', 'frmticketing/deletePost/:ticketId/:postId', 'FRMTICKETING_CTRL_Ticket', 'deletePost'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.lock-ticket', 'frmticketing/lockTicket/:ticketId/:page', 'FRMTICKETING_CTRL_Ticket', 'lockTicket'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.delete-ticket', 'frmticketing/deleteTicket/:ticketId', 'FRMTICKETING_CTRL_Ticket', 'deleteTicket'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.get-post', 'frmticketing/getPost/:postId', 'FRMTICKETING_CTRL_Ticket', 'getPost'));

OW::getRouter()->addRoute(new OW_Route('frmticketing.admin', 'admin/plugins/frmticketing', "FRMTICKETING_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmticketing.find_similar_usernames', 'admin/plugins/autocompleteUsernames', "FRMTICKETING_CTRL_Admin", 'autoCompleteUsernames'));
OW::getRouter()->addRoute(new OW_Route('frmticketing.admin-currentSection', 'admin/plugins/frmticketing/:currentSection', "FRMTICKETING_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('frmticketing.delete_attachment', 'frmticketing/deleteAttachment', 'FRMTICKETING_CTRL_Ticket', 'ajaxDeleteAttachment'));



$eventHandler = FRMTICKETING_CLASS_EventHandler::getInstance();
$eventHandler->init();