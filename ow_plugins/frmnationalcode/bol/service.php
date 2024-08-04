<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 10/8/2017
 * Time: 10:46 AM
 */
class FRMNATIONALCODE_BOL_Service
{
    private static $INSTANCE;
    public static $NATIONAL_CODE_FIELD_NAME = 'field_national_code';
    public static $NATIONAL_CODE_VALIDATOR_PATTERN = "/^\d{10}$/m";

    public static function getInstance()
    {
        if (self::$INSTANCE === null) {
            self::$INSTANCE = new self();
        }

        return self::$INSTANCE;
    }

    public function __construct()
    {
    }

    public function checkNationalCodeExist($code){
        $questionDao = BOL_QuestionDataDao::getInstance();
        $example = new OW_Example();
        $example->andFieldEqual('questionName', self::$NATIONAL_CODE_FIELD_NAME);
        $example->andFieldEqual('textValue', $code);
        $result = $questionDao->findListByExample($example);
        return sizeof($result)>0?true:false;
    }

    public function on_render_join_form(OW_Event $event){
        $param = $event->getParams();
        if ($param['joinForm']) {
            $joinRealFieldNames = OW_Session::getInstance()->get('join.real_question_list');
            $fieldNationalCodeHashName = '';
            foreach ($joinRealFieldNames as $key => $value){
                if($value == self::$NATIONAL_CODE_FIELD_NAME){
                    $fieldNationalCodeHashName = $key;
                    break;
                }
            }
            $field = $param['joinForm']->getElement($fieldNationalCodeHashName);
            $field->addValidator(new NationalCodeExistenceValidator());
            $field->addValidator(new NationalCodeValidator());
        }
    }

    public function onQuestionFieldCreate(OW_Event $event){
        $param = $event->getParams();
        if (isset($param['element']) && isset($param['field_name']) && $param['field_name'] == self::$NATIONAL_CODE_FIELD_NAME) {
            $param['element']->addValidator(new NationalCodeValidator());
            $validator = new NationalCodeExistenceValidator();
            $validator->setCode($param['element']->getValue());
            $param['element']->addValidator($validator);
        }
    }

    public function removeNationalCodeQuestionField(){
        $question = BOL_QuestionService::getInstance()->findQuestionByName(self::$NATIONAL_CODE_FIELD_NAME);
        BOL_QuestionService::getInstance()->deleteQuestion(array($question->id));
        BOL_QuestionService::getInstance()->deleteQuestionToAccountType(self::$NATIONAL_CODE_FIELD_NAME, array('290365aadde35a97f11207ca7e4279cc'));
    }


    public function addQuestion()
    {
        $question = new BOL_Question();
        $question->name = self::$NATIONAL_CODE_FIELD_NAME;
        $question->required = true;
        $question->onJoin = true;
        $question->onEdit = true;
        $question->onSearch = false;
        $question->onView = false;
        $question->presentation = 'text';
        $question->type = 'text';
        $question->columnCount = 0;
        $question->sectionName = 'f90cde5913235d172603cc4e7b9726e3';
        $question->sortOrder = ( (int) BOL_QuestionService::getInstance()->findLastQuestionOrder($question->sectionName) ) + 1;
        $question->custom = json_encode(array());
        $question->removable = false;
        $questionValues = false;
        $name = OW::getLanguage()->text('frmnationalcode', 'field_national_code_label');
        $description = OW::getLanguage()->text('frmnationalcode', 'field_national_code_description');
        BOL_QuestionService::getInstance()->createQuestion($question, $name, $description, $questionValues, true);
        BOL_QuestionService::getInstance()->addQuestionToAccountType(self::$NATIONAL_CODE_FIELD_NAME, array('290365aadde35a97f11207ca7e4279cc'));
    }
}

class NationalCodeExistenceValidator extends OW_Validator
{
    protected $jsObjectName = null;
    protected $code = null;

    public function __construct()
    {
        $errorMessage = OW::getLanguage()->text('frmnationalcode', 'form_validator_national_code_exists_message');

        if ( empty($errorMessage) )
        {
            $errorMessage = 'mobile Validator Error!';
        }

        $this->setErrorMessage($errorMessage);
    }

    public function setCode($code){
        $this->code = $code;
    }

    public function isValid( $value )
    {
        return parent::isValid1($value);
    }

    public function setJsObjectName( $name )
    {
        if ( !empty($name) )
        {
            $this->jsObjectName = $name;
        }
    }

    public function checkValue( $value )
    {
        if($this->code !== null){
            if($this->code === $value){
                return true;
            }
        }
        return !FRMNATIONALCODE_BOL_Service::getInstance()->checkNationalCodeExist($value);
    }
}
