<?php
class FRMSECURITYESSENTIALS_CMP_PrivacyFloatBox extends OW_Component
{
    public function __construct($objectId, $actionType, $feedId)
    {
        parent::__construct();

        //check if authorized
        if(!OW::getUser()->isAuthenticated()) {
            exit(json_encode(array('result'=>false, 'content' => '404')));
        }
        if(!OW::getUser()->isAdmin()){
            if($feedId != '' && $feedId != OW::getUser()->getId()){
                exit(json_encode(array('result'=>false, 'content' => '404')));
            }
        }

        $form = new Form('edit-privacy');
        $form->setAction(OW::getRouter()->urlForRoute('frmsecurityessentials.edit_privacy'));
        $form->setAjax();
        $form->setAjaxResetOnSuccess(false);
        $privacy_successfuly_changedLabel = OW::getLanguage()->text("frmsecurityessentials", "privacy_successfuly_changed");
        $privacy_error_changedLabel = OW::getLanguage()->text("frmsecurityessentials", "privacy_not_changed");
        $form->bindJsFunction(Form::BIND_SUCCESS, 'function(data){if(data.result){OW.info("'.$privacy_successfuly_changedLabel.'");privacyChangeComplete(privacyChangeFloatBox,data.id,data.src,data.title,data.privacy,data.privacy_list);}else{OW.error("'.$privacy_error_changedLabel.'");}}');

        $privacy_value = null;

        if($actionType == 'user_status') {
            $privacy_value = $this->getPrivacyValueOfNewsFeedByActionId($objectId);
        }else if($actionType == 'photo_comments'){
            $privacy_value = $this->getPrivacyValueOfPhoto($objectId);
        }else if($actionType == 'video_comments'){
            $privacy_value = $this->getPrivacyValueOfVideo($objectId);
        }else if($actionType == 'album'){
            $albumPrivacy = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getPrivacyOfAlbum($objectId);
            if($albumPrivacy!=null){
                $privacy_value = $albumPrivacy;
            }
        }else if($actionType == 'questionsPrivacy'){
            $privacy_value = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->getQuestionPrivacy($feedId, $objectId);
        }else if($actionType == 'question'){
            $question = QUESTIONS_BOL_ActivityDao::getInstance()->findActivity($objectId, 'create', $objectId);
            $privacy_value = $question->privacy;

        }

        $privacy = new Selectbox('privacy');
        $options = array();
        $options[FRMSECURITYESSENTIALS_BOL_Service::$PRIVACY_EVERYBODY] = OW::getLanguage()->text("privacy", "privacy_everybody");
        $options[FRMSECURITYESSENTIALS_BOL_Service::$PRIVACY_ONLY_FOR_ME] = OW::getLanguage()->text("privacy", "privacy_only_for_me");
        $options[FRMSECURITYESSENTIALS_BOL_Service::$PRIVACY_FRIENDS_ONLY] = OW::getLanguage()->text("friends", "privacy_friends_only");
        $privacy->setHasInvitation(false);
        $privacy->setOptions($options);
        $privacy->setRequired();
        $privacy->setValue($privacy_value);
        $form->addElement($privacy);

        $actionIdHiddenField = new HiddenField('objectId');
        $actionIdHiddenField->setValue($objectId);
        $form->addElement($actionIdHiddenField);

        $actionTypeHiddenField = new HiddenField('actionType');
        $actionTypeHiddenField->setValue($actionType);
        $form->addElement($actionTypeHiddenField);

        $feedIdHiddenField = new HiddenField('feedId');
        $feedIdHiddenField->setValue($feedId);
        $form->addElement($feedIdHiddenField);

        $submit = new Submit('submit', 'button');
        $submit->setValue(OW::getLanguage()->text('frmsecurityessentials', 'submit'));
        $form->addElement($submit);

        $this->addForm($form);
    }

    public function getPrivacyValueOfPhoto($objectId){
        $photoService = PHOTO_BOL_PhotoService::getInstance();
        $photo = $photoService->findPhotoById($objectId);
        return $photo->privacy;
    }

    public function getPrivacyValueOfVideo($objectId){
        $videoService = VIDEO_BOL_ClipService::getInstance();
        $video = $videoService->findClipById($objectId);
        return $video->privacy;
    }


    public function getPrivacyValueOfNewsFeedByActionId($actionId){
        $activities = NEWSFEED_BOL_ActivityDao::getInstance()->findIdListByActionIds(array($actionId));
        foreach ($activities as $activityId) {
            $activity = NEWSFEED_BOL_Service::getInstance()->findActivity($activityId)[0];
            if ($activity->activityType == 'create') {
                return $activity->privacy;
            }
        }

        return null;
    }

}
