<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 2/26/18
 * Time: 1:08 PM
 */
class FRMQUESTIONS_MCMP_OptionList extends OW_MobileComponent
{
    private $editMode;
    /**
     * @var FRMQUESTIONS_BOL_Question
     */
    private $question;
    private $userId;
    private $optionList;
    private $optionComponents = array();
    private $additionalInfo = array();
    /**
     *
     * @var FRMQUESTIONS_BOL_Service
     */
    protected $service;


    public function __construct($question, array $optionList, $userId, $editMode = false, $additionalInfo = array())
    {
        parent::__construct();

        $this->optionList = $optionList;
        $this->userId = $userId;
        $this->question = $question;
        $this->editMode = $editMode;
        $this->additionalInfo = $additionalInfo;
        $this->service = FRMQUESTIONS_BOL_Service::getInstance();
        $this->setTemplate(OW::getPluginManager()->getPlugin('frmquestions')->getMobileCmpViewDir() . 'option_list.html');
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $optionCmpList = array();
        $optionIdList = array();
        $isMultiple = $this->question != null ? $this->question->isMultiple : 1;
        foreach ($this->optionList as $option) {
            $optionCmp = new FRMQUESTIONS_MCMP_Option($option, $this->userId, $this->editMode || $option->userId == $this->userId, $isMultiple, false, $this->additionalInfo);
            $this->optionComponents[] = $optionCmp;
            $optionIdList[] = $option->id;
        }
        $answeredOptionIdList = array();
        if ($this->question && isset($this->additionalInfo['cache']['questions'][$this->question->id]['options'])) {
            $cachedOptions = $this->additionalInfo['cache']['questions'][$this->question->id]['options'];
            foreach ( $cachedOptions as $cachedOption ) {
                if (isset($cachedOption['answers']) && isset($cachedOption['object']) && in_array($this->userId, $cachedOption['answers'])) {
                    $answeredOptionIdList[] = $cachedOption['object']->id;
                }
            }
        } else {
            $answeredOptionIdList = $this->service->findUserAnsweredOptionIdList($this->userId, $optionIdList, $this->additionalInfo);
        }
        foreach ( $this->optionComponents as $optionCmp )
        {
            if (in_array($optionCmp->getOptionId(), $answeredOptionIdList)){
                $optionCmp->setVoted(true);
            }
            $optionCmpList[] = $optionCmp->render();
        }
        $this->assign('list', $optionCmpList);
    }
}