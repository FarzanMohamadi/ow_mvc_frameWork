<?php
class FRMNEWSFEEDPLUS_BOL_Service
{
    private static $classInstance;

    const ORDER_BY_ACTIVITY='activity';
    const ORDER_BY_ACTION='action';
    const FORWARDABLE_TYPES = array('user-status', 'groups-status', 'photo_comments','multiple_photo_upload');

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

    /***
     * @param OW_Event $event
     */
    public function addAttachmentInputFieldsToNewsfeed(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['form'])) {
            $form = $this->addAttachmentInputsFieldToForm($params['form']);
        }

        $this->attachmentRender($event, 'newsfeed');
        $uid = FRMSecurityProvider::generateUniqueId();
        $attachmentCmp = new BASE_CLASS_FileAttachment('frmnewsfeedplus', $uid);
        $attachmentCmp->setInputSelector('#newsfeedplusAttachmentsBtn');
        $attachmentCmp->setDropAreasSelector('form[name="newsfeed_update_status"]');
        $params['component']->addComponent('attachments', $attachmentCmp);
    }

    /***
     * @param $form
     * @param null $dataValue
     * @return mixed
     */
    public function addAttachmentInputsFieldToForm($form, $dataValue = null)
    {
        $attachmentFileData = new HiddenField('attachment_feed_data');
        $attachmentFileData->addAttribute("id", "attachment_feed_data");
        $attachmentFileData->setValue($dataValue);
        $form->addElement($attachmentFileData);

        $attachmentPreviewData = new HiddenField('attachment_preview_data');
        $attachmentPreviewData->addAttribute("id", "attachment_preview_data");
        $attachmentPreviewData->setValue($dataValue);
        $form->addElement($attachmentPreviewData);

        return $form;
    }

    public function attachmentRender(OW_Event $event, $type = "")
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticJsUrl() . 'frmnewsfeedplus.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmnewsfeedplus")->getStaticCssUrl() . 'frmnewsfeedplus.css');
        if ($type == "newsfeed") {
            if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE) {

                OW::getDocument()->addOnloadScript('$(\'.owm_newsfeed_block .owm_newsfeed_status_update_add_cont \').append(\'<span class="ow_smallmargin frmnewsfeedplus_attachment"><span class="frmnewsfeedplus_attachment" onclick="addAttachment()"><span class="buttons clearfix"><a class="frmnewsfeedplus_attachment" id="newsfeedplusAttachmentsBtn"></a></span></span></span>\');');
            } else {
                OW::getDocument()->addOnloadScript('$(\'.ow_status_update_btn_block .ow_attachment_icons\').append(\'<span class="ow_smallmargin frmnewsfeedplus_attachment"><span class="frmnewsfeedplus_attachment" onclick="addAttachment()"><span class="buttons clearfix"><a class="frmnewsfeedplus_attachment" id="newsfeedplusAttachmentsBtn"></a></span></span></span>\');');
            }
        }
        $css = '
            .frmnewsfeedplus_attachment{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticUrl() . 'img/attachment.svg' . '");}
            ';
        OW::getDocument()->addStyleDeclaration($css);
    }


    public function saveAttachments(OW_Event $event){
        $data = $event->getData();
        $attachmentDao = BOL_AttachmentDao::getInstance();
        $attachmentService = BOL_AttachmentService::getInstance();
        if ( isset($_POST['attachment_feed_data']) && !empty($_POST['attachment_feed_data']) ) {
            $attachmentIds=array();
            $previewIdList=array();
            $attachmentData = $_POST['attachment_feed_data'];
            $attachmentsArray = explode('-', $attachmentData);
            if ( isset($_POST['attachment_preview_data']) && !empty($_POST['attachment_preview_data']) ) {
                $attachmentsPreviewArray = explode('-', $_POST['attachment_preview_data']);
            }
            foreach ($attachmentsArray as $attachment){
                $attachmentSplit = explode(':', $attachment);
                if(!isset($attachmentSplit) || !isset($attachmentSplit[1])){
                    continue;
                }
                $file = $attachmentDao->findById($attachmentSplit[1]);
                if(!isset($file) || $file->userId!=OW::getUser()->getId()){
                    continue;
                }
                $attachmentService->updateStatusForBundle('frmnewsfeedplus',$file->bundle,1);
                if(isset($attachmentsPreviewArray) && in_array($attachmentSplit[1],$attachmentsPreviewArray))
                {
                    $previewIdList[]=$attachmentSplit[1];
                }
                $attachmentIds[]=$attachmentSplit[1];
            }
            if(sizeof($attachmentIds) > 0) {
                $data["attachmentIdList"] = $attachmentIds;
            }
            if(sizeof($previewIdList) > 0) {
                $data["previewIdList"] = $previewIdList;
            }
            $event->setData($data);
        }
    }

    public function appendAttachmentsToFeed(OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();

        if (!isset($params["data"]["attachmentIdList"])) {
            return;
        }

        $attachmentIdList = $params["data"]["attachmentIdList"];
        $previewIdList = array();
        if (isset($params["data"]["previewIdList"])) {
            $previewIdList = $params["data"]["previewIdList"];
        }
        if (sizeof($attachmentIdList) == 0 )
        {
            return;
        }

        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticJsUrl() . 'photoswipe.min.js');
        OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticJsUrl() . 'photoswipe-ui-default.min.js');
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticCssUrl() . 'photoswipe.min.css');
        OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticCssUrl() . 'default-skin.min.css');
        OW::getLanguage()->addKeyForJs('frmnewsfeedplus', 'download');

        $attachmentDao = BOL_AttachmentDao::getInstance();
        $attachmentsList = array();
        if (isset($params['data']['cache']['attachments'])) {
            $cachedAttachments = $params['data']['cache']['attachments'];
            foreach ($cachedAttachments as $key => $cachedAttachment) {
                if (in_array($key, $attachmentIdList)) {
                    $attachmentsList[] = $cachedAttachment;
                }
            }
        } else {
            $attachmentsList = $attachmentDao->findByIdList($attachmentIdList);
        }

        $attachmentPreviewItems = array();
        $attachmentNoPreviewItems = array();

        foreach ($attachmentsList as $attachment) {
            $itemType = FRMSecurityProvider::getAttachmentExtensionType($attachment);
            if ($itemType != '' && in_array($attachment->id,$previewIdList)) {
                $attachmentPreviewItems[] = $attachment;
            } else {
                $attachmentNoPreviewItems[] = $attachment;
            }
        }
        $itemsAttachmentPreviewData = new FRMNEWSFEEDPLUS_CMP_RenderAttachmentPreview($attachmentPreviewItems, $params);
        $AttachmentsPreviewHtml = $itemsAttachmentPreviewData->render();
        $attachmentItemsNoPreview = new FRMNEWSFEEDPLUS_CMP_RenderAttachmentNoPreview($attachmentNoPreviewItems, $params);
        $AttachmentsNoPreviewHtml = $attachmentItemsNoPreview->render();

        $data["attachmentHTML"] = $AttachmentsPreviewHtml . $AttachmentsNoPreviewHtml;
        $event->setData($data);

        OW::getDocument()->addOnloadScript('thumbnailCreator();');
        FRMSecurityProvider::addMediaElementPlayerAfterRender();
    }

    public function onBeforeActionDelete( OW_Event $event )
    {
        $params = $event->getParams();
        $attachmentService = BOL_AttachmentService::getInstance();
        if(isset($params['actionId'])) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findById($params['actionId']);
            $newsfeedData = json_decode($action->data);
            if(isset($newsfeedData->attachmentIdList)){
                foreach ($newsfeedData->attachmentIdList as $attachmentId) {
                    $attachmentService->deleteAttachmentById($attachmentId);
                    $this->deleteThumbnailsById($attachmentId);
                }
            }
        }
    }

    public function deleteThumbnailsById($attachmentId){
        $thumbnail=$this->getThumbnailFileDir($attachmentId.'.png');
        if(OW::getStorage()->fileExists($thumbnail)){
            OW::getStorage()->removeFile($thumbnail);
        }
    }

    public function onBeforeUpdateStatusFormRenderer(OW_Event $event){
        $params = $event->getParams();
        if(isset($params['form'])) {
            $form = $params['form'];
            $form->bindJsFunction(Form::BIND_SUCCESS, "function(data){refreshAttachClass();}");

        }
    }

    public function onFeedItemAddRoleData(OW_Event $event)
    {
        $data = $event->getData();
        $params = $event->getParams();
        if (isset($params['usersInfo']) && isset($params['usersInfo']["roleLabels"]) &&
            isset($params['usersInfo']["roleLabels"][$params['userId']])) {
            $data["roleLabel"] = $params['usersInfo']["roleLabels"][$params['userId']];
            $event->setData($data);
        }
    }
    public function newsfeedDefualtLinkIconRenderer(OW_Event $event){
        $eventData = $event->getParams();
        if($eventData['data']['content']['thumbnail_url']==null && $eventData['data']['content']['type']=='link'){
            $eventData['data']['content']["thumbnail_url"]=OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticUrl().'img/defualt.svg';
        }
        $event->setData(array('data' => $eventData));
    }

    public function getCreatorActivityOfAction($entityType, $entityId, $action = null){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return null;
        }

        if ($action == null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
        }
        if($action == null){
            return null;
        }
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($action->getId()));
        foreach($activities as $activityId){
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if($activity->activityType=='create'){
                return $activity;
            }
        }
        return null;
    }

    public function getCreatorActivityOfActionById($actionId){
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return null;
        }

        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionId));
        foreach($activities as $activityId){
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if($activity->activityType=='create'){
                return $activity;
            }
        }
        return null;
    }


    public function editPost($text, $eid, $etype){
        if(!$this->canEditPost($eid, $etype)){
            return array('actionId' => -1, 'status' => '');
        }

        $text = strip_tags($text);

        $text = json_encode($text);
        $text = str_replace('\u202b', '', $text);
        $text = json_decode($text);
        $originalText = $text;
        $renderedText = $text;
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $renderedText)));
        if (isset($stringRenderer->getData()['string'])) {
            $renderedText = ($stringRenderer->getData()['string']);
        }

        $text = UTIL_HtmlTag::autoLink($text);
        //$text = nl2br($text);

        $renderedText = UTIL_HtmlTag::autoLink($renderedText);
        $renderedText = nl2br($renderedText);

        $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($etype, $eid);
        $actionData = $action->data;
        $oldText = '';
        $actionJsonData = json_decode($actionData);

        if(empty($text)){

            if(isset($_POST['entityType']) && isset($_POST['entityId']) && (isset($_POST['tags']) ||  isset($_POST['products']))){
                $text = $actionJsonData->status;
                $renderedText =  nl2br($text);
            }else{
                return array('actionId' => -1, 'status' => '');
            }
        }

        $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";
        preg_match($reg_exUrl, $text, $urls);
        if(empty($urls) || ($etype != 'user-status' && $etype !='groups-status') ){
            if(isset($actionJsonData->content->vars->status)){
                $actionJsonData->content->vars->status = $text;
            }

            if(isset($actionJsonData->content->vars->title)){
                unset($actionJsonData->content->vars->title);
            }

            if(isset($actionJsonData->content->vars->description)){
                unset($actionJsonData->content->vars->description);
            }

            if(isset($actionJsonData->content->vars->url)){
                unset($actionJsonData->content->vars->url);
            }

            if(isset($actionJsonData->content->vars->image)){
                unset($actionJsonData->content->vars->image);
            }

            if(isset($actionJsonData->content->vars->thumbnail)){
                unset($actionJsonData->content->vars->thumbnail);
            }

            if( ($etype == 'user-status' || $etype =='groups-status') && $action->pluginKey == 'newsfeed' && isset($actionJsonData->content->format)){
                $actionJsonData->content->format = 'text';
            }

            $action->format = 'text';
        }
        //url detected and needs update
        elseif(!empty($urls) && ($etype == 'user-status' || $etype =='groups-status') &&
            ( (isset($actionJsonData->content->vars->url) && $urls[0] != $actionJsonData->content->vars->url)
            || $actionJsonData->content->format == 'text') ){
            $url = $urls[0];
            $urlInfo = parse_url($url);
            if ( empty($urlInfo['scheme']) ){
                $url = 'http://' . $url;
            }
            $url = str_replace("'", '%27', $url);
            $oembed = UTIL_HttpResource::getOEmbed($url);
            if(isset($oembed)){
                $event = new OW_Event('frmsecurityessentials.on.after.read.url.embed', array('stringToFix' => $oembed['title']));
                OW::getEventManager()->trigger($event);
                if (isset($event->getData()['fixedString'])) {
                    $oembed['title'] = $event->getData()['fixedString'];
                }
                $event = new OW_Event('frmsecurityessentials.on.after.read.url.embed', array('stringToFix' => $oembed['description']));
                OW::getEventManager()->trigger($event);
                if (isset($event->getData()['fixedString'])) {
                    $oembed['description'] = $event->getData()['fixedString'];
                }
                unset($oembed['allImages']);
                unset($actionJsonData->content->vars);
                $actionJsonData->content->vars = new \stdClass();
                $contentHref = empty($oembed["href"]) ? null : $oembed["href"];
                $actionJsonData->content->vars->url = empty($oembed["url"]) ? $contentHref : $oembed["url"];
                $actionJsonData->content->vars->title = $oembed["title"];
                $actionJsonData->content->vars->description = $oembed["description"];


                if (empty($oembed["thumbnail_url"])) {
                    $actionJsonData->content->format = "content";
                    $action->format = "content";
                } else {
                    $actionJsonData->content->format = "image_content";
                    $action->format = "image_content";
                    $actionJsonData->content->vars->image = $oembed["thumbnail_url"];
                    $actionJsonData->content->vars->thumbnail = $oembed["thumbnail_url"];
                }
            }else{
                OW::getLogger()->writeLog(OW_Log::ERROR, 'oembed_null_for_url', ['url'=>$url]);
            }
        }

        if(isset($actionJsonData->data->status)) {
            $oldText = $actionJsonData->data->status;
            $actionJsonData->data->status = $text;
        }
        if(isset($actionJsonData->status)) {
            $oldText = $actionJsonData->status;
            $actionJsonData->status = $text;
        }

        if(isset($_POST['tags'])){
            $actionJsonData->tags = $_POST['tags'];
        }

        if(isset($_POST['products'])){
            $productsList = json_decode( $_POST['products'] );
            if(is_array($productsList)){
                foreach ($productsList as $item){
                    $item = (array) $item;
                    if(!isset($item['positionX'])  || !isset($item['positionY'])  || !isset($item['productId']) ){
                        return array('valid' => false, 'message' => 'invalid_product_info');
                    }
                    $productNew[] = $item;
                }
                $actionJsonData->products = json_encode( $productNew );
            }
        }


        $action->data = json_encode($actionJsonData);
        NEWSFEED_BOL_ActionDao::getInstance()->save($action);
        if($action->format == "image_content" || $action->format == "content"){
            $actionJsonData->content->vars->status = $renderedText;
            $cmp = OW::getClassInstance("NEWSFEED_CMP_FeedItem", new NEWSFEED_CLASS_Action(), array());
            $content = array("format" => $actionJsonData->content->format, "vars" => (array)$actionJsonData->content->vars);
            $renderedText = $cmp->renderContent($content);
        }
        OW::getLogger()->writeLog(OW_Log::INFO, 'edit_action', ['actionType'=>OW_Log::UPDATE, 'enType'=>'newsfeed', 'enId'=>$action->id]);
        OW::getEventManager()->trigger(new OW_Event('hashtag.edit_newsfeed', array('entityId' => $eid,'entityType' => $etype,'text'=>$originalText,'pluginKey'=>'newsfeed')));
        OW::getEventManager()->trigger(new OW_Event('newsfeed.edit_post', array('actionId' => $action->getId(), 'status' => $renderedText, 'text' => $text, 'entityId' => $eid,'entityType' => $etype, 'oldText' => $oldText,'pluginKey'=>'newsfeed')));
        return array('actionId' => $action->getId(), 'status' => $renderedText, 'text' => $text);
    }

    public function getText($eid, $etype, $getFullText=false, $action = null){
        if ($action == null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($etype, $eid);
        }
        $data = $action->data;
        $data = json_decode($data);
        if(!isset($data->data->status)){
            return '';
        }
        $status = $data->data->status;
        if ($getFullText)
            $status = FRMSecurityProvider::getDomTextContent($status);
        $status = strip_tags($status);
        return $status;
    }


    public function getEditPostForm($text, $eid, $etype){
        $form = new Form('edit_post');
        $form->setAjax(true);
        $action = NEWSFEED_BOL_Service::getInstance()->findAction($etype,$eid);
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(!data.error){closeEditNewsfeedComponent(data.status, data.actionId);OW.trigger(\'base.newsfeed_content.edited\', {entityType:\''.$etype.'\',entityId:\''.$eid.'\',itemId:\''.$action->getId().'\'});OW.info("'. OW::getLanguage()->text('frmnewsfeedplus', 'edit_post_successfully') .'");}else{OW.error("Parser error");}}');
        $actionRoute = OW::getRouter()->urlForRoute('frmnewsfeedplus.edit.post');
        $form->setAction($actionRoute);

        $field = new Textarea('status');
        $field->setId('newsfeed_update_status_info_edit_id');
        $field->setRequired();
        $field->setValue($text);
        $form->addElement($field);

        $field = new HiddenField('eid');
        $field->setValue($eid);
        $form->addElement($field);

        $field = new HiddenField('etype');
        $field->setValue($etype);
        $form->addElement($field);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('base', 'ow_ic_save'));
        $form->addElement($submit);

        return $form;
    }

    public function canEditPost($eid, $etype, $action = null, $creatorActivity = null){
        if ($eid == null || $etype == null || empty($eid) || empty($etype) ){
            return false;
        }

        if(OW::getUser()->isAdmin()){
            return true;
        }

        if(!in_array($etype, array('user-status', 'groups-status', 'photo_comments'))){
            return false;
        }

        if ($action == null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($etype, $eid);
        }
        if($action == null){
            return false;
        }

        if($action->pluginKey != 'newsfeed'){
            return false;
        }

        $activity = $creatorActivity;
        if ($activity == null) {
            $activity = $this->getCreatorActivityOfAction($etype, $eid, $action);
        }
        if($activity == null){
            return false;
        }

        $isFeedOwner = $activity->userId == OW::getUser()->getId();
        if(!$isFeedOwner){
            return false;
        }

        return true;
    }

    public function genericItemRender(OW_Event $event)
    {
        if(!FRMSecurityProvider::checkPluginActive('newsfeed', true)){
            return;
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticJsUrl() . 'frmnewsfeedplus.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmnewsfeedplus")->getStaticCssUrl() . 'frmnewsfeedplus.css');

        $params = $event->getParams();
        $data = $event->getData();

        $group = null;
        if (isset($params['group'])) {
            $group = $params['group'];
        }
        if (isset($params['action']['data']['contextFeedType'])) {
            $entityId = $params['action']['data']['contextFeedId'];
            $entityType = $params['action']['data']['contextFeedType'];
            if ($entityType == 'groups' && isset($params['cache']['groups'][$entityId])) {
                $group = $params['cache']['groups'][$entityId];
            }
        }

        if(!isset($params['action']['entityId']) || !isset($params['action']['entityType'])){
            return;
        }

        $entityId = null;
        $entityType = null;
        $canEdit = false;
        if(isset($params['action']) &&
            isset($params['action']['userId']) &&
            isset($params['action']['entityType']) &&
            isset($params['action']['entityId'])){

            $entityId = $params['action']['entityId'];
            $entityType = $params['action']['entityType'];
            $action = null;
            $activity = null;
            if (isset($params['cache']['actions_by_entity'][$entityType . '-' . $entityId])) {
                $action = $params['cache']['actions_by_entity'][$entityType . '-' . $entityId];
                if ($action != null && isset($params['cache']['activity_creator'])) {
                    $activity = $params['cache']['activity_creator'][$action->id];
                }
            }
            if($this->canEditPost($entityId, $entityType, $action, $activity)){
                $canEdit = true;
            }
        }

        $action = null;
        if (isset($params['cache']['actions_by_entity'][$entityType . '-' . $entityId])) {
            $action = $params['cache']['actions_by_entity'][$entityType . '-' . $entityId];
        }

        $oldText = $this->getText($entityId, $entityType, false, $action);
        if(empty($oldText)){
            $canEdit = false;
        }

        if ($canEdit) {
            array_unshift($data['contextMenu'], array(
                'label' => OW::getLanguage()->text('frmnewsfeedplus', 'edit_post'),
                "class" => "newsfeed_edit_btn",
                'attributes' => array(
                    'onclick' => 'showEditNewsfeedComponent($(this).data().eid, $(this).data().etype)',
                    "data-etype" => $entityType,
                    "data-eid" => $entityId
                )
            ));
        }
        if ($group != null && !isset($params['group'])) {
            $params['group'] = $group;
        }
        $this->addForwardFeature($data['contextMenu'],$params['feedType'],$params['action']['id'],$entityType,$entityId, $params);
        $event->setData($data);
    }

    /***
     * @param $entityType
     * @param $entityId
     * @param $feedType
     * @param array $params
     * @return bool
     * @throws Redirect404Exception
     */
    public function canForwardPost($entityType, $entityId, $feedType, $params = array())
    {
        if(!isset($feedType))
        {
            return false;
        }
        return $this->canForwardPostByEntityIdAndEntityType($entityType, $entityId, $params);
    }

    /***
     * @param $entityType
     * @param $entityId
     * @param array $params
     * @return bool
     * @throws Redirect404Exception
     */
    public function canForwardPostByEntityIdAndEntityType($entityType, $entityId, $params = array())
    {
        $action = null;
        $activity = null;
        $group = null;
        $cache = array();
        if (isset($params['cache'])) {
            $cache = $params['cache'];
        }
        if (isset($params['params']['cache'])) {
            $cache = $params['params']['cache'];
        }
        if (isset($params['action']) && $params['action'] instanceof NEWSFEED_BOL_Action) {
            $action = $params['action'];
        }
        if (isset($params['activity']) && $params['activity'] instanceof NEWSFEED_BOL_Activity) {
            $activity = $params['activity'];
        }
        if (isset($params['createActivity']) && $params['createActivity'] instanceof NEWSFEED_BOL_Activity) {
            $activity = $params['createActivity'];
        }
        if (isset($params['group'])) {
            $group = $params['group'];
        }
        if(!isset($entityId)  || !OW::getUser()->isAuthenticated())
        {
            return false;
        }
        if(!in_array($entityType, self::FORWARDABLE_TYPES)){
            return false;
        }

        /*
         * check if action exists
         */
        if (isset($cache['actions_by_entity'][$entityType . '-' . $entityId])) {
            $action = $cache['actions_by_entity'][$entityType . '-' . $entityId];
        }
        if ($action == null) {
            $action = NEWSFEED_BOL_ActionDao::getInstance()->findAction($entityType, $entityId);
        }
        if($action == null){
            return false;
        }

        if($action->pluginKey != 'newsfeed'){
            return false;
        }

        /*
         * check if creation activity
         */
        if ($activity == null) {
            $activity = $this->getCreatorActivityOfAction($entityType, $entityId, $action);
        }
        if($activity == null){
            return false;
        }

        /*
         * check action feed
         */
        $actionFeed = null;
        if (isset($activity->feed_object)) {
            $actionFeed = $activity->feed_object;
        }
        if ($actionFeed == null) {
            if (isset($cache['feed_by_creator_activity']) && array_key_exists($activity->id, $cache['feed_by_creator_activity'])) {
                if (isset($cache['feed_by_creator_activity'][$activity->id])) {
                    $actionFeed = $cache['feed_by_creator_activity'][$activity->id];
                }
            } else {
                $actionFeed = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds(array($activity->id))[0];
            }
        }
        if(!isset($actionFeed))
        {
            return false;
        }

        /*
         * check newsfeed belongs to a group or a user
         */
        if($actionFeed->feedType!='groups' && $actionFeed->feedType!='user')
        {
            return false;
        }

        if($actionFeed->feedType=='groups') {
            /*
             * check if group plugin is active
             */
            if (!FRMSecurityProvider::checkPluginActive('groups')) {
                return;
            }
            /*
             * check if source group exists
             */
            if ($group == null) {
                $group = GROUPS_BOL_Service::getInstance()->findGroupById($actionFeed->feedId);
            }
            if (!isset($group)) {
                return;
            }

            /*
             * check if current user has access to source group
             */

            $canView = GROUPS_BOL_Service::getInstance()->isCurrentUserCanView($group, false, $params);
            if (!$canView) {
                return false;
            }
            return true;
        }
        else if($actionFeed->feedType=='user') {
            /*
             * check if current user is owner of the activity
             */
            if ($activity->userId == OW::getUser()->getId()) {
                return true;
            }
            /*
             * check if current user has access to this activity
             */
            $activityOwnerId = $activity->userId;
            $activityPrivacy = $activity->privacy;

            /*
             * activity is private
             */
            if ($activity->userId != OW::getUser()->getId())
            {
                switch ( $activityPrivacy)
                {
                    case 'only_for_me' :
                        return false;
                        break;
                    case 'everybody' :
                        /*
                         * all users have access to a general status
                         */
                        return true;
                        break;
                    case 'friends_only' :
                        /*
                         * check if current user is a friend of owner of the activity
                         */
                        if (!FRMSecurityProvider::checkPluginActive('friends', true)) {
                            return false;
                        }
                        $service = FRIENDS_BOL_Service::getInstance();
                        $isFriends = null;
                        if (isset($cache['friendships'][OW::getUser()->getId()])) {
                            if (isset($cache['friendships'][OW::getUser()->getId()][$activityOwnerId])) {
                                $isFriends = $cache['friendships'][OW::getUser()->getId()][$activityOwnerId];
                            }
                        } else {
                            $isFriends = $service->findFriendship(OW::getUser()->getId(), $activityOwnerId);
                        }
                        if (isset($isFriends) && $isFriends->status == 'active') {
                            return true;
                        }else {
                            return false;
                        }
                        break;
                    default:
                        return false;
                }
            }
        }
        else
        {
            return false;
        }
    }

    public function addForwardFeature(&$contextMenu,$feedType,$actionId,$entityType,$entityId, $params = array())
    {

        if(!$this->canForwardPost($entityType, $entityId, $feedType, $params))
        {
            return;
        }
        $activity = null;
        if (isset($params['createActivity'])) {
            $activity = $params['createActivity'];
        }
        if ($activity == null) {
            $activity = $this->getCreatorActivityOfAction($entityType, $entityId);
        }
        $actionFeed = null;
        if (isset($params['cache']['feed_by_creator_activity']) && array_key_exists($activity->id, $params['cache']['feed_by_creator_activity'])) {
            if (isset($params['cache']['feed_by_creator_activity'][$activity->id])) {
                $actionFeed = $params['cache']['feed_by_creator_activity'][$activity->id];
            }
        } else {
            $actionFeed = NEWSFEED_BOL_ActionFeedDao::getInstance()->findByActivityIds(array($activity->id))[0];
        }
        if(!FRMSecurityProvider::checkPluginActive('groups', true))
        {
            $sectionId=2;
        }else{
            $sectionId=1;
        }
        $enableQRSearch = !(boolean)OW::getConfig()->getValue('frmnewsfeedplus','enable_QRSearch');
        array_unshift($contextMenu, array(
            'label' => OW::getLanguage()->text('frmnewsfeedplus', 'forward_post'),
            "class" => "newsfeed_forward_btn",
            'attributes' => array(
                'onclick' => 'showUserGroupsComponent($(this).data().aid, $(this).data().fid,$(this).data().vis,$(this).data().pri,$(this).data().fty,$(this).data().sid,$(this).data().rqs,$(this).data().title)',
                "data-aid" => $actionId,
                "data-fid" => $actionFeed->feedId,
                "data-vis" => $activity->visibility,
                "data-pri" => $activity->privacy,
                "data-fty" =>$actionFeed->feedType,
                "data-sid" =>$sectionId,
                "data-rqs" => (int)$enableQRSearch,
                "data-title" =>"",
            )
        ));
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getStaticJsUrl() . 'group_user_select.js');
    }

    /**
     * @param $sectionId
     * @param $actionId
     * @param $feedId
     * @param $visibility
     * @param $privacy
     * @param $feedType
     * @return array
     */
    public function getForwardSections($sectionId,$actionId,$feedId,$visibility,$privacy,$feedType)
    {
        $sections = array();
        $title="";
        for ($i = 1; $i <= 2; $i++)
        {
            if($i==1)
            {
                if(!FRMSecurityProvider::checkPluginActive('groups', true))
                {
                    continue;
                }

            }
            if($i==2)
            {
                $disableNewsfeedFromUserProfile = OW::getConfig()->getValue('newsfeed', 'disableNewsfeedFromUserProfile');
                if (isset($disableNewsfeedFromUserProfile) && $disableNewsfeedFromUserProfile == "on")
                {
                    continue;
                }

            }
            $enableQRSearch = !(boolean)OW::getConfig()->getValue('frmnewsfeedplus','enable_QRSearch');
            $url = "javascript:forwardNewsfeedComponent.close();showUserGroupsComponent(" . $actionId.",".$feedId.",".$visibility.",'".$privacy."','".$feedType."',".$i.",".(int)$enableQRSearch.",'".$title. "');";
            $sections[] = array(
                'sectionId' => $i,
                'active' => $sectionId == $i ? true : false,
                'url' => $url,
                'label' => $this->getPageHeaderLabel($i)
            );
        }
        return $sections;
    }

    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frmnewsfeedplus', 'forward_to_group');
        } else if ($sectionId == 2) {
            return OW::getLanguage()->text('frmnewsfeedplus', 'forward_to_user');
        }
    }

    public function getFeedForwardType($sectionId)
    {
        if ($sectionId == 1) {
            return 'groups';
        } else if ($sectionId == 2) {
            return 'user';
        }
    }

    public function attachmentAddParameters(OW_Event $event)
    {
        $params=$event->getParams();
        if(!isset($params['oldParams']) || !isset($params['pluginKey']) || $params['pluginKey']!='frmnewsfeedplus')
        {
            return;
        }
        $newparams = $params['oldParams'];
        $newparams['photoPreviewFeature']=true;
        $previewExtensions = array_merge(FRMSecurityProvider::VIDEO_EXTENSIONS,FRMSecurityProvider::AUDIO_EXTENSIONS,FRMSecurityProvider::IMAGE_EXTENSIONS);
        $newparams['previewExtensions']=$previewExtensions;
        $event->setData(array('newParams'=>$newparams));
    }

    public function getThumbnailFileDir($FileName)
    {
        return OW::getPluginManager()->getPlugin('frmnewsfeedplus')->getUserFilesDir() . $FileName;
    }

    public function getThumbnailFilePath($FileName)
    {
        return OW::getStorage()->getFileUrl($this->getThumbnailFileDir($FileName));
    }

    public function afterStatusComponentAddition(OW_Event $event)
    {
        $params = $event->getParams();
        $eventData= $event->getData();
        $uri = OW::getRequest()->getRequestUri();
        $allow_sort = true;
        if(OW::getConfig()->configExists('frmnewsfeedplus', 'allow_sort')){
            $allow_sort = OW::getConfig()->getValue('frmnewsfeedplus', 'allow_sort');
        }
        $attr = OW::getRequestHandler()->getHandlerAttributes();
        if ($allow_sort && preg_match('/newsfeed\/\d*\??.*$/', $uri) == 0 &&
            ($attr[OW_RequestHandler::ATTRS_KEY_CTRL]=='BASE_CTRL_ComponentPanel' && $attr[OW_RequestHandler::ATTRS_KEY_ACTION]=='dashboard')) {
            if (isset($params['feedType']) && isset($params['feedId'])) {
                $options = array();
                $options[self::ORDER_BY_ACTIVITY]['text'] = OW::getLanguage()->text('frmnewsfeedplus', 'sort_by_activity');
                $options[self::ORDER_BY_ACTION]['text'] = OW::getLanguage()->text('frmnewsfeedplus', 'sort_by_action');
                $options[self::ORDER_BY_ACTIVITY]['value']=self::ORDER_BY_ACTIVITY;
                $options[self::ORDER_BY_ACTION]['value']=self::ORDER_BY_ACTION;
                if (isset($_COOKIE['newsfeed_order']) && ($_COOKIE['newsfeed_order']==self::ORDER_BY_ACTION || $_COOKIE['newsfeed_order']==self::ORDER_BY_ACTIVITY)) {
                    $options[$_COOKIE['newsfeed_order']]['selected'] = true;
                }else if(OW::getConfig()->configExists('frmnewsfeedplus', 'newsfeed_list_order')) {
                    $options[OW::getConfig()->getValue('frmnewsfeedplus', 'newsfeed_list_order')]['selected'] = true;
                }
                $eventData['options']=$options;
                $event->setData($eventData);
            }
        }
    }

    public function canForwardPostEvent(OW_Event $event){
        $params = $event->getParams();
        $action = null;
        $activity = null;
        $group = null;
        if (!isset($params['entityId']) || !isset($params['entityType'])) {
            $event->setData(array('forwardable' => false));
            return;
        }
        if (isset($params['action'])) {
            $action = $params['action'];
        }
        if (isset($params['activity'])) {
            $activity = $params['activity'];
        }
        if (isset($params['group_object'])) {
            $group = $params['group_object'];
        }
        $params['group'] = $group;
        $params['action'] = $action;
        $params['activity'] = $activity;
        $event->setData(array('forwardable' => $this->canForwardPostByEntityIdAndEntityType($params['entityType'], $params['entityId'], $params)));
    }

    public function changeNewsfeedActionQuery(OW_Event $event)
    {
        $order = null;
        if (isset($_COOKIE['newsfeed_order'])) {
            $order = $_COOKIE['newsfeed_order'];
        } else if (isset($_POST['newsfeed_order'])) {
            $order = $_POST['newsfeed_order'];
        }
        if ($order != null) {
            $order = UTIL_HtmlTag::stripTagsAndJs($order);
            $order = UTIL_HtmlTag::escapeHtml($order);
            if (OW::getConfig()->configExists('frmnewsfeedplus', 'newsfeed_list_order'))
            {
                if (OW::getConfig()->getValue('frmnewsfeedplus', 'newsfeed_list_order') == self::ORDER_BY_ACTION) {
                    $orderBy = ' ORDER BY MAX(`b`.`id`) DESC ';
                }else {
                    $orderBy = ' ORDER BY MAX(`b`.`timeStamp`) DESC ';
                }
            }

            if ($order == self::ORDER_BY_ACTION)
            {
                $orderBy = ' ORDER BY MAX(`b`.`id`) DESC ';
            } else if ($order == self::ORDER_BY_ACTIVITY)
            {
                $orderBy = ' ORDER BY MAX(`b`.`timeStamp`) DESC ';
            }
            if(isset($orderBy))
            {
                $event->setData(array('orderBy'=>$orderBy));
            }
        }
    }

    public function onStatusUpdateCheckData (OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if ( isset($_POST['attachment_feed_data']) && !empty($_POST['attachment_feed_data']) ) {
            $data['hasData']=true;
            $data['attachments'] = $_POST['attachment_feed_data'];
        }
        $event->setData($data);
    }
}
