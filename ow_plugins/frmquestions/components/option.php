<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:11 PM
 */
class FRMQUESTIONS_CMP_Option extends OW_Component
{
    const UNIQUE_ID_PREFIX = 'question_option_';
    /**
     *
     * @var FRMQUESTIONS_BOL_Option
     */
    private $option;
    private $answered;
    private $answerCount;
    private $percents;
    private $userIds;
    private $multiple;
    private $disabled;
    private $editMode;
    private $uniqueId;
    private $voted = false;
    private $additionalInfo = array();

    public function setVoted( $voted = true )
    {
        $this->voted = (bool) $voted;
    }
    public function getOptionId()
    {
        if(isset($this->option)){
            return $this->option->getId();
        }
        return null;
    }

    public function __construct(FRMQUESTIONS_BOL_Option $opt, $userId, $editMode = false, $multiple = true, $disabled = false, $additionalInfo = array())
    {
        parent::__construct();

        $this->option = $opt;
        $this->editMode = $editMode;
        $this->additionalInfo = $additionalInfo;
        if ($opt->getId() == 0){
            $this->uniqueId = self::UNIQUE_ID_PREFIX.$opt->questionId.'_'.FRMSecurityProvider::generateUniqueId();
        }
        else{
            $this->uniqueId = self::UNIQUE_ID_PREFIX.$opt->questionId.'_'.$opt->getId();
        }
        $this->multiple = $multiple;
        $questionsCount = null;
        $this->disabled = $disabled;
        if (isset($this->additionalInfo['cache']['questions']) && array_key_exists($this->option->questionId, $this->additionalInfo['cache']['questions'])) {
            $questionsCount = 0;
            $this->userIds = array();
            $cachedQuestions = $this->additionalInfo['cache']['questions'];
            if (isset($cachedQuestions[$this->option->questionId])) {
                $cachedQuestion = $cachedQuestions[$this->option->questionId];
                if (isset($cachedQuestion['options'])) {
                    if (isset($cachedQuestion['options'][$this->option->getId()])) {
                        $option = $cachedQuestion['options'][$this->option->getId()];
                        if (isset($option['answers'])) {
                            $this->userIds = $option['answers'];
                        }
                    }
                    foreach ($cachedQuestion['options'] as $op) {
                        if (isset($op['answers'])) {
                            $questionsCount = $questionsCount + sizeof($op['answers']);
                        }
                    }
                }
            }
        } else {
            $this->userIds = FRMQUESTIONS_BOL_Service::getInstance()->findUserAnsweredByOption($this->option->getId());
        }
        $this->answerCount = sizeof($this->userIds);
        $this->percents = FRMQUESTIONS_BOL_Service::getInstance()->findAnswerPercentByOption($this->option->questionId, $this->option->getId(), $this->answerCount, $questionsCount);
        $this->answered = in_array($userId, $this->userIds);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();

        $tplOption = array();
        $tplOption['id'] = $this->option->id;
        $tplOption['text'] = $this->option->text;
        $tplOption['count'] = $this->answerCount;
        $tplOption['percents'] = $this->percents;
        $tplOption['answered'] = $this->answered;
        $tplOption['multiple'] = $this->multiple;
        $tplOption['disabled'] = $this->disabled;
        $tplOption['editMode'] = $this->editMode;
        $tplOption['voted'] = $this->voted;

        $avatarList = new FRMQUESTIONS_CMP_Answers($this->userIds, $this->answerCount, $this->additionalInfo);

        $tplOption['users'] = $avatarList->render();
        $idList = $this->userIds;
        $userIds = array();
        foreach ($idList as $item)
            $userIds[] = (int) $item;
        $showUsers = 'javascript: OW.showUsers('.json_encode($userIds).')';
        $this->assign('userIds', $showUsers);
        $this->assign('option', $tplOption);

        $this->assign('uniqueId', $this->uniqueId);

        OW::getDocument()->addOnloadScript($this->getJs());
    }

    function getJs()
    {
        return UTIL_JsGenerator::composeJsString('question_map[{$questionId}].addOption(new QUESTIONS_Option({$uniqueId},{$optionId},{$ajaxUrls},{$questionId},{$answered},{$multiple},{$answerError}));', array(
            'uniqueId' => $this->uniqueId,
            'questionId' => $this->option->questionId,
            'optionId' => $this->option->getId(),
            'ajaxUrls' => array('delete'=>OW::getRouter()->urlForRoute('frmoption-delete'),'answer'=>OW::getRouter()->urlForRoute('frmquestion-answer')),
            'answered' => $this->answered,
            'multiple' => $this->multiple,
            'answerError' => !OW::getUser()->isAuthenticated()? OW::getLanguage()->text('frmquestions', 'guest_answer_error') : ''
        ));
    }

    function getComponents()
    {
        return $this->components;
    }
}