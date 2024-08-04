<?php
/**
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ButtonList extends BASE_MCMP_AbstractButtonList
{
    protected $items = array();
    
    /**
     * Constructor.
     */
    public function __construct( $items )
    {
        parent::__construct();
        
        $this->items = $items;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $this->initList();
        
        $template = OW::getPluginManager()->getPlugin("base")->getMobileCmpViewDir() . "button_list.html";
        $this->setTemplate($template);
    }

    protected function initList()
    {
        $itemGroups = array();
        $buttons = array();

        foreach ( $this->items as $item  )
        {
            if ( isset($item["group"]) )
            {
                if ( empty($itemGroups[$item["group"]]) )
                {
                    $itemGroups[$item["group"]] = array(
                        "key" => $item["group"],
                        "label" => isset($item["groupLabel"]) ? $item["groupLabel"] : null,
                        "context" => array()
                    );
                }
                
                $itemGroups[$item["group"]]["items"][] = $item;
            }
            else 
            {
                $buttons[] = $this->prepareItem($item, "owm_btn_list_item");
            }
        }

        $tplGroups = array();
        
        foreach ( $itemGroups as $group )
        {
            $contextAction = new BASE_MCMP_ContextAction($group["items"], $group["label"]);
            $tplGroups[] = $contextAction->render();
        }
        
        $this->assign('groups', $tplGroups);
        $this->assign("buttons", $this->getSortedItems($buttons));
    }
}