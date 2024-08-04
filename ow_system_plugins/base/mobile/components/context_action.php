<?php
/**
 * @package ow_system_plugins.base.mobile.components
 * @since 1.6.0
 */
class BASE_MCMP_ContextAction extends BASE_MCMP_AbstractButtonList
{
    protected $items = array();
    protected $uniqId;
    
    /**
     * Constructor.
     */
    public function __construct( $items, $label = null )
    {
        parent::__construct();
        
        if ( empty($items) )
        {
            $this->setVisible(false);
        }
        
        $this->items = $items;
        $this->uniqId = FRMSecurityProvider::generateUniqueId("ca-");
        
        $this->assign("uniqId", $this->uniqId);
        $this->assign("label", $label);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        $this->initList();
        
        $js = UTIL_JsGenerator::newInstance();
        $js->jQueryEvent("#" . $this->uniqId . " .ca-dropdown-btn", "click", 
                'var dd = $(this).parents(".ca-dropdown-wrap:eq(0)").find(".ca-dropdown"); isVisible = dd.is(":visible"); '
                . 'hide_opened_dropdown(); '
                . 'return isVisible ? (dd.hide(), true) : (open_dropdown(dd), false);');
        
        $js->addScript('$(document).on("click", function(e) { return $(e.target).is(".ca-dropdown, .ca-dropdown *") ? false : hide_opened_dropdown(), true; });');
        $js->addScript('function hide_opened_dropdown(){ $(".ca-dropdown:visible").siblings(".ca-dropdown-btn").find("span.owm_context_arr_c").removeClass("frmmenu_active_opened_dropdown"), $(".ca-dropdown:visible").hide() }');
        $js->addScript('function open_dropdown(dd){ dd.show(); $(".ca-dropdown:visible").siblings(".ca-dropdown-btn").find("span.owm_context_arr_c").addClass("frmmenu_active_opened_dropdown") }');

        OW::getDocument()->addOnloadScript($js);
    }

    protected function initList()
    {
        $tplActions = array();

        foreach ( $this->items as $item  )
        {
            $tplActions[] = $this->prepareItem($item, "owm_context_action_list_item");
        }
       
        $this->assign("buttons", $this->getSortedItems($tplActions));
    }
}