<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmobilesupport.bol
 * @since 1.0
 */
class FRMMOBILESUPPORT_BOL_WebServiceQuestions
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

    public function prepareOptionsData($questionId, $editPermission = false, $params = array()) {
        $options = array();
        $cache = array();
        if (isset($params['cache'])) {
            $cache = $params['cache'];
        }
        if (isset($cache['questions'][$questionId]['options'])) {
            $cachedOptions = $cache['questions'][$questionId]['options'];
            foreach ($cachedOptions as $cachedOption) {
                if (isset($cachedOption['object'])) {
                    $options[] = $cachedOption['object'];
                }
            }
        }else {
            $options = FRMQUESTIONS_BOL_Service::getInstance()->findOptionList($questionId);
        }
        $optionsData = array();

        $optionIds = array();
        foreach ($options as $option) {
            $optionIds[] = $option->id;
        }
        $optionsAnswerUserIds = array();
        if (isset($cache['questions'][$questionId]['options'])) {
            $optionsCached = $cache['questions'][$questionId]['options'];
            foreach ($optionsCached as $key => $optionCached) {
                if (isset($optionCached['answers'])) {
                    $optionsAnswerUserIds[$key] = $optionCached['answers'];
                }
            }
        } else {
            $optionsAnswerUserIds = FRMQUESTIONS_BOL_Service::getInstance()->findUserAnsweredByOptions($optionIds);
        }
        $allUserIds = array();
        foreach ($optionsAnswerUserIds as $key => $userIds) {
            foreach ($userIds as $userId) {
                if (!in_array($userId, $allUserIds)) {
                    $allUserIds[] = $userId;
                }
            }
        }
        $allUsersAnswered = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($allUserIds);

        foreach ($options as $optionItem) {
            $optionAnswerUserIds = array();
            if (isset($optionsAnswerUserIds[$optionItem->id])) {
                $optionAnswerUserIds = $optionsAnswerUserIds[$optionItem->id];
            }

            $answeredUsers = array();
            foreach ($allUsersAnswered as $usersAnswered) {
                if(in_array($usersAnswered['id'], $optionAnswerUserIds)) {
                    $answeredUsers[] = $usersAnswered;
                }
            }
            $optionsData[] = $this->prepareOptionData($optionItem, $answeredUsers, $editPermission);
        }

        return $optionsData;
    }

    public function findUserAnsweredOptions($questionId) {
        $options = FRMQUESTIONS_BOL_Service::getInstance()->findOptionList($questionId);
        $optionsData = array();

        $optionIds = array();
        foreach ($options as $option) {
            $optionIds[] = $option->id;
        }
        $optionsAnswerUserIds = FRMQUESTIONS_BOL_Service::getInstance()->findUserAnsweredByOptions($optionIds);
        $allUserIds = array();
        foreach ($optionsAnswerUserIds as $key => $userIds) {
            foreach ($userIds as $userId) {
                if (!in_array($userId, $allUserIds)) {
                    $allUserIds[] = $userId;
                }
            }
        }
        $allUsersAnswered = FRMMOBILESUPPORT_BOL_WebServiceUser::getInstance()->getUsersInfoByIdList($allUserIds);

        foreach ($options as $optionItem) {
            $optionAnswerUserIds = array();
            if (isset($optionsAnswerUserIds[$optionItem->id])) {
                $optionAnswerUserIds = $optionsAnswerUserIds[$optionItem->id];
            }

            $answeredUsers = array();
            foreach ($allUsersAnswered as $usersAnswered) {
                if(in_array($usersAnswered['id'], $optionAnswerUserIds)) {
                    $answeredUsers[] = $usersAnswered;
                }
            }
            $optionsData[] = $this->prepareOptionData($optionItem, $answeredUsers, $editPermission);
        }

        return $optionsData;
    }

    public function prepareOptionData($option, $answeredUsers, $editable = false)
    {
        $editable = $editable || $option->userId == OW::getUser()->getId();

        $userIds = array();
        foreach ($answeredUsers as $answeredUser) {
            $userIds[] = $answeredUser['id'];
        }

        $currentUserAnswered = false;
        if(in_array(OW::getUser()->getId(), $userIds)) {
            $currentUserAnswered = true;
        }
        return array(
            'id' => (int) $option->id,
            'userId' => (int) $option->userId,
            'questionId' => (int) $option->questionId,
            'text' => $option->text,
            'answered' => $currentUserAnswered,
            'count' => sizeof($answeredUsers),
            'canRemove' => $editable,
            'timestamp' => $option->timeStamp,
            'users' => $answeredUsers,
        );
    }

    public function subscribe() {
        if(!FRMSecurityProvider::checkPluginActive('frmquestions', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if(!isset($_GET['questionId']) || empty($_GET['questionId'])){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $subscribeData = FRMQUESTIONS_BOL_Service::getInstance()->changeSubscribe(OW::getUser()->getId(), $_GET['questionId']);
        return array('valid' => true, 'subscribe' => !$subscribeData['subscribed']);
    }

    public function addAnswer(){
        if(!FRMSecurityProvider::checkPluginActive('frmquestions', true)){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        if(!isset($_GET['optionId']) || empty($_GET['optionId'])){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $guestAccess = FRMMOBILESUPPORT_BOL_WebServiceGeneral::getInstance()->checkGuestAccess();
        if(!$guestAccess){
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $optionId = (int) $_GET['optionId'];
        $option = FRMQUESTIONS_BOL_Service::getInstance()->findOption($optionId);
        if (!isset($option)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }
        $status = 'add';
        if (!FRMQUESTIONS_BOL_Service::getInstance()->findAnsweredStatusByOption(OW::getUser()->getId(), $option->getId())){
            FRMQUESTIONS_BOL_Service::getInstance()->addAnswer(OW::getUser()->getId(), $option->questionId, $option->getId());
        }
        else{
            $status = 'remove';
            FRMQUESTIONS_BOL_Service::getInstance()->removeAnswer(OW::getUser()->getId(), $option->getId());
        }
        $editable = FRMQUESTIONS_BOL_Service::getInstance()->canCurrentUserEdit($option->questionId);
        $optionsData = $this->prepareOptionsData($option->questionId, $editable);
        return array('valid' => true, 'id' => (int) $optionId, 'status' => $status,'optionsData'=>$optionsData, 'questionId' => (int) $option->questionId);
    }

    public function addQuestionOption(){
        if(!FRMSecurityProvider::checkPluginActive('frmquestions', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $text = null;
        $questionId = null;

        if(isset($_POST['text'])){
            $text = $_POST['text'];
            $text = UTIL_HtmlTag::stripTags($text);
        }

        if(isset($_POST['question_id'])){
            $questionId = $_POST['question_id'];
            $questionId = (int) $questionId;
        }

        if($questionId == null || $text == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        /** @var FRMQUESTIONS_BOL_Question $question */
        $question = FRMQUESTIONS_BOL_Service::getInstance()->findQuestion($questionId);
        if(!isset($question)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $action = NEWSFEED_BOL_Service::getInstance()->findAction($question->entityType, $question->entityId);
        if($action == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $text = trim($text);

        if ($text == '') {
            return array('valid' => false, 'message' => 'empty_text');
        }

        $valid = FRMQUESTIONS_BOL_Service::getInstance()->createOption($questionId, $text);
        if (!$valid) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $editable = FRMQUESTIONS_BOL_Service::getInstance()->canCurrentUserEdit($questionId);
        $optionsData = $this->prepareOptionsData($questionId, $editable);
        return array('valid' => true, 'optionsData' => $optionsData, 'questionId' => (int) $questionId);
    }

    public function removeQuestionOption(){
        if(!FRMSecurityProvider::checkPluginActive('frmquestions', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $optionId = null;

        if(isset($_POST['optionId'])){
            $optionId = $_POST['optionId'];
            $optionId = (int) $optionId;
        }

        if($optionId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $option = FRMQUESTIONS_BOL_Service::getInstance()->findOption($optionId);

        if($option == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $valid = false;

        $editable = FRMQUESTIONS_BOL_Service::getInstance()->canCurrentUserEdit($option->questionId);
        if (!$editable) {
            return array('valid' => $valid, 'message' => 'authorization_error');
        }

        $valid = FRMQUESTIONS_BOL_Service::getInstance()->removeOptionByObject($option, false);

        if ($valid == true) {
            $optionsData = $this->prepareOptionsData($option->questionId, $editable);
            return array('valid' => $valid, 'optionsData' => $optionsData, 'optionId' => $optionId, 'questionId' => (int) $option->questionId);
        }

        return array('valid' => $valid, 'message' => 'authorization_error');
    }

    public function changeQuestionConfig() {
        if(!FRMSecurityProvider::checkPluginActive('frmquestions', true)){
            return array('valid' => false, 'message' => 'plugin_not_found');
        }

        if(!OW::getUser()->isAuthenticated()){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $questionId = null;
        $allowAddOption = null;
        $allowMultipleAnswers = null;

        if(isset($_POST['question_id'])){
            $questionId = $_POST['question_id'];
            $questionId = (int) $questionId;
        }

        if(isset($_POST[FRMQUESTIONS_CLASS_CreateQuestionForm::ALLOW_ADD_OPTION_FIELD_NAME])){
            $allowAddOption = $_POST[FRMQUESTIONS_CLASS_CreateQuestionForm::ALLOW_ADD_OPTION_FIELD_NAME];
        }

        if(isset($_POST[FRMQUESTIONS_CLASS_EditQuestionForm::ALLOW_MULTIPLE_ANSWERS_NAME])){
            $allowMultipleAnswers = $_POST[FRMQUESTIONS_CLASS_EditQuestionForm::ALLOW_MULTIPLE_ANSWERS_NAME];
        }

        if (!in_array($allowAddOption, array(FRMQUESTIONS_BOL_Service::PRIVACY_EVERYBODY, FRMQUESTIONS_BOL_Service::PRIVACY_FRIENDS_ONLY, FRMQUESTIONS_BOL_Service::PRIVACY_ONLY_FOR_ME))) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if (!in_array($allowMultipleAnswers, array(FRMQUESTIONS_BOL_Service::MULTIPLE_ANSWER, FRMQUESTIONS_BOL_Service::ONE_ANSWER))) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        if($questionId == null){
            return array('valid' => false, 'message' => 'authorization_error');
        }

        /** @var FRMQUESTIONS_BOL_Question $question */
        $question = FRMQUESTIONS_BOL_Service::getInstance()->findQuestion($questionId);
        if(!isset($question)) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $canUserEdit = FRMQUESTIONS_BOL_Service::getInstance()->canUserEdit($question->id);
        if(!$canUserEdit) {
            return array('valid' => false, 'message' => 'authorization_error');
        }

        $isMultiple = $allowMultipleAnswers == FRMQUESTIONS_BOL_Service::MULTIPLE_ANSWER;
        FRMQUESTIONS_BOL_Service::getInstance()->editQuestion($questionId, $allowAddOption, $isMultiple);

        return array('valid' => true, 'questionId' => $questionId);
    }
}