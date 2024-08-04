<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 6/11/2017
 * Time: 11:43 AM
 */

if (!OW::getConfig()->configExists('frmvitrin', 'description')) {
    OW::getConfig()->saveConfig('frmvitrin', 'description', '');
}