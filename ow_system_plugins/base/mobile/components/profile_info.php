<?php
class BASE_MCMP_ProfileInfo extends OW_MobileComponent
{
    /**
     *
     * @var BOL_User
     */
    protected $user;
    protected $previewMode = false;

    public function __construct( BOL_User $user, $previewMode = false )
    {
        parent::__construct();
        
        $this->user = $user;
        $this->previewMode = $previewMode;
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $questionNames = array();
        
        if ( $this->previewMode )
        {
            $questions = BOL_QuestionService::getInstance()->findViewQuestionsForAccountType($this->user->accountType);
            foreach ( $questions as $question )
            {
                if ( $question["name"] == OW::getConfig()->getValue('base', 'display_name_question') )
                {
                    continue;
                }
                
                $questionNames[$question['sectionName']][] = $question["name"];
            }
        }
        
        $questions = BASE_CMP_UserViewWidget::getUserViewQuestions($this->user->id, OW::getUser()->isAdmin(), reset($questionNames));
        
        $data = array();
        if (isset($questions['data'][$this->user->id])) {
            foreach ($questions['data'][$this->user->id] as $key => $value) {
                $data[$key] = $value;

                if (is_array($value)) {
                    $data[$key] = implode(', ', $value);
                }
            }
        }
        foreach ( $questions['sections'] as $key => $value )
        {
            OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_QUESTIONS_DATA_PROFILE_RENDER, array('questions' => $questions['questions'][$key], 'userId' => $this->user->id, 'component' => $this)));
        }

        $this->assign("displaySections", !$this->previewMode);
        $this->assign('questionArray', $questions['questions']);
        $this->assign('questionData', $data);
        $this->assign('questionLabelList', $questions['labels']);
    }
}