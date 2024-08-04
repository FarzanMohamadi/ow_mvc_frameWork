<?php
/**
 * Notification
 *
 * @package ow_plugins.notifications.components
 * @since 1.0
 */
class NOTIFICATIONS_CMP_Notification extends OW_Component
{
    private $items = array();
    private $userId;
    private $unsubscribeAction;
    private $unsubscribeCode;

    const NL_PLACEHOLDER = '%%%nl%%%';
    const TAB_PLACEHOLDER = '%%%tab%%%';
    const SPACE_PLACEHOLDER = '%%%space%%%';

    public function __construct( $userId )
    {
        parent::__construct();

        $this->userId = $userId;
    }

    public function addItem( $notification )
    {
        $this->items[] = $this->processDataInterface($notification);
        $this->unsubscribeAction = count($this->items) == 1 ? $notification['action'] : 'all';
    }

    private function processDataInterface( $item )
    {
        $data = $item['data'];

        foreach ( array('string', 'conten') as $langProperty )
        {
            if ( !empty($data[$langProperty]) && is_array($data[$langProperty]) )
            {
                $key = explode('+', $data[$langProperty]['key']);
                $vars = empty($data[$langProperty]['vars']) ? array() : $data[$langProperty]['vars'];
                $data[$langProperty] = OW::getLanguage()->text($key[0], $key[1], $vars);
            }
        }

        if ( !empty($data['contentImage']) )
        {
            $data['contentImage'] = is_string($data['contentImage'])
                ? array( 'src' => $data['contentImage'] )
                : $data['contentImage'];
        }
        else
        {
            $data['contentImage'] = null;
        }

        $sentenceCorrected = false;
        if (!empty($data['content']) && mb_strlen($data['content']) > 300 )
        {
            $sentence = $data['content'];
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_HALF_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::PARTIAL_SPACE_CODE_DISPLAY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
            $event = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::HTML_ENTITY_CORRECTION, array('sentence' => $sentence, 'trimLength' => 300)));
            if(isset($event->getData()['correctedSentence'])){
                $sentence = $event->getData()['correctedSentence'];
                $sentenceCorrected = true;
            }
        }
        if($sentenceCorrected){
            $data['content'] = $sentence.'...';
        }else{
            $data['content'] = empty($data['content']) ? '' : UTIL_String::truncate($data['content'], 300, '...');
        }

        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['content'], 'inline_styles' => true)));
        if(isset($stringRenderer->getData()['string'])){
            $data['content'] = ($stringRenderer->getData()['string']);
        }

        $data['string'] = empty($data['string']) ? '' : $data['string'];
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $data['string'], 'inline_styles' => true)));
        if(isset($stringRenderer->getData()['string'])){
            $data['string'] = ($stringRenderer->getData()['string']);
        }

        $data['avatar'] = empty($data['avatar']) ? null : $data['avatar'];
        $data['contentImage'] = empty($data['contentImage']) ? array() : $data['contentImage'];
        $data['toolbar'] = empty($data['toolbar']) ? array() : $data['toolbar'];
        $data['url'] = empty($data['url']) ? null : $data['url'];
        $data['time'] = $item['time'];

        return $data;
    }

    private function itemsPrepare()
    {
        $out = array();

        foreach ( $this->items as $item )
        {
            $date = getdate($item['time']);
            $timeKey = mktime(0, 0, 0, $date['mon'], $date['mday'], $date['year']);
            $out[$timeKey][$item['time']] = $item;
        }

        return $out;
    }

    public function setUnsubscribeCode( $code )
    {
        $this->unsubscribeCode = $code;
    }

    private function getUnsubscribeUrl( $all = false )
    {
        return OW::getRouter()->urlForRoute('notifications-unsubscribe', array(
            'code' => $this->unsubscribeCode,
            'action' => $all ? "all" : $this->unsubscribeAction
        ));
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $items = $this->itemsPrepare();

        $this->assign('items', $items);
        $this->assign('userUrl', BOL_UserService::getInstance()->getUserUrl($this->userId));
        $this->assign('userName', BOL_UserService::getInstance()->getDisplayName($this->userId));
        $this->assign('unsubscribeUrl', $this->getUnsubscribeUrl());
        $this->assign('unsubscribeAllUrl', $this->getUnsubscribeUrl(true));

        $single = $this->unsubscribeAction != 'all';
        $this->assign('single', $single);

        $this->assign('settingsUrl', OW::getRouter()->urlForRoute('notifications-settings'));
    }

    public function getSubject()
    {
        if ( count($this->items) == 1 )
        {
            $item = reset($this->items);

            return strip_tags($item['string']);
        }

        return OW::getLanguage()->text('notifications', 'email_subject');
    }

    public function getHtml()
    {
        $template = OW::getPluginManager()->getPlugin('notifications')->getCmpViewDir() . 'notification_html.html';
        $this->setTemplate($template);

        $site_url = OW_URL_HOME;
        if(substr($site_url, -1)=='/'){
            $site_url = substr($site_url, 0, -1);
        }
        $this->assign('my_site_url',$site_url);

        return parent::render();
    }

    public function getTxt()
    {
        $template = OW::getPluginManager()->getPlugin('notifications')->getCmpViewDir() . 'notification_txt.html';
        $this->setTemplate($template);

        $this->assign('nl', '%%%nl%%%');
        $this->assign('tab', '%%%tab%%%');
        $this->assign('space', '%%%space%%%');

        $content = parent::render();
        $search = array('%%%nl%%%', '%%%tab%%%', '%%%space%%%');
        $replace = array("\n", '    ', ' ');

        return str_replace($search, $replace, $content);
    }
}