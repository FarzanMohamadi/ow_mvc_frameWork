<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function genericInit()
    {
        $service = FRMHASHTAG_BOL_Service::getInstance();
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        OW::getEventManager()->bind('admin.add_auth_labels', array($this, 'addAuthLabels'));
        OW::getEventManager()->bind('frmadvancesearch.on_collect_search_items', array($this, 'onCollectSearchItems'));

        //new content added
        OW::getEventManager()->bind('feed.after_comment_add', array($service, 'onAddComment'));
        OW::getEventManager()->bind('feed.action', array($service, 'onEntityUpdate') , 1500);
        OW::getEventManager()->bind('feed.delete_item', array($service, 'onEntityUpdate'));
        OW::getEventManager()->bind('hashtag.on_entity_change', array($service,'onEntityUpdate'));
        OW::getEventManager()->bind('hashtag.edit_newsfeed', array($service, 'onEntityUpdate'));
        OW::getEventManager()->bind('base_delete_comment', array($service, 'onCommentDelete'));
//        OW::getEventManager()->bind('feed.hashtag', array($service, 'feedHashtag'));

        //rendering content
        OW::getEventManager()->bind('base.comment_item_process', array($service, 'renderComments')); //comments, images
        //OW::getEventManager()->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service,'renderNewsfeed') );
        //OW::getEventManager()->bind(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array($service,'renderString')); //newsfeed, frmnews
        OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_RENDER_STRING, array($service,'renderString')); //newsfeed, groups, event, video, forum, frmnews
        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQNotificationRelease"));
    }

    public function onBeforeDocumentRender( OW_Event $event )
    {
        //  if (!startsWith(OW::getRouter()->getUri(), "forum/"))
        {
            OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('frmhashtag')->getStaticCssUrl() . 'frmhashtag.css' );

            $js = ";var frmhashtagLoadTagsUrl='". OW::getRouter()->urlForRoute('frmhashtag.load_tags')."/';";
            $js = $js.";var frmhashtagMaxCount=". OW::getConfig()->getValue('frmhashtag', 'max_count').";";
            $friends = "var frmhashtag_friends = [{tag: 'i.moradnejad', count: '5'}];";
            $js = $js.";".$friends.";";
            OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmhashtag')->getStaticJsUrl() . 'suggest.js' );
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmhashtag')->getStaticJsUrl() . 'frmhashtag.js' );
        }
    }

    /**
     * @param BASE_CLASS_EventCollector $event
     */
    public function addAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmhashtag' => array(
                    'label' => $language->text('frmhashtag', 'auth_group_label'),
                    'actions' => array(
                        'view_newsfeed' => $language->text('frmhashtag', 'auth_action_label_view_newsfeed'),
                    )
                )
            )
        );
    }

    public function onCollectSearchItems(OW_Event $event){
        $params = $event->getParams();
        $searchValue = '';
        $selected_section = null;
        if(!empty($params['selected_section']))
            $selected_section = $params['selected_section'];
        if( isset($selected_section) && $selected_section != OW_Language::getInstance()->text('frmadvancesearch','all_sections') && $selected_section!= OW::getLanguage()->text('frmadvancesearch', 'hashtag_label') )
            return;
        if ( !empty($params['q']) )
        {
            $searchValue = str_replace('#', '', $params['q']);
        }
        $maxCount = empty($params['maxCount'])?10:$params['maxCount'];
        $first= empty($params['first'])?0:$params['first'];
        $first=(int)$first;
        $count=empty($params['count'])?$first+$maxCount:$params['count'];
        $count=(int)$count;
        $result = array();
        $topics = array();

        if (!isset($params['do_query']) || $params['do_query']) {
            $topics = FRMHASHTAG_BOL_Service::getInstance()->findTagsInAdvanceSearchPlugin($searchValue,$first,$count);
        }

        $count = 0;

        foreach($topics as $item){
            $itemInformation = array();
            $itemInformation['title'] = $item['tag'];
            $itemInformation['id'] = $item['tag'];
            $itemInformation['count'] = (int) $item['count'];
            $itemInformation['link'] = OW::getRouter()->urlForRoute('frmhashtag.tag', array('tag' => $item['tag']));
            $itemInformation['label'] = OW::getLanguage()->text('frmadvancesearch', 'hashtag_label');
            $itemInformation['emptyImage'] = true;
            $itemInformation['image'] = FRMHASHTAG_BOL_Service::getInstance()->generateDefaultImageUrl();
            $result[] = $itemInformation;
            $count++;
            if($count == $maxCount){
                break;
            }
        }

        $data = $event->getData();
        $data['hashtags']= array('label' => OW::getLanguage()->text('frmadvancesearch', 'hashtag_label'), 'data' => $result);
        $event->setData($data);
    }
}
