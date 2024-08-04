<?php
/**
 * frmmainpage
 */

class FRMMAINPAGE_MCMP_ChatGroupList extends OW_MobileComponent
{
    protected $showOnline = true, $list = array();
    protected $listKey;

    public function __construct($listKey, $list)
    {
        parent::__construct();
        if(isset($list['tplList']))
        {
            $this->list = $list['tplList'];
        }else {
            $this->list = $list;
        }
       $this->assign('list',  $this->list);
       $this->assign('userId', OW::getUser()->getId());

        $this->setTemplate(OW::getPluginManager()->getPlugin('frmmainpage')->getMobileCmpViewDir().'chat_group_list.html');
    }


    public function onBeforeRender()
    {
        parent::onBeforeRender();

    }

    public function getContextMenu()
    {
        return null;
    }



}