<?php
if ( !OW::getConfig()->configExists('frminstagram', 'instagram_load_more_url') )
{
    OW::getConfig()->saveConfig('frminstagram', 'instagram_load_more_url', 'https://www.instagram.com/graphql/query/?query_id=17888483320059182&variables={"id":"%s","first":%s,"after":"%s"}');
}

if ( !OW::getConfig()->configExists('frminstagram', 'instagram_url') )
{
    OW::getConfig()->saveConfig('frminstagram', 'instagram_url', 'https://www.instagram.com/');
}