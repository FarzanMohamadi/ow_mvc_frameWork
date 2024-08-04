<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:07 PM
 */
class FRMQUESTIONS_MCMP_Question extends OW_MobileComponent
{
    const UNIQUE_ID_PREFIX = 'question_';

    private $editMode;
    /**
     * @var FRMQUESTIONS_BOL_Question
     */
    private $question;
    public $questionIdtmp;
    private $uniqueId;
    private $userId;
    private $options;
    private $additionalInfo;

    public function __construct($question, $userId, $editMode = false, $questionIdtmp = false, $options = '', $additionalInfo = array())
    {
        parent::__construct();

        $this->userId = $userId;
        $this->additionalInfo = $additionalInfo;
        if (isset($question)){
            $this->question = $question;
            $this->uniqueId = self::UNIQUE_ID_PREFIX .$this->question->getId();
        }
        else if(isset($questionIdtmp) && $questionIdtmp != false){
            $this->questionIdtmp = $questionIdtmp;
            $this->options = $options;
            $this->uniqueId = self::UNIQUE_ID_PREFIX .$this->questionIdtmp ;
        }
        else{
            $this->questionIdtmp = FRMSecurityProvider::generateUniqueId();
            $this->uniqueId = self::UNIQUE_ID_PREFIX .$this->questionIdtmp ;
        }

        $this->editMode = $editMode;
        $this->setTemplate(OW::getPluginManager()->getPlugin('frmquestions')->getMobileCmpViewDir() . 'question.html');
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $this->assign('uniqueId', $this->uniqueId);
        $optionList = array();
        if (isset($this->question)){
            if (isset($this->additionalInfo['cache']['questions'][$this->question->id]['options'])) {
                $cachedOptions = $this->additionalInfo['cache']['questions'][$this->question->id]['options'];
                foreach ($cachedOptions as $cachedOption) {
                    if (isset($cachedOption['object'])) {
                        $optionList[] = $cachedOption['object'];
                    }
                }
            }else {
                $optionList = FRMQUESTIONS_BOL_Service::getInstance()->findOptionList($this->question->getId());
            }
        }
        else{
            $this->options = json_decode($this->options);
            $optionList = array();
            if ($this->options != null && $this->options != ''){
                foreach ($this->options as $text){
                    $option = new FRMQUESTIONS_BOL_Option();
                    $option->text = $text;
                    $option->questionId = $this->questionIdtmp;
                    $option->userId = $this->userId;
                    $option->timeStamp = time();
                    $optionList[] = $option;

                }
            }

        }
        $this->addComponent('optionList', new FRMQUESTIONS_MCMP_OptionList($this->question, $optionList, $this->userId, $this->editMode, $this->additionalInfo));
        $groupEntity = null;
        $checkGroupManager = true;
        $isGroupManager = false;
        if (OW::getUser()->isAdmin()) {
            $isGroupManager = true;
            $checkGroupManager = false;
        }
        if (isset($this->question) && isset($this->question->context) && $this->question->context === 'groups' && isset($this->additionalInfo['cache']['groups'][$this->question->contextId])) {
            $groupEntity = $this->additionalInfo['cache']['groups'][$this->question->contextId];
            if (isset($this->additionalInfo['cache']['groups_managers'])) {
                if (isset($this->additionalInfo['cache']['groups_managers'][$this->question->contextId])) {
                    $managerIds = $this->additionalInfo['cache']['groups_managers'][$this->question->contextId];
                    if (in_array(OW::getUser()->getId(), $managerIds)) {
                        $isGroupManager = true;
                        $checkGroupManager = false;
                    } else {
                        $checkGroupManager = false;
                    }
                }
            }
        }
        if ($groupEntity != null && $groupEntity->userId == OW::getUser()->getId()) {
            $isGroupManager = true;
            $checkGroupManager = false;
        }
        if (!isset($this->question) && OW::getUser()->isAuthenticated()){
            $this->addComponent('addOption', new FRMQUESTIONS_MCMP_AddOption($this->questionIdtmp));
        }
        else if ($isGroupManager || FRMQUESTIONS_BOL_Service::getInstance()->canCurrentUserAddOption($this->question->getId(), $groupEntity, $checkGroupManager, $this->question)){
            $this->addComponent('addOption', new FRMQUESTIONS_MCMP_AddOption($this->question->getId()));
        }

        $js = $this->getJs();
        OW::getDocument()->addOnloadScript($js);
    }

    public function getJs()
    {
        $questionId = isset($this->question) ? $this->question->getId() : $this->questionIdtmp;
        $js = UTIL_JsGenerator::composeJsString('question_map[{$questionId}] = new QUESTIONS_Question({$uniqueId},{$questionId},{$ajaxUrl});', array(
            'uniqueId' => $this->uniqueId,
            'questionId' => $questionId,
            'ajaxUrl' => OW::getRouter()->urlForRoute('frmquestion-reload'),
        ));
        return $js;
    }

    function getComponents()
    {
        return $this->components;
    }
}