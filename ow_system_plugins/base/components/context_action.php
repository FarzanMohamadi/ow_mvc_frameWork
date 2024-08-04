<?php
/**
 * Context action component class.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.3.2
 */
class BASE_CMP_ContextAction extends OW_Component
{
    const POSITION_LEFT = 'ow_tooltip_top_left';
    const POSITION_RIGHT = 'ow_tooltip_top_right';

    private $position;

    private $actions = array();

    public function __construct( $position = self::POSITION_RIGHT )
    {
        parent::__construct();

        $this->position = $position;
        if(FRMSecurityProvider::themeCoreDetector()){
            $script = '
            let isMenuClicked = false;
                        $(document).click(function(e) {
                        if (!isMenuClicked) {
                            $(this).find(".ow_tooltip.ow_comments_context_tooltip ").hide();
                            $(this).find(".ow_tooltip.ow_newsfeed_context_tooltip ").hide();
                            $(this).find(".ow_tooltip.ow_small.ow_tooltip_top_right").hide();
                        }else{
                            isMenuClicked = false;
                        }
                });
                
            $(document).on("click", ".ow_context_action",function(e) {
                        var target = $(e.target.querySelector(".ow_tooltip"));
                        if (target.is(":hidden")) {
                            $(".ow_tooltip.ow_comments_context_tooltip ").hide();
                            $(".ow_tooltip.ow_newsfeed_context_tooltip ").hide();
                            $(".ow_tooltip.ow_small.ow_tooltip_top_right").hide();
                            $(this).find(".ow_tooltip.ow_comments_context_tooltip ").hide();
                            $(this).find(".ow_tooltip.ow_newsfeed_context_tooltip ").hide();
                            $(this).find(".ow_tooltip").css("display","block");
                            isMenuClicked = true;
                        }
                        else {
                            $(this).find(".ow_tooltip.ow_comments_context_tooltip ").hide();
                            $(this).find(".ow_tooltip.ow_newsfeed_context_tooltip ").hide();
                            $(this).find(".ow_tooltip.ow_small.ow_tooltip_top_right").hide();
                        }     
                    }
                );
            $(".ow_user_list_item.ow_item_set3").hover(function(){}, function(){
                $(".ow_tooltip.ow_small.ow_tooltip_top_right").hide();
            });
                ';
            OW::getDocument()->addScriptDeclaration($script);
        }else{
            $script = '$(document).on("hover", ".ow_context_action",function(e) {
                        if (e.type == "mouseenter") {
                            $(this).find(".ow_tooltip").css({opacity: 0, top: 10}).show().stop(true, true).animate({top: 18, opacity: 1}, "fast"); 
                        }
                        else { // mouseleave
                            $(this).find(".ow_tooltip").hide();  
                        }     
                    }
                );
                ';
            OW::getDocument()->addOnloadScript($script);
        }

    }

    public function addAction( BASE_ContextAction $action )
    {
        if ( $action->getParentKey() == null )
        {
            $this->actions[$action->getKey()]['action'] = $action;
        }
        else
        {
            if ( !empty($this->actions[$action->getParentKey()]) )
            {
                $this->actions[$action->getParentKey()]['subactions'][$action->getKey()] = $action;
            }
        }

        if ( $action->getOrder() === null )
        {
            $order = $action->getParentKey() === null
                ? count($this->actions)
                : count($this->actions[$action->getParentKey()]['subactions']);

            $action->setOrder($order);
        }
    }

    public function sortActionsCallback( $a1, $a2 )
    {
        $o1 = $a1->getOrder();
        $o2 = $a2->getOrder();

        $o1 = $o1 === null ? 0 : $o1;
        $o2 = $o2 === null ? 0 : $o2;

        if ($o1 == $o2)
        {
            return 0;
        }

        if ( $o1 === -1 )
        {
            return 1;
        }

        if ( $o2 === -1 )
        {
            return -1;
        }

        return ($o1 < $o2) ? -1 : 1;
    }

    public function setClass( $class )
    {
        $this->assign("class", $class);
    }
    
    public function sortParentActionsCallback( $a1, $a2 )
    {
        return $this->sortActionsCallback($a1['action'], $a2['action']);
    }

    public function render()
    {
        if ( !count($this->actions) )
        {
            $this->setVisible(false);
        }
        else
        {
            $visible = true;
            foreach ( $this->actions as & $action )
            {
                if ( empty($action['subactions']) && !$action['action']->getLabel() )
                {
                    $visible = false;
                    break;
                }

                if ( !empty($action['subactions']) )
                {
                    usort($action['subactions'], array($this, 'sortActionsCallback'));
                }
            }

            $this->setVisible($visible);
        }

        usort($this->actions, array($this, 'sortParentActionsCallback'));

        $this->assign('actions', $this->actions);

        $this->assign('position', $this->position);

        $contextMenuCMPEvent = OW::getEventManager()->trigger(new OW_Event('on.before.context.menu.render', array('actions' => $this->actions, 'position' => $this->position, 'visible' => $this->isVisible())));
        if(isset($contextMenuCMPEvent->getData()['cmp'])){
            return $contextMenuCMPEvent->getData()['cmp']->render();
        }

        return parent::render();
    }
}

class BASE_ContextAction
{
    private $key;

    private $label;

    private $url;

    private $id;

    private $class;

    private $order;

    private $parentKey;

    private $attributes = array();

    public function __construct() { }

    public function setKey( $key )
    {
        $this->key = $key;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setLabel( $label )
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setUrl( $url )
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setId( $id )
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setClass( $class )
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setOrder( $order )
    {
        $this->order = $order;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setParentKey( $parentKey )
    {
        $this->parentKey = $parentKey;
    }

    public function getParentKey()
    {
        return $this->parentKey;
    }

    public function addAttribute( $name, $value )
    {
        $this->attributes[$name] = $value;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}