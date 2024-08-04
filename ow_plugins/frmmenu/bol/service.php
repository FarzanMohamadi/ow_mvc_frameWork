<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmenu
 * @since 1.0
 */
class FRMMENU_BOL_Service
{
    private static $classInstance;
    const HIDE_UNWANTED_ELEMENTS = 'frmmenu.hide.unwanted.element';
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }


    private function __construct()
    {
    }

    public function onBeforeContextMenuRender(OW_Event $event)
    {
        $adminEvent = OW::getEventManager()->call('admin.check_if_admin_page');
        if($adminEvent){
            return;
        }
        $params = $event->getParams();
        if(isset($params['actions']) && isset($params['position'])){
            $actions = $params['actions'];
            foreach ( $actions as & $action )
            {
                $actionDto = $action['action'];
                if ( $actionDto->getClass() != "ow_newsfeed_context" )
                {
                    return;
                }
                if(isset($action['subactions'])) {
                    $subActions = $action['subactions'];
                    foreach ($subActions as & $subAction) {
                        if (isset($subAction->getAttributes()['onclick'])) {
                            $subAction->setClass($subAction->getClass() . ' ' . $this->getContextActionClassIcon($subAction->getAttributes()['onclick']));
                        }
                    }
                }
            }
            $contextMenu = new FRMMENU_CMP_ContextAction();
            $contextMenu->setActions($actions);
            $contextMenu->setPosition($params['position']);
            $contextMenu->setVisible($params['visible']);
            $event->setData(array('cmp' => $contextMenu));
        }else if(isset($params['items'])){
            $items = $params['items'];
            $newItems = array();
            foreach ( $items as & $item )
            {
                if(isset($item['attributes']['onclick'])){
                    $item['class'] = $item['class'].' '.$this->getContextActionClassIcon($item['attributes']['onclick']);
                }
                $newItems[] = $item;
            }
            $contextMenu = new FRMMENU_MCMP_ContextAction($newItems);
            $event->setData(array('cmp' => $contextMenu));
        }
        OW::getDocument()->addOnloadScript("loadFRMMenu();");
        $this->loadOveralCss();
    }

    public function onBeforeDocumentRender(OW_Event $event)
    {
        $adminEvent = OW::getEventManager()->call('admin.check_if_admin_page');
        if($adminEvent){
            return;
        }
        $this->createTopMenu();
    }

    public function createTopMenu(){
        if(OW::getConfig()->getValue('frmmenu', 'replaceMenu')){
            $menuItems = OW::getDocument()->getMasterPage()->getMenu(BOL_NavigationService::MENU_TYPE_MAIN)->getMenuItems();
            $menuHtml = '<nav class="cd-stretchy-nav"><a class="cd-nav-trigger" href="#0">Menu<span aria-hidden="true"></span></a><ul>';
            foreach ($menuItems as $menuItem) {
                $menuItem->activate(OW::getRouter()->getBaseUrl() . OW::getRequest()->getRequestUri());
                $target = "_self";
                if ($menuItem->getNewWindow()) {
                    $target = "_blank";
                }
                $moreClass = "";
                if ($menuItem->isActive()) {
                    $moreClass = "active";
                }
                $menuHtml .= '<li><a href="' . $menuItem->getUrl() . '" target="' . $target . '" class="' . $this->getMenuClassIcon($menuItem->getUrl()) . ' ' . $moreClass . '"><span>' . $menuItem->getLabel() . '</span></a></li>';
            }
            $menuHtml .= '</ul><span aria-hidden="true" class="stretchy-nav-bg"></span></nav>';
            OW::getDocument()->addOnloadScript("$('body').append('" . $menuHtml . "');loadFRMMenu();");
            $this->loadMenuCss();
        }
    }

    public function getMenuClassIcon($url){
        if (strpos($url, 'event')>0) {
            return "event";
        } else if (strpos($url, 'newsfeed')>0) {
            return "newsfeed";
        } else if (strpos($url, 'video')>0) {
            return "video";
        } else if (strpos($url, 'photo')>0) {
            return "photo";
        } else if (strpos($url, 'contact')>0) {
            return "contact";
        } else if (strpos($url, 'news')>0) {
            return "news";
        } else if (strpos($url, 'group')>0) {
            return "group";
        } else if (strpos($url, 'dashboard')>0) {
            return "dashboard";
        } else if (strpos($url, 'audio')>0) {
            return "audio";
        } else if (strpos($url, 'userlogin')>0) {
            return "login";
        } else if (strpos($url, 'index')>0) {
            return "home";
        } else if (strpos($url, 'hashtag')>0) {
            return "hashtag";
        } else if (strpos($url, 'forum')>0) {
            return "forum";
        } else if (strpos($url, 'competitions')>0) {
            return "competitions";
        } else if (strpos($url, 'blogs')>0) {
            return "blogs";
        } else if (strpos($url, 'users')>0) {
            return "users";
        } else if (strpos($url, 'telegram')>0) {
            return "telegram";
        } else if (strpos($url, 'questions')>0) {
            return "questions";
        } else if (strpos($url, 'vitrin')>0) {
            return "vitrin";
        }

        return "item";
    }

    public function getContextActionClassIcon($url){
        if (strpos($url, 'deleteUser')>0) {
            return "delete_user";
        } else if (strpos($url, 'remove')>0) {
            return "remove";
        } else if (strpos($url, 'flag')>0) {
            return "flag";
        }
        return "item";
    }

    public function loadMenuCss(){
        $cssStaticUrl = OW::getPluginManager()->getPlugin('frmmenu')->getStaticCssUrl();
        OW::getDocument()->addStyleSheet( $cssStaticUrl . 'frmmenu.css');
        $this->loadOveralCss();
    }

    public function loadOveralCss(){
        $cssStaticUrl = OW::getPluginManager()->getPlugin('frmmenu')->getStaticCssUrl();
        OW::getDocument()->addStyleSheet( $cssStaticUrl . 'style.css');
        OW::getDocument()->addStyleSheet( $cssStaticUrl . 'overall.css');

        $jsStaticUrl = OW::getPluginManager()->getPlugin('frmmenu')->getStaticJsUrl();
        OW::getDocument()->addScript($jsStaticUrl . 'main.js');
    }


    public function hideUnwantedElements(OW_Event $event){
        $adminEvent = OW::getEventManager()->call('admin.check_if_admin_page');
        if($adminEvent){
            return;
        }
        OW::getDocument()->addStyleDeclaration(".cd-nav-trigger{display:none;}");
        OW::getDocument()->addStyleDeclaration(".cd-stretchy-nav{display:none;}");
    }
}
