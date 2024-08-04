<?php
/**
 * frminstagram
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frminstagram
 * @since 1.0
 */
OW::getConfig()->saveConfig('frminstagram', 'default_page', '', 'Default Page for Widget.');
OW::getConfig()->saveConfig('frminstagram', 'instagram_load_more_url', 'https://www.instagram.com/graphql/query/?query_id=17888483320059182&variables={"id":"%s","first":%s,"after":"%s"}');
OW::getConfig()->saveConfig('frminstagram', 'instagram_url', 'https://www.instagram.com/');
