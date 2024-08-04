<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */
final class FRMAUDIO_BOL_Service
{
    /***
     * @var FRMAUDIO_BOL_AudioDao
     */
    private $audioDao;

    /***
     * FRMAUDIO_BOL_Service constructor.
     */
    private function __construct()
    {
        $this->audioDao = FRMAUDIO_BOL_AudioDao::getInstance();
    }

    /***
     * @var
     */
    private static $classInstance;

    /***
     * @return FRMAUDIO_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param $expiredTime
     */
    public function removeTempAudios($expiredTime){
        $this->audioDao->removeTempAudios($expiredTime);
    }

    /***
     * @param $data
     * @return FRMAUDIO_BOL_Audio|null
     */
    public function saveTempAudio($data){
        if(!OW::getUser()->isAuthenticated()){
            return null;
        }
        $audioHashName = OW::getUser()->getId() . "_" . UTIL_String::getRandomString(16);
        OW::getStorage()->fileSetContent($this->getAudioFileDirectory($audioHashName), base64_decode($data));
        $audio = new FRMAUDIO_BOL_Audio();
        $audio->userId = OW::getUser()->getId();
        $audio->title = "temp";
        $audio->hash = $audioHashName;
        $audio->addDateTime = time();
        $audio->object_id = -1;
        $audio->object_type = "temp";
        $audio->valid = false;
        $this->audioDao->save($audio);
        return $audio;
    }

    /***
     * @param $file
     * @return FRMAUDIO_BOL_Audio|null
     */
    public function saveTempBlob($file){
        if(!OW::getUser()->isAuthenticated()){
            return null;
        }
        $audioHashName = OW::getUser()->getId() . "_" . UTIL_String::getRandomString(16);
        OW::getStorage()->moveFile($file['tmp_name'], $this->getAudioFileDirectory($audioHashName));
        $audio = new FRMAUDIO_BOL_Audio();
        $audio->userId = OW::getUser()->getId();
        $audio->title = "temp";
        $audio->hash = $audioHashName;
        $audio->addDateTime = time();
        $audio->object_id = -1;
        $audio->object_type = "temp";
        $audio->valid = false;
        $this->audioDao->save($audio);
        return $audio;
    }

    //  This Functions adds audio specifications to frm_audio table in Database
    /***
     * @param $title
     * @param $hash
     * @param $object_id
     * @param $object_type
     * @return FRMAUDIO_BOL_Audio
     */
    public function addAudio($title, $hash, $object_id, $object_type)
    {
        $audiocheck = $this->findAudiosByObject($object_id, $object_type);
        // this sections aims for edit post in forum where user wants to replace a posted audio with a new one
        if (isset($audiocheck)) {
            $this->deleteDatabaseRecord($audiocheck->id);
        }
        $audio = new FRMAUDIO_BOL_Audio();
        $audio->userId = OW::getUser()->getId();
        $audio->title = $title;
        $audio->hash = $hash;
        $audio->addDateTime = time();
        $audio->object_id = $object_id;
        $audio->object_type = $object_type;
        $this->audioDao->save($audio);
        return $audio;
    }

    public function onForward(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['actionData'])){
            return;
        }

        $actionData = $params['actionData'];
        if (isset($actionData->audioId)) {
            $audio = $this->findAudioById($actionData->audioId);
            if(isset($audio)){
                $audioEvent= new OW_Event('frmaudio.audioForward',array(
                    'fileHash'=>$audio->hash,
                    'title'=> $audio->title,
                    'object_id'=>-2,
                    'object_type'=> 'newsfeed'),'');
                OW::getEventManager()->trigger($audioEvent);
            }
        }
    }

    /***
     * @param OW_Event $event
     * @return FRMAUDIO_BOL_Audio
     */
    public function forwardAudio(OW_Event $event){
        $params=$event->getParams();
        $data=$event->getData();
        $audioHashName = OW::getUser()->getId() . "_" . UTIL_String::getRandomString(16);
        OW::getStorage()->copyFile($this->getAudioFileDirectory($params['fileHash']), $this->getAudioFileDirectory($audioHashName));
        $audio = new FRMAUDIO_BOL_Audio();
        $audio->userId = OW::getUser()->getId(); //todo: assign correct userID
        $audio->title = $params['title'];
        $audio->hash = $audioHashName;
        $audio->addDateTime = time();
        $audio->object_id =$params['object_id'];
        $audio->object_type = $params['object_type'];
        $audio->valid = false;
        $this->audioDao->save($audio);
        $event->setData(array('audioId'=>$audio->id));
    }

    /***
     * @return Form
     */
    public function getAddAudioForm()
    {

        $form = new Form("add_audio_form");
        $form->setAction(OW::getRouter()->urlForRoute('frmaudio.add_audio'));
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){addAudioComplete(audioFloatBox, data.name, data.audioData, data.audioId);}else{OW.error("Parser error");}}');
        $nameField = new TextField("name");
        $nameField->setLabel(OW::getLanguage()->text('frmaudio', 'audionamefield'));
        $nameField->setRequired();
        $form->addElement($nameField);

        $upload = new HiddenField("audioId");
        $upload->addAttribute("id", "audioId");
        $upload->setRequired();
        $form->addElement($upload);

        $submitField = new Submit("submit");
        $form->addElement($submitField);
        return $form;
    }

    /***
     * @param $id
     */
    public function deleteDatabaseRecord($id)
    {
        $this->audioDao->deleteById($id);
    }

    /***
     * @param $objectId
     * @param $objectType
     */
    public function deleteByObjectIdAndType($objectId, $objectType)
    {
        $this->audioDao->deleteByObjectIdAndType($objectId, $objectType);
    }

    /***
     * @param $userId
     * @return array
     */
    public function findAudiosByUserId($userId)
    {
        return $this->audioDao->findAudiosByUserId($userId);
    }

    /***
     * @param $id
     * @return FRMAUDIO_BOL_Audio
     */
    public function findAudioById($id)
    {
        return $this->audioDao->findAudioById($id);
    }

    /***
     * @param $object_id
     * @param $object_type
     * @return mixed
     */
    public function findAudiosByObject($object_id, $object_type)
    {
        return $this->audioDao->findAudiosByObject($object_id, $object_type);
    }


    /***
     * @param $userId
     * @param int $first
     * @param int $count
     * @return array
     */
    public function findListOrderedByDate($userId, $first = 0, $count = 10)
    {
        return $this->audioDao->findListOrderedByDate($userId, $first, $count);
    }

    /***
     * @param $audioName
     * @return string
     */
    public function getAudioFileDirectory($audioName)
    {
        return OW::getPluginManager()->getPlugin('frmaudio')->getUserFilesDir() . $audioName . ".mp3";
    }

    /***
     * @param $audioName
     * @return string
     */
    public function getAudioFileUrl($audioName)
    {
        return OW::getPluginManager()->getPlugin('frmaudio')->getUserFilesUrl() . $audioName . ".mp3";
    }

    /***
     *
     */
    public function getAudioJS()
    {
        //new way
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmaudio')->getStaticJsUrl() . 'js2/WebAudioRecorder.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmaudio')->getStaticJsUrl() . 'js2/Recorder.js');
        OW::getDocument()->addOnloadScript('loadAudioRecorder("' . OW::getPluginManager()->getPlugin('frmaudio')->getStaticJsUrl() . '");');
    }

    /***
     * @param OW_Event $event
     */
    public function addAudioInputFieldsToNewsfeed(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['form'])) {
            $form = $this->addAudioInputFieldToForm($params['form']);
        }

        $this->AudioRender($event, 'newsfeed');
    }

    /***
     * @param $form
     * @param null $dataValue
     * @param null $nameValue
     * @return mixed
     */
    public function addAudioInputFieldToForm($form, $dataValue = null, $nameValue = null)
    {
        $AudioFileData = new HiddenField('audio_feed_data');
        $AudioFileData->addAttribute("id", "audio_feed_data");
        $AudioFileData->setValue($dataValue);
        $form->addElement($AudioFileData);

        $AudioFileName = new HiddenField('audio_feed_name');
        $AudioFileName->addAttribute("id", "audio_feed_name");
        $AudioFileName->setValue($nameValue);
        $form->addElement($AudioFileName);
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){audioRemove();}');
        return $form;
    }

    /***
     * @param OW_Event $event
     */
    public function addAudioInputFieldsToForum(OW_Event $event)
    {
        $params = $event->getParams();
        $service = FRMAUDIO_BOL_Service::getInstance();
        $audioEditFile = null;
        $audio = null;
        if(isset($params['postId'])) {
            $audio = $service->findAudiosByObject($params['postId'], 'forum-post');
            if (isset($audio)) {
                $audioEditFile = $service->getAudioFileUrl($audio->hash);
            }
        }

        if ($audioEditFile==null || $audio == null) {
            $form = $this->addAudioInputFieldToForm($params['form']);
        } elseif (isset($audio)) {
            $form = $this->addAudioInputFieldToForm($params['form'], $audioEditFile, $audio->title);
        }


        $this->AudioRender($event, 'edit_forum');
        if ( OW::getRequest()->isPost() && !OW::getRequest()->isAjax()){
            $this->saveInsertedAudio($event);
        }
    }

    /***
     * @param OW_Event $event
     */
    public function saveInsertedAudio(OW_Event $event)
    {
        $data = $event->getData();
        $params = $event->getParams();
        $object_type = null;
        $object_id = null;

        // To Extend Plugin to other sections, related $object_type and $object_id keys must be declared here and
        // function to render bust be binded on the corresponding event.

        if (isset($data["statusId"])) {
            $object_type = "newsfeed";
            $object_id = $data["statusId"];
        }
        if (isset($params["postId"])) {
            $object_type = "forum-post";
            $object_id = $params["postId"];
        }
        if (isset($params["postDto"])) {
            $object_type = "forum-post";
            $object_id = $_POST['post-id'];
        }
        if(isset($data['photoIdList'][0])){
            $object_type = "newsfeed";
            $object_id = $data['photoIdList'][0];
        }
        $service = FRMAUDIO_BOL_Service::getInstance();
        if ($object_type != null && $object_id != null && isset($_POST['audio_feed_name']) && $_POST['audio_feed_name'] != "" && isset($_POST['audio_feed_data']) && $_POST['audio_feed_data'] != "") {
            $audio = $this->findAudioById($_POST['audio_feed_data']);
            if(OW::getUser()->getId()!=$audio->userId)
                return;
            $audio->title = $_POST['audio_feed_name'];
            $audio->object_id = $object_id;
            $audio->object_type = $object_type;
            $audio->valid = true;
            $this->audioDao->save($audio);
            $data["audioId"] = $audio->id;
            $event->setData($data);

            if ($event->getName() == 'feed.on_entity_action' && OW::getApplication()->getContext() == OW::CONTEXT_MOBILE) {
                //just works with mobile version and for newsfeed update status form.
                echo '<script type="text/javascript">window.parent.audioRemove();</script>';
            }
        }else if($object_id != null && $object_type != null && (!isset($_POST['audio_feed_name']) || !isset($_POST['audio_feed_data']) || $_POST['audio_feed_name'] == "" || $_POST['audio_feed_data'] == "" ) && $object_type == "forum-post"){
            $service->deleteByObjectIdAndType($object_id, $object_type);
        }else{
            $forwardedAudio = $this->findAudiosByObject(-2, 'newsfeed');
            if (isset($forwardedAudio)) {
                $service->deleteByObjectIdAndType(-2, 'newsfeed');
                $audio = new FRMAUDIO_BOL_Audio();
                $audio->userId = $forwardedAudio->userId;
                $audio->title = $forwardedAudio->title;
                $audio->hash = $forwardedAudio->hash;
                $audio->addDateTime = $forwardedAudio->addDateTime;
                $audio->object_id =$params['entityId'];
                $audio->object_type = $forwardedAudio->object_type;
                $audio->valid = true;
                $this->audioDao->save($audio);
                $data["audioId"] = $audio->id;
                $event->setData($data);
            }

        }
    }

    /***
     * @param OW_Event $event
     */
    public function appendAudioPlayerToFeed(OW_Event $event)
    {
        $data = $event->getData();
        $params = $event->getParams();
        if (isset($params["data"]["audioId"])) {
            $audioId = $params["data"]["audioId"];
            $audio = FRMAUDIO_BOL_Service::getInstance()->findAudioById($audioId);
            if ($audio != null) {
                $src = FRMAUDIO_BOL_Service::getInstance()->getAudioFileUrl($audio->hash);
                $audioString = '<div class="audio_item_player"><audio class="audio_item_player" width="100%" height="38px" controls src="' . $src . '" type="audio/mp3"></audio></div>';
            } else {
                $audioString = '<div class="audio_item_removed">' . OW::getLanguage()->text('frmaudio', 'audio_feed_removed') . '</div>';
            }
            $data["content"] = $data["content"] . $audioString;
            $event->setData($data);
        }
        if(!empty($_REQUEST) && isset($_REQUEST['p']) && json_decode($_REQUEST['p'])!=null && isset(json_decode($_REQUEST['p'])->entityType) && (json_decode($_REQUEST['p'])->entityType=='user-status' || json_decode($_REQUEST['p'])->entityType == 'photo_comments')){
            OW::getDocument()->addOnloadScript("$('audio').mediaelementplayer();");
        }
    }

    // this function gets the values of form post before render data and adds audio field to it
    /***
     * @param OW_Event $event
     */
    public function AudioRenderInPostForum(OW_Event $event)
    {
        $params = $event->getParams();
        if (isset($params['postList'])) {
            $postList = $params['postList'];
            $editedPostList = array();
            foreach ($postList as $post) {
                $audio = FRMAUDIO_BOL_Service::getInstance()->findAudiosByObject($post['id'], 'forum-post');
                if ($audio != null) {
                    $src = FRMAUDIO_BOL_Service::getInstance()->getAudioFileUrl($audio->hash);
                    $audioString = '<div class="audio_item_player"><audio class="audio_item_player"  width="100%" height="38px" controls src="' . $src . '" type="audio/mp3"></audio></div>';
                    $post['audio'] =  $audioString;
                }
                $editedPostList[] = $post;
                $event->setData(array('postList' => $editedPostList));
            }
        } else if (isset($params['postId'])) {                                     //to add audio player in forum post edit page.
            $postId = $params['postId'];
            $audio = FRMAUDIO_BOL_Service::getInstance()->findAudiosByObject($postId, 'forum-post');
            if ($audio != null) {
                $src = FRMAUDIO_BOL_Service::getInstance()->getAudioFileUrl($audio->hash);
                $deleteTile = OW_Language::getInstance()->text('frmaudio', 'delete_audio_item');
                $audioString = '<div class="audio_item_player"><audio class="audio_item_player" width="100%" height="38px" controls src="' . $src . '" type="audio/mp3"></audio></div> <a class="audio_item_delete" onclick="audioRemove()" style="cursor: pointer">' . $deleteTile . '</a>';
                OW::getDocument()->addOnloadScript("$('audio').mediaelementplayer();");
                $event->setData(array('extendedText' => $audioString));
            }
        }
    }

    // this Function is for adding Audio plugin icon to site elements and to initiate the plugin
    /***
     * @param OW_Event $event
     * @param string $type
     */
    public function AudioRender(OW_Event $event, $type = "")
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmaudio')->getStaticJsUrl() . 'frmaudio.js');
        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("frmaudio")->getStaticCssUrl() . 'audio.css');
        if (OW::getConfig()->getValue('frmaudio', 'audio_dashbord') && $type == "newsfeed") {
            OW::getDocument()->addOnloadScript('$(".frmaudio_mic").remove();if(hasRecoredAudio()){$(\'.dashboard-NEWSFEED_CMP_MyFeedWidget .ow_status_update_btn_block .ow_attachment_icons\').append(\'<span class="ow_smallmargin frmaudio_mic"><span class="frmaudio_mic" onclick="CreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            OW::getDocument()->addOnloadScript('$(".frmaudio_mic").remove();if(hasRecoredAudio()){$(\'.group-NEWSFEED_CMP_EntityFeedWidget .ow_status_update_btn_block .ow_attachment_icons\').append(\'<span class="ow_smallmargin frmaudio_mic"><span class="frmaudio_mic" onclick="CreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
        }
        if (OW::getConfig()->getValue('frmaudio', 'audio_profile') && $type == "newsfeed") {
            if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE) {
                OW::getDocument()->addOnloadScript('if(hasRecoredAudio()){$(\'.owm_newsfeed_block .owm_newsfeed_status_update_add_cont \').append(\'<span class="ow_smallmargin frmaudio_mic"><span class="frmaudio_mic" onclick="MobileCreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            } else {
                OW::getDocument()->addOnloadScript('if(hasRecoredAudio()){$(\'.profile-NEWSFEED_CMP_UserFeedWidget .ow_status_update_btn_block .ow_attachment_icons\').append(\'<span class="ow_smallmargin frmaudio_mic"><span class="frmaudio_mic" onclick="CreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            }
        }
        if (OW::getConfig()->getValue('frmaudio', 'audio_forum') && $type == "forum") {
            if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE) {
                OW::getDocument()->addOnloadScript('if(hasRecoredAudio()){$(\'.owm_forum_topic_attach_info\').append(\'<span class="ow_smallmargin frmaudio_mic" style="float: left"><span class="frmaudio_mic" onclick="MobileCreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            } else {
                OW::getDocument()->addOnloadScript('if(hasRecoredAudio()){$(\'.forum_add_post .ow_status_update_btn_block\').append(\'<span class="ow_smallmargin frmaudio_mic"><span class="frmaudio_mic" onclick="CreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            }
        }
        if (OW::getConfig()->getValue('frmaudio', 'audio_forum') && $type == "edit_forum") {
            if (OW::getApplication()->getContext() == OW::CONTEXT_MOBILE) {
                OW::getDocument()->addOnloadScript('if(hasRecoredAudio()){$(\'.owm_forum_topic_attach_info\').append(\'<span class="ow_smallmargin frmaudio_mic" style="float: left"><span class="frmaudio_mic" onclick="MobileCreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            } else {
                OW::getDocument()->addOnloadScript('if(hasRecoredAudio()){$(\'.ow_status_update_btn_block\').append(\'<span class="ow_smallmargin frmaudio_mic"><span class="frmaudio_mic" onclick="CreateAudio()"><span class="buttons clearfix"><a class="frmaudio_mic"></a></span></span></span>\');}');
            }
        }
        $css = '
            .frmaudio_mic{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmaudio')->getStaticUrl() . 'img/mic.svg' . '");}
            input.start{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmaudio')->getStaticUrl() . 'img/record.svg' . '") !important; background-position: right 50%; background-repeat: no-repeat; padding-right: 27px;}
            input.stop{
                background-image: url("' . OW::getPluginManager()->getPlugin('frmaudio')->getStaticUrl() . 'img/stop.svg' . '") !important; background-position: right 50%; background-repeat: no-repeat; padding-right: 27px;}
            input.start:hover {
                color: #a2acb3;
                background-image: url("' . OW::getPluginManager()->getPlugin('frmaudio')->getStaticUrl() . 'img/record.svg' . '") !important;}
            input.stop:hover {
                color: #a2acb3;
                background-image: url("' . OW::getPluginManager()->getPlugin('frmaudio')->getStaticUrl() . 'img/stop.svg' . '") !important;}
            ';
        OW::getDocument()->addStyleDeclaration($css);
        $lang = OW::getLanguage();
        $lang->addKeyForJs('frmaudio', 'Recording');
        $lang->addKeyForJs('frmaudio', 'Converting');
        $lang->addKeyForJs('frmaudio', 'delete_audio_item');
        $defineMP3PathTemp = 'defineMP3Recorder("' . OW_PluginManager::getInstance()->getPlugin("frmaudio")->getStaticJsUrl() . 'recorderWorker.js' . '");';
        OW::getDocument()->addOnloadScript($defineMP3PathTemp);
        $defineMP3workerTemp = 'defineMP3Worker("' . OW_PluginManager::getInstance()->getPlugin("frmaudio")->getStaticJsUrl() . 'mp3Worker.js' . '");';
        OW::getDocument()->addOnloadScript($defineMP3workerTemp);
    }

    public function onStatusUpdateCheckData (OW_Event $event)
    {
        $params = $event->getParams();
        $data = $event->getData();
        if ( isset($_POST['audio_feed_data']) && !empty($_POST['audio_feed_data'])) {
            $data['hasData']=true;
        }
        $event->setData($data);
    }
}
