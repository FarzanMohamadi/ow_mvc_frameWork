<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.classes
 * @since 1.0
 */
class BASE_CLASS_UserQuestionForm extends Form
{

    /**
     * Return form element presentation class
     *
     * @param string $presentation
     * @param string $questionName
     *
     * @return FormElement
     */
    protected function getPresentationClass( $presentation, $questionName, $configs = null )
    {
        return BOL_QuestionService::getInstance()->getPresentationClass($presentation, $questionName, $configs);
    }

    /**
     * Set question label
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function setLabel( $formField, $question )
    {
        $label = $formField->getLabel();

        if ( empty($label) )
        {
            $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label'));
        }
    }

    /**
     * Add user questions to form
     *
     * $questionList[section_name][question]
     * @param array $questionList
     * @param array $questionValueList
     * @param array $questionData
     *
     * @return BASE_CLASS_UserQuestionForm
     */
    public function addQuestions( $questionList, $questionValueList = array(), $questionData = array() )
    {
        $nameToElementName = [];
        foreach ( $questionList as $key => $question )
        {
            $custom = isset($question['custom']) ? $question['custom'] : null;

            $formField = $this->getPresentationClass($question['presentation'], $question['name'], $custom);

            $formField->setLabel(OW::getLanguage()->text('base', 'questions_question_' . $question['name'] . '_label'));
            $this->setLabel($formField, $question);

            if (!isset($question['fake']) || !$question['fake']) {
                $elName = (isset($question['realName']))?$question['realName']:$question['name'];
                $nameToElementName[$elName] = $question['name'];

                if (!empty($question['condition'])) {
                    $c = json_decode($question['condition'], true);
                    if(isset($nameToElementName[$c['question']])) {
                        // question may be unrelated
                        $cname = $nameToElementName[$c['question']];
                        $formField->addAttribute('condition', $question['name'] . '::' . $cname . '::' . $c['value']);
                        $question['required'] = '0';
                        // process
                        $submittedValue = (isset($_POST[$question['name']])) ? $_POST[$question['name']] : null;
                        $conditionValue = (isset($_POST[$cname])) ? $_POST[$cname] : null;
                        if (isset($this->post[$question['name']])) {
                            // for join
                            $submittedValue = (isset($this->post[$question['name']])) ? $this->post[$question['name']] : null;
                            $conditionValue = (isset($this->post[$cname])) ? $this->post[$cname] : null;
                        }
                        if (!empty($submittedValue)) {
                            $removeIt = false;
                            if (isset($questionValueList[$cname])) {
                                $label = BOL_QuestionService::getInstance()->getQuestionValueLang($c['question'], $conditionValue);
                                if (!fnmatch($c['value'], $label)) {
                                    $removeIt = true;
                                }
                            } else {
                                if (!fnmatch($c['value'], $conditionValue)) {
                                    $removeIt = true;
                                }
                            }
                            if ($removeIt) {
                                if (isset($_POST[$question['name']])) {
                                    unset($_POST[$question['name']]);
                                } else {
                                    unset($this->post[$question['name']]);
                                }
                            }
                        }
                    }
                }
            }

            if ( in_array($question['type'], array( BOL_QuestionService::QUESTION_VALUE_TYPE_MULTISELECT, BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT) ) 
                && method_exists($formField, 'setColumnCount') )
            {
                $this->setColumnCount($formField, $question);
            }

            // set field options
            if ( isset($questionValueList[$question['name']]) && method_exists($formField, 'setOptions') )
            {
                $this->setFieldOptions($formField, $question['name'], $questionValueList[$question['name']]);
            }

            // set field value
            if ( isset($questionData[$question['name']]) && $question['presentation'] !== BOL_QuestionService::QUESTION_PRESENTATION_PASSWORD )
            {
                $this->setFieldValue($formField, $question['presentation'], $questionData[$question['name']]);
            }

            $this->addFieldValidator($formField, $question);

            $formField->setRequired((string) $question['required'] === '1');

            OW_EventManager::getInstance()->trigger(new OW_Event('base.question_field_create',array(
                'element' => $formField,
                'field_name' => $question['name']
            )));

            $desc = '';
            if( isset($question['realName']) ) {
                $desc = BOL_QuestionService::getInstance()->getQuestionDescriptionLang($question['realName']);
            }
            if( empty($desc) && isset($question['name']) ){
                $desc = BOL_QuestionService::getInstance()->getQuestionDescriptionLang($question['name']);
            }
            $formField->setDescription($desc);
            $formField->addAttribute('autofocus');

            if($question['presentation'] == 'password'){
                $formField->addAttribute('autocomplete', 'new-password');
            }else {
                $formField->addAttribute('autocomplete', 'off');
            }

            $this->addElement($formField);
        }

        return $this;
    }

    /**
     * Set field value
     *
     * @param FormElement $formField
     * @param string $presentation
     * @param string $value
     */
    protected function setFieldValue( $formField, $presentation, $value )
    {
        $value = BOL_QuestionService::getInstance()->prepareFieldValue($presentation, $value);
        $formField->setValue($value);
    }

    /**
     * Set field options
     *
     * @param FormElement $formField
     * @param string $questionName
     * @param array<BOL_QuestionValue> $questionValues
     */
    protected function setFieldOptions( $formField, $questionName, array $questionValues )
    {
        $valuesArray = array();

        foreach ( $questionValues as $values )
        {
            if ( is_array($values) )
            {
                foreach ( $values as $value )
                {
                    if(!empty($value->questionText)){
                        $valuesArray[($value->value)] = $value->questionText;
                    }else {
                        $valuesArray[($value->value)] = BOL_QuestionService::getInstance()->getQuestionValueLang($value->questionName, $value->value);
                    }
                }
            }
        }

        $formField->setOptions($valuesArray);
    }

    /**
     * Return acount types array
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function getAccountTypes()
    {
        // get available account types from DB
        $accountTypes = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $accounts = array();



        /* @var $value BOL_QuestionAccount */
        foreach ( $accountTypes as $key => $value )
        {
            $accounts[$value->name] = OW::getLanguage()->text('base', 'questions_account_type_' . $value->name);
        }

        return $accounts;
    }

    public function setColumnCount( $formElement, $question )
    {
        $formElement->setColumnCount($question['columnCount']);
    }

    /**
     * Set field validator
     *
     * @param FormElement $formField
     * @param array $question
     */
    protected function addFieldValidator( $formField, $question )
    {

    }
}

