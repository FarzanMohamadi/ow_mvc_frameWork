<?php
/**
 * frmemoji
 */
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmemoji
 * @since 1.0
 */
class FRMEMOJI_CLASS_EventHandler
{
    private static $classInstance;

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

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_RENDER_STRING, array($this, 'onBeforeTextRender'));
        $eventManager->bind('emoji.before_render_string', array($this, 'onBeforeTextRender'));
        $eventManager->bind('base.comment_item_process', array($this, 'renderComments'));
        $eventManager->bind('frm.replace.unicode.emoji', array($this,'replace_emoji'), 10000);
        $eventManager->bind('emoji.replace_text_emoji', array($this, 'replace_text_emoji'));
    }

    private function getEmojiType(){
        $emojiType = 'emojione';
        if(OW::getConfig()->configExists('frmemoji','emojiType')){
            $emojiType = OW::getConfig()->getValue('frmemoji','emojiType');
        }
        return $emojiType;
    }
    public function getEmojiFilePath(){
        $bg_photo_addr = OW::getPluginManager()->getPlugin('frmemoji')->getStaticCssUrl() . 'sheet_' . $this->getEmojiType() . '_64_indexed_128.png';
        return $bg_photo_addr;
    }

    public function onBeforeDocumentRender(OW_Event $event)
    {
        //emoji+wdt
        $cssFile = OW::getPluginManager()->getPlugin('frmemoji')->getStaticCssUrl() . 'wdt-emoji-bundle.css';
        OW::getDocument()->addStyleSheet($cssFile);

        $jsFile = OW::getPluginManager()->getPlugin('frmemoji')->getStaticJsUrl() . 'emoji.min.js';
        OW::getDocument()->addScript($jsFile);
        $jsFile = OW::getPluginManager()->getPlugin('frmemoji')->getStaticJsUrl() . 'wdt-emoji-bundle.js';
        OW::getDocument()->addScript($jsFile);

        $lbl_search = OW::getLanguage()->text('frmemoji', 'search');
        $lbl_search_results = OW::getLanguage()->text('frmemoji', 'search_results');
        $lbl_no_emoji_found = OW::getLanguage()->text('frmemoji', 'no_emoji_found');
        $types = '<a class="wdt-emoji-tab active" data-group-name="Recent"></a><a class="wdt-emoji-tab" data-group-name="People"></a><a class="wdt-emoji-tab" data-group-name="Nature"></a>             <a class="wdt-emoji-tab" data-group-name="Foods"></a>            <a class="wdt-emoji-tab" data-group-name="Activity"></a>            <a class="wdt-emoji-tab" data-group-name="Places"></a>            <a class="wdt-emoji-tab" data-group-name="Objects"></a>           <a class="wdt-emoji-tab" data-group-name="Symbols"></a>            <a class="wdt-emoji-tab" data-group-name="Flags"></a>';//            <a class="wdt-emoji-tab" data-group-name="Custom"></a>';
        $html = '<div class="wdt-emoji-popup" style="z-index: 111;"><a href="#" class="wdt-emoji-popup-mobile-closer"> &times; </a>	<div class="wdt-emoji-menu-content">'
            .'<div id="wdt-emoji-menu-header">'.$types.'</div><div class="wdt-emoji-scroll-wrapper"><div id="wdt-emoji-menu-items">'
            .'<input id="wdt-emoji-search" type="text" placeholder="'.$lbl_search.'">'
            .'<h3 id="wdt-emoji-search-result-title">'.$lbl_search_results.'</h3><div class="wdt-emoji-sections"></div>'
            .'<div id="wdt-emoji-no-result">'.$lbl_no_emoji_found.'</div></div></div>'
            .'<div id="wdt-emoji-footer" style="display: none"><div id="wdt-emoji-preview"><span id="wdt-emoji-preview-img"></span><div id="wdt-emoji-preview-text"><span id="wdt-emoji-preview-name"></span><br><span id="wdt-emoji-preview-aliases"></span></div></div>'
            .'<div id="wdt-emoji-preview-bundle"><span>Emoji Bundle</span></div></div></div></div>';
        OW::getDocument()->addOnloadScript('$("body").append(\''.$html.'\');');

        $lang = OW::getLanguage();
        $lang->addKeyForJs('frmemoji', 'label_recent');
        $lang->addKeyForJs('frmemoji', 'label_people');
        $lang->addKeyForJs('frmemoji', 'label_nature');
        $lang->addKeyForJs('frmemoji', 'label_foods');
        $lang->addKeyForJs('frmemoji', 'label_activity');
        $lang->addKeyForJs('frmemoji', 'label_places');
        $lang->addKeyForJs('frmemoji', 'label_objects');
        $lang->addKeyForJs('frmemoji', 'label_symbols');
        $lang->addKeyForJs('frmemoji', 'label_flags');

        $emojiType = $this->getEmojiType();
        $bg_photo_addr = $this->getEmojiFilePath();
        OW::getDocument()->addStyleDeclaration('.emj, span.emoji-inner {background-image: url(' . $bg_photo_addr . ') !important; }');

        $js = "
    var base_addr = '".OW::getPluginManager()->getPlugin('frmemoji')->getStaticCssUrl()."';
    wdtEmojiBundle.defaults.emojiSheets = {
        'apple'    : base_addr+'sheet_apple_64_indexed_128.png',
        'google'   : base_addr+'sheet_emojione_64_indexed_128.png',
        'twitter'  : base_addr+'sheet_emojione_64_indexed_128.png',
        'emojione' : base_addr+'sheet_emojione_64_indexed_128.png',
        'facebook' : base_addr+'sheet_emojione_64_indexed_128.png',
        'messenger': base_addr+'sheet_emojione_64_indexed_128.png'
    };
    wdtEmojiBundle.defaults.type = '$emojiType';
       
    function check_inputs(){
        $('.ow_newsfeed_status_input,.comments_fake_autoclick, #dialogTextarea, #about_me_widget_input').each(function() {
            if(parseInt($(this).attr('emoji-loaded'))!='y'){
                $(this).addClass('emoji-input');
                $(this).attr('emoji-loaded', 'y');
            }
        });
        wdtEmojiBundle.init('.emoji-input');
    }
    function render_emojis(){
        $('.ow_newsfeed_body > .ow_newsfeed_string, .ow_telegram_list .ow_ipc_content>span, .ow_telegram_widget .ow_ipc_content>span, #dialogMessageText, .ow_mailbox_message_content, .ow_mailbox_subject_text, #conversationItemPreviewText ').each(function() {
            if($('img.emj',this).length>0){
                return;
            }
            if(parseInt($(this).attr('emoji'))!=$(this)[0].innerHTML.length){
                var new_html = wdtEmojiBundle.render((($(this)[0].innerHTML).replace('<',' <')+' ').replace('&n',' &n'));
                if(new_html.length > $(this)[0].innerHTML.length+10 )
                    $(this)[0].innerHTML=new_html;
                $(this).attr('emoji', $(this)[0].innerHTML.length);
            }
        });
    }
    
    setInterval(function() {
        check_inputs();
        render_emojis();
    }, 1000);
    
    check_inputs();
    render_emojis();
    ";
        OW::getDocument()->addOnloadScript('$(function(){'.$js.'});');
    }

    public function onBeforeTextRender(OW_Event $event){
        $data = $event->getData();
        $params = $event->getParams();
        if(isset($data['string'])) {
            $string = $data['string'];
        }else if (isset($params['string'])) {
            $string = $params['string'];
        }
        if(!isset($string))
        {
            return;
        }
        $inline_styles = false;
        if(isset($params['inline_styles'])) {
            $inline_styles = $params['inline_styles'];
        }
        $renderer = new FRMEMOJI_CLASS_BackendRender($inline_styles);
        $data['string'] = $renderer->render($string);
        $event->setData($data);
    }

    public function renderComments(BASE_CLASS_EventProcessCommentItem $event){
        $string = $event->getDataProp('content');
        $renderer = new FRMEMOJI_CLASS_BackendRender(false);
        $string = $renderer->render($string);
        $event->setDataProp('content', $string);
    }

    public function replace_emoji(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['text'])) {
            $correctedText = FRMEMOJI_CLASS_BackendRender::replace_utf8($params['text']);
            $event->setData(array('correctedText'=>$correctedText));
        }
    }

    public function replace_text_emoji(OW_Event $event)
    {
        $params = $event->getParams();
        if(isset($params['text'])) {
            $correctedText = FRMEMOJI_CLASS_BackendRender::replace_text_emojiImg($params['text']);
            $event->setData(array('correctedText'=>$correctedText));
        }
    }
}