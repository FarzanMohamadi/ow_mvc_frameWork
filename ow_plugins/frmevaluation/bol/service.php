<?php
/**
 * 
 * All rights reserved.
 */

/**
 * 
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmevaluation.bol
 * @since 1.0
 */
class FRMEVALUATION_BOL_Service
{
    private static $classInstance;
    public $SECTION_INDEX = 1;
    public $SECTION_RESULTS = 2;
    public $P1_1 = 161;
    public $P1_1_1 = 54;
    public $P1_1_2 = 108;
    public $P1_2 = 256;
    public $P1_2_1 = 193;
    public $P1_2_2 = 225;
    public $P2_1 = 51;
    public $P2_2 = 26;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $answerDao;
    private $categoryDao;
    private $questionDao;
    private $valueDao;
    private $userDao;
    
    private function __construct()
    {
        $this->answerDao = FRMEVALUATION_BOL_AnswerDao::getInstance();
        $this->categoryDao = FRMEVALUATION_BOL_CategoryDao::getInstance();
        $this->questionDao = FRMEVALUATION_BOL_QuestionDao::getInstance();
        $this->valueDao = FRMEVALUATION_BOL_ValueDao::getInstance();
        $this->userDao = FRMEVALUATION_BOL_UserDao::getInstance();
    }

    /***
     * @return array
     */
    public function getAssignUsers(){
        return $this->userDao->getUsers();
    }

    /***
     * @param $userId
     * @return bool
     */
    public function isUserAssigned($userId){
        return $this->userDao->isUserAssigned($userId);
    }

    /***
     * @return array
     */
    public function getLockedUsers(){
        return $this->userDao->getLockedUsers();
    }

    /***
     * @return array
     */
    public function getActiveUsers(){
        return $this->userDao->getActiveUsers();
    }

    /***
     * @param $userId
     * @param $username
     * @param int $lock
     * @return FRMEVALUATION_BOL_User
     */
    public function assignUser($userId, $username, $lock = 0){
        $user = $this->userDao->getUser($userId);
        if($user!=null){
            return $this->userDao->update($userId, $lock);
        }else{
            return $this->userDao->saveUser($userId, $username, $lock);
        }
    }

    /**
     * @param $username
     */
    public function unassignUser($username){
        $this->userDao->deleteUser($username);
    }

    /***
     * @return bool
     */
    public function checkUserPermission(){
        if(!OW::getUser()->isAuthenticated()){
            return false;
        }
        if(OW::getUser()->isAdmin()){
            return true;
        }

        $service = FRMEVALUATION_BOL_Service::getInstance();
        return $service->isUserAssigned(OW::getUser()->getId());
    }

    public function checkUserPermissionForSubmitAnswer(){
        if(!$this->checkUserPermission()){
            return false;
        }
        return !$this->isUserLocked(OW::getUser()->getId());
    }

    /***
     * @param $userId
     * @return bool
     */
    public function isUserLocked($userId){
        return $this->userDao->isUserLocked($userId);
    }

    /***
     * @param $catId
     * @return array
     */
    public function getQuestions($catId){
        return $this->questionDao->getQuestions($catId);
    }

    /***
     * @param $categoryId
     * @param bool $ignoreQuestionWithEmptyValue
     * @return int
     */
    public function getCountOfQuestionsOfCategory($categoryId, $ignoreQuestionWithEmptyValue = true){
        return $this->questionDao->getCountOfQuestionsOfCategory($categoryId, $ignoreQuestionWithEmptyValue);
    }

    /**
     * @param $categoryId
     * @param $userId
     * @return int
     */
    public function getCountOfAnswersOfCategory($categoryId, $userId){
        return $this->answerDao->getCountOfAnswersOfCategory($categoryId, $userId);
    }

    /**
     * @param $questionId
     * @param $userId
     * @return null
     */
    public function checkQuestionsAnswered($questionId, $userId){
        return $this->answerDao->checkQuestionsAnswered($questionId, $userId);
    }

    /**
     * @param $questionId
     * @param $userId
     * @return mixed
     */
    public function getAnswerByQuestionIdAndUserId($questionId, $userId){
        return $this->answerDao->getAnswerByQuestionIdAndUserId($questionId, $userId);
    }

    /***
     * @param $name
     * @param $description
     * @param $icon
     * @return FRMEVALUATION_BOL_Category
     */
    public function saveCategory($name, $description, $icon){
        $order = $this->getMaxOrderOfCategory() +1;
        return $this->categoryDao->saveCategory($name, $description, $order, $icon);
    }

    /**
     * @param $name
     * @param $value
     * @param $questionId
     * @return FRMEVALUATION_BOL_Value
     */
    public function saveValue($name, $value, $questionId){
        return $this->valueDao->saveValue($name, $value, $questionId);
    }

    /***
     * @param $sign
     * @param $questionId
     * @param $description
     * @param $file
     * @param $valueId
     * @return FRMEVALUATION_BOL_Answer
     */
    public function saveAnswer($sign, $questionId, $description, $file, $valueId){
        return $this->answerDao->saveAnswer($sign, $questionId, $description, $file, $valueId);
    }

    /***
     * @param $catId
     * @param $titleValue
     * @param $descriptionValue
     * @param $hasDescribeValue
     * @param $hasFileValue
     * @param $hasVerificationValue
     * @param $weight
     * @param $level
     * @return FRMEVALUATION_BOL_Question
     */
    public function saveQuestion($catId,  $titleValue, $descriptionValue, $hasDescribeValue, $hasFileValue, $hasVerificationValue, $weight, $level){
        $order = $this->getMaxOrderOfQuestion() +1;
        return $this->questionDao->saveQuestion($catId,  $titleValue, $descriptionValue, $hasDescribeValue, $hasFileValue, $hasVerificationValue, $weight, $level, $order);
    }

    /***
     * @param $categoryId
     * @param $name
     * @param $description
     * @param $icon
     * @return FRMEVALUATION_BOL_Category
     */
    public function updateCategory($categoryId, $name, $description, $icon){
        return $this->categoryDao->update($categoryId, $name, $description, $icon);
    }

    /***
     * @param $answerId
     * @param $sign
     * @param $questionId
     * @param $description
     * @param $file
     * @param $valueId
     * @return FRMEVALUATION_BOL_Answer
     */
    public function updateAnswer($answerId, $sign, $questionId, $description, $file, $valueId){
        return $this->answerDao->updateAnswer($answerId, $sign, $questionId, $description, $file, $valueId);
    }

    /***
     * @param $valueId
     * @param $name
     * @param $value
     * @return FRMEVALUATION_BOL_Value
     */
    public function updateValue($valueId, $name, $value){
        return $this->valueDao->updateValue($valueId, $name, $value);
    }

    /***
     * @param $questionId
     * @param $catId
     * @param $titleValue
     * @param $descriptionValue
     * @param $hasDescribeValue
     * @param $hasFileValue
     * @param $hasVerificationValue
     * @param $weight
     * @param $level
     */
    public function updateQuestion($questionId, $catId,  $titleValue, $descriptionValue, $hasDescribeValue, $hasFileValue, $hasVerificationValue, $weight, $level){
        $this->questionDao->update($questionId, $catId,  $titleValue, $descriptionValue, $hasDescribeValue, $hasFileValue, $hasVerificationValue, $weight, $level);
    }

    /***
     * @param $category
     */
    public function saveCategoryByObject($category){
        $this->categoryDao->save($category);
    }

    public function saveQuestionByObject($question){
        $this->questionDao->save($question);
    }

    /***
     * @return int|mixed
     */
    public function getMaxOrderOfCategory(){
        return $this->categoryDao->getMaxOrder();
    }

    /***
     * @return int|mixed
     */
    public function getMaxOrderOfQuestion(){
        return $this->questionDao->getMaxOrder();
    }

    /***
     * @return array
     */
    public function getAllCategories(){
        return $this->categoryDao->getCategories();
    }

    /***
     * @param $categoryId
     * @return FRMEVALUATION_BOL_Category
     */
    public function getCategory($categoryId){
        return $this->categoryDao->getCategory($categoryId);
    }

    public function getValue($valueId){
        return $this->valueDao->getValue($valueId);
    }

    /***
     * @param $categoryId
     */
    public function deleteCategory($categoryId){
        $this->categoryDao->deleteById($categoryId);
    }

    /***
     * @param $valueId
     */
    public function deleteValue($valueId){
        $this->valueDao->deleteById($valueId);
    }

    /***
     * @param $questionId
     */
    public function deleteQuestion($questionId){
        $this->questionDao->deleteById($questionId);
    }

    /***
     * @param $action
     * @param null $nameValue
     * @param null $descriptionValue
     * @param null $iconValue
     * @return Form
     */
    public function getCategoryForm($action, $nameValue = null, $descriptionValue = null, $iconValue = null){
        $form = new Form('category');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $name = new TextField('name');
        $name->setRequired();
        $name->setValue($nameValue);
        $name->setHasInvitation(false);
        $form->addElement($name);

        $description = new Textarea('description');
        $description->setRequired();
        $description->setValue($descriptionValue);
        $description->setHasInvitation(false);
        $form->addElement($description);

        $icon = new FileField('icon');
        $form->addElement($icon);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param $action
     * @param null $nameValue
     * @param null $valueValue
     * @return Form
     */
    public function getValueForm($action, $nameValue = null, $valueValue = null){
        $form = new Form('value');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $name = new TextField('name');
        $name->setRequired();
        $name->setValue($nameValue);
        $name->setHasInvitation(false);
        $form->addElement($name);

        $value = new TextField('value');
        $value->setRequired();
        $value->addValidator(new IntValidator());
        $value->setValue($valueValue);
        $value->setHasInvitation(false);
        $form->addElement($value);

        $submit = new Submit('submitValue');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param $questionId
     * @return FRMEVALUATION_BOL_Question
     */
    public function getQuestion($questionId){
        return $this->questionDao->getQuestion($questionId);
    }

    /***
     * @param $questionId
     * @return array
     */
    public function getValuesOfQuestion($questionId){
        return $this->valueDao->getValues($questionId);
    }

    /***
     * @param $action
     * @param $catId
     * @param null $titleValue
     * @param null $descriptionValue
     * @param null $hasDescribeValue
     * @param null $hasFileValue
     * @param null $hasVerificationValue
     * @param null $weight
     * @param null $level
     * @return Form
     */
    public function getQuestionForm($action, $catId,  $titleValue = null, $descriptionValue = null, $hasDescribeValue = null, $hasFileValue = null, $hasVerificationValue = null, $weight = null, $level = null){
        $form = new Form('questions');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $title = new TextField('title');
        $title->setRequired();
        $title->setValue($titleValue);
        $title->setHasInvitation(false);
        $form->addElement($title);

        $buttons = array(
            BOL_TextFormatService::WS_BTN_BOLD,
            BOL_TextFormatService::WS_BTN_ITALIC,
            BOL_TextFormatService::WS_BTN_UNDERLINE,
            BOL_TextFormatService::WS_BTN_IMAGE,
            BOL_TextFormatService::WS_BTN_LINK,
            BOL_TextFormatService::WS_BTN_ORDERED_LIST,
            BOL_TextFormatService::WS_BTN_UNORDERED_LIST,
            BOL_TextFormatService::WS_BTN_MORE,
            BOL_TextFormatService::WS_BTN_SWITCH_HTML,
            BOL_TextFormatService::WS_BTN_HTML,
            BOL_TextFormatService::WS_BTN_VIDEO
        );
        $description = new WysiwygTextarea('description','frmevaluation', $buttons);
        $description->setSize(WysiwygTextarea::SIZE_L);
        $description->setRequired();
        $description->setValue($descriptionValue);
        $description->setHasInvitation(false);
        $form->addElement($description);

        $hasDescribe = new CheckboxField('hasDescribe');
        $hasDescribe->setValue($hasDescribeValue);
        $form->addElement($hasDescribe);

        $hasFile = new CheckboxField('hasFile');
        $hasFile->setValue($hasFileValue);
        $form->addElement($hasFile);

        $hasVerification = new CheckboxField('hasVerification');
        $hasVerification->setValue($hasVerificationValue);
        $form->addElement($hasVerification);

        $weightField = new TextField('weight');
        $weightField->setRequired();
        $weightField->addValidator(new IntValidator());
        $weightField->setValue($weight);
        $weightField->setHasInvitation(false);
        $form->addElement($weightField);

        $levelField = new Selectbox('level');
        $levelField->setHasInvitation(false);
        $levelField->setOptions($this->getLevelsQuestionOption());
        $levelField->setRequired();
        $levelField->setValue($level);
        $form->addElement($levelField);

        $categories = FRMEVALUATION_BOL_Service::getInstance()->getAllCategories();
        $categoryField = new Selectbox('categoryId');
        $options = array();
        foreach($categories as $category){
            $options[$category->id] = $category->name;
        }
        $categoryField->setHasInvitation(false);
        $categoryField->setOptions($options);
        $categoryField->setRequired();
        $categoryField->setValue($catId);
        $form->addElement($categoryField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    public function getLevelsQuestionOption(){
        $options = array();
        $options['پیشنهاد'] = 'پیشنهاد';
        $options['الزام عادی'] = 'الزام عادی';
        $options['الزام مهم'] = 'الزام مهم';
        $options['الزام اساسی'] = 'الزام اساسی';
        return $options;
    }

    public function getUserForm($action, $formName, $usernameLabel){
        $form = new Form($formName);
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $users = $this->answerDao->getUsers();
        $usersField = new TextField($usernameLabel);
        $usersField->setHasInvitation(false);
        $usersField->setRequired();
        $form->addElement($usersField);

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }


    /**
     * @param $action
     * @param $question
     * @param null $answer
     * @param $userId
     * @return Form
     */
    public function getQuestionDataForm($action, $question, $answer=null, $userId){
        $form = new Form('question');
        $form->setAction($action);
        $form->setMethod(Form::METHOD_POST);
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        if($question->hasDescribe){
            $description = new Textarea('description');
            $description->setHasInvitation(false);
            if($answer!=null){
                $description->setValue($answer->description);
            }
            $form->addElement($description);
        }

        if($question->hasFile){
            $file = new FileField('file');
            $form->addElement($file);
        }

        $values = FRMEVALUATION_BOL_Service::getInstance()->getValuesOfQuestion($question->id);
        $valuesField = new RadioField('values');
        $options = array();
        foreach($values as $value){
            $options[$value->id] = $value->name;
        }
        $valuesField->setOptions($options);
        $valuesField->setRequired();
        if($answer!=null){
            $valuesField->setValue($answer->valueId);
        }
        $form->addElement($valuesField);

        if($question->hasVerification){
            $sign = new TextField('sign');
            $sign->setRequired();
            $sign->setHasInvitation(false);
            if($answer!=null){
                $sign->setValue($answer->sign);
            }
            $form->addElement($sign);

            $verification = new CheckboxField('verification');
            $verification->setRequired();
            if($answer!=null){
                $verification->setValue(true);
            }
            $form->addElement($verification);
        }

        $submit = new Submit('submit');
        $form->addElement($submit);

        return $form;
    }

    /***
     * @param $imageName
     * @param $isImage
     * @return null|string
     */
    public function saveFile($imageName, $isImage){
        if($isImage) {
            if (!((int)$_FILES[$imageName]['error'] !== 0 || !is_uploaded_file($_FILES[$imageName]['tmp_name']) || !UTIL_File::validateImage($_FILES[$imageName]['name']))) {
                $iconName = FRMSecurityProvider::generateUniqueId() . '.' . UTIL_File::getExtension($_FILES[$imageName]['name']);
                $userfilesDir = OW::getPluginManager()->getPlugin('frmevaluation')->getUserFilesDir();
                $tmpImgPath = $userfilesDir . $iconName;
                $image = new UTIL_Image($_FILES[$imageName]['tmp_name']);
                $image->saveImage($tmpImgPath);
                return $iconName;
            }
        }else if(!((int)$_FILES[$imageName]['error'] !== 0 || !is_uploaded_file($_FILES[$imageName]['tmp_name']))){
            $iconName = FRMSecurityProvider::generateUniqueId() . '.' . UTIL_File::getExtension($_FILES[$imageName]['name']);
            $userfilesDir = OW::getPluginManager()->getPlugin('frmevaluation')->getUserFilesDir();
            $tmpImgPath = $userfilesDir . $iconName;
            $storage = new BASE_CLASS_FileStorage();
            $storage->copyFile($_FILES[$imageName]['tmp_name'], $tmpImgPath);
            return $iconName;
        }

        return null;
    }

    /***
     * @param $iconName
     * @return string
     */
    public function getFile($iconName){
        return OW::getPluginManager()->getPlugin('frmevaluation')->getUserFilesUrl() . $iconName;
    }


    /***
     * @param $sectionId
     * @param $userId
     * @return BASE_CMP_ContentMenu
     */
    public function getAdminSections($sectionId, $userId)
    {
        $menu = new BASE_CMP_ContentMenu();

        $menuItem = new BASE_MenuItem();
        $menuItem->setLabel(OW::getLanguage()->text('frmevaluation', 'categories'));
        $menuItem->setIconClass('ow_ic_info ow_dynamic_color_icon');
        $menuItem->setUrl(OW::getRouter()->urlForRoute('frmevaluation.index'));
        $menuItem->setKey($this->getInstance()->SECTION_INDEX);
        $menuItem->setActive($sectionId == $this->getInstance()->SECTION_INDEX ? true : false);
        $menuItem->setOrder(0);
        $menu->addElement($menuItem);

        $menuItem = new BASE_MenuItem();
        $menuItem->setLabel(OW::getLanguage()->text('frmevaluation', 'results_header'));
        $menuItem->setIconClass('ow_ic_dashboard ow_dynamic_color_icon');
        $menuItem->setUrl(OW::getRouter()->urlForRoute('frmevaluation.results.user', array('userId' => $userId)));
        $menuItem->setKey($this->getInstance()->SECTION_RESULTS);
        $menuItem->setActive($sectionId == $this->getInstance()->SECTION_RESULTS ? true : false);
        $menuItem->setOrder(1);
        $menu->addElement($menuItem);

        return $menu;
    }

    /***
     * @param $userId
     * @return string
     */
    public function getAggregateUserResult($userId){
        if(!isset($userId)){
            return '';
        }

        $A = 0;
        $B = 0;
        $C = 0;
        $D = 0;
        $numberOfQuestionByWeightOfA = 0;
        $numberOfQuestionByWeightOfB = 0;

        $query = 'select iq.`level`, count(*) as count from '.OW_DB_PREFIX.'frmevaluation_question iq group by iq.`level`;';
        $results = array();
        $numberOfQuestionsByWeight = OW::getDbo()->queryForList($query);

        foreach($numberOfQuestionsByWeight as $numberOfQuestionByWeight){
            if($numberOfQuestionByWeight['level'] == $this->getWeightOfQuestions()[3]['level']){
                $numberOfQuestionByWeightOfA = $numberOfQuestionByWeight['count'];
            }else if($numberOfQuestionByWeight['level'] == $this->getWeightOfQuestions()[2]['level']){
                $numberOfQuestionByWeightOfB = $numberOfQuestionByWeight['count'];
            }
        }

        $query = 'select iq.`level`, sum(iv.value) as value from '.$this->questionDao->getTableName().' iq, '.$this->answerDao->getTableName().' ia, '.$this->valueDao->getTableName().' iv where ia.userId = :userId and ia.questionId = iq.id and iv.id = ia.valueId group by iq.`level` ; ';
        $resultsQuery = OW::getDbo()->queryForList($query, array('userId' => $userId));
        foreach($resultsQuery as $resultQuery){
            if($this->getWeightOfQuestions()[0]['level'] == $resultQuery['level']){ //its C
                $C = $resultQuery['value'];
            }else if($this->getWeightOfQuestions()[1]['level'] == $resultQuery['level']){ //its D
                $D = $resultQuery['value'];
            }else if($this->getWeightOfQuestions()[2]['level'] == $resultQuery['level']){//its B
                $B = $resultQuery['value'];
            }else if($this->getWeightOfQuestions()[3]['level'] == $resultQuery['level']){//its A
                $A = $resultQuery['value'];
            }
        }

        $P1 = ($A * 4 + $B * 3)/100;
        $P2 = ($C + $D)/100;
        $degree = '';
        if($P1 < $this->getInstance()->P1_1){
            if($P1 < $this->getInstance()->P1_1_1){
                $degree = 'I';
            }else{
                if($P1 < $this->getInstance()->P1_1_2){
                    $degree = 'H';
                }else{
                    $degree = 'G';
                }
            }
        }else{
            if($P1 < $this->getInstance()->P1_2){
                if($P1 < $this->getInstance()->P1_2_1){
                    $degree = 'F';
                }else{
                    if($P1 < $this->getInstance()->P1_2_2){
                        $degree = 'E';
                    }else{
                        $degree = 'D';
                    }
                }
            }else{

                if($numberOfQuestionByWeightOfA * 100 != $A){
                    $degree = 'C';
                }else{
                    if($numberOfQuestionByWeightOfB * 100 != $B){
                        $degree = 'B';
                    }else{
                        $degree = 'A';
                    }
                }

                if($P2 == $this->getInstance()->P2_1){
                    $degree .= '++';
                }else{
                    if($P2 < $this->getInstance()->P2_2){
                        //Do Nothing
                    }else{
                        $degree .= '+';
                    }
                }
            }
        }

        return $degree;
    }

    public function getBackgroundColorOfDegree($degree){
        if (strpos($degree, 'A') !== false || strpos($degree, 'B') !== false || strpos($degree, 'C') !== false) {
            return 'evaluation_green';
        }else if (strpos($degree, 'D') !== false || strpos($degree, 'E') !== false || strpos($degree, 'F') !== false) {
            return 'evaluation_yellow';
        }

        return 'evaluation_red';
    }

    public function getUserResult($userId){
        if(!isset($userId)){
            return;
        }

        $categories = $this->getAllCategories();
        $categories = array_reverse($categories);

        $categoriesName = array();
        foreach($categories as $category){
            $categoriesName[] = $category->name;
        }

        $questionWeights = $this->getWeightOfQuestions();
        $results = array();
        $jsResult = array();

        $queryForGettingUserResult = 'select ic.id as categoryId, iq.`level`, sum(iq.weight * iv.value) as value from '.$this->questionDao->getTableName().' iq, '.$this->answerDao->getTableName().' ia, '.$this->valueDao->getTableName().' iv, '.$this->categoryDao->getTableName().' ic where ic.id = iq.categoryId and ia.userId = :userId and ia.questionId = iq.id and iv.id = ia.valueId  group by iq.`level`, ic.id; ';
        $userResultsQuery = OW::getDbo()->queryForList($queryForGettingUserResult, array('userId' => $userId));
        foreach($userResultsQuery as $userResultQuery){
            $results['userData'][$userResultQuery['level']][$userResultQuery['categoryId']] = $userResultQuery['value'];
        }

        $counter = 1;
        foreach($questionWeights as $questionWeight){
            $queryForGettingMaxValueOfEachCategoryInEachWeight = 'select c.id as categoryId, c.name, count(q.weight)*100 as value from '.$this->questionDao->getTableName().' q, '.$this->categoryDao->getTableName().' c where q.`level` = :level and q.categoryId = c.id  group by q.categoryId;';
            $resultsOfQuery = OW::getDbo()->queryForList($queryForGettingMaxValueOfEachCategoryInEachWeight, array('level' => $questionWeight['level']));
            foreach($resultsOfQuery as $resultOfQuery){
                $results['information'][$questionWeight['level']][$resultOfQuery['categoryId']] = $resultOfQuery['value'];
            }


            $userResultValue = array();
            $remainingResultValue = array();

            foreach($categories as $category){
                $tempResult = 0;
                if(isset($results['userData'][$questionWeight['level']][$category->id])){
                    $tempResult = intval($results['userData'][$questionWeight['level']][$category->id]);
                }
                $userResultValue[] = $tempResult;
                $remainingResultValue[] =  intval($results['information'][$questionWeight['level']][$category->id]) - $tempResult;
            }

            $userValueName = "{name: '".OW::getLanguage()->text('frmevaluation', 'user_value')."',data: [".str_replace('\'','',OW::getDbo()->mergeInClause($userResultValue))."]}";
            $remainingValueName = "{name: '".OW::getLanguage()->text('frmevaluation', 'remaining_value')."',data: [".str_replace('\'','',OW::getDbo()->mergeInClause($remainingResultValue))."]}";

            $jsResult[] = $this->getJsForEachChart('container'.$counter, $questionWeight['title'], OW::getDbo()->mergeInClause($categoriesName ), '', $userValueName,  $remainingValueName);
            $counter++;
        }

        return $jsResult;
    }

    public function getJsForEachChart($divName, $chartTitle, $categories, $verticalLabel, $userValueName, $remainingValueName){
        $js = '$(function () {
            $(\'#'.$divName.'\').highcharts({
                chart: {
                    type: \'column\'
                },
                title: {
                    text: \''.$chartTitle.'\'
                },
                xAxis: {
                    categories: ['.$categories.']
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: \''.$verticalLabel.'\'
                    },
                    stackLabels: {
                        enabled: true,
                        style: {
                            fontWeight: \'bold\',
                            color: (Highcharts.theme && Highcharts.theme.textColor) || \'gray\'
                        }
                    }
                },
                legend: {
                    align: \'right\',
                    x: 0,
                    verticalAlign: \'top\',
                    y: 5,
                    floating: true,
                    backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || \'white\',
                    borderColor: \'#CCC\',
                    borderWidth: 1,
                    shadow: false
                },
                tooltip: {
                    useHTML: true,
                    headerFormat: \'<b>{point.x}</b><br/>\',
                    pointFormat: \'<div style="text-align: center;">{series.name}: {point.y:.0f}<br/>'.OW::getLanguage()->text('frmevaluation', 'total_value').': {point.stackTotal}</div>\'
                },
                plotOptions: {
                    column: {
                        stacking: \'percent\',
                        dataLabels: {
                            enabled: true,
                            color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || \'white\',
                            style: {
                                textShadow: \'0 0 3px black\'
                            }
                        }
                    }
                },
                series: ['.$remainingValueName.', '.$userValueName.']
            });
        });';

        return $js;
    }

    /***
     * @return array
     */
    public function getWeightOfQuestions(){
        $weights = array();
        $weights[0]['level'] = 'پیشنهاد';
        $weights[0]['value'] = 1;
        $weights[0]['title'] = OW::getLanguage()->text('frmevaluation', 'requirement_suggest');

        $weights[1]['level'] = 'الزام عادی';
        $weights[1]['value'] = 1;
        $weights[1]['title'] = OW::getLanguage()->text('frmevaluation', 'requirement_normal');

        $weights[2]['level'] = 'الزام مهم';
        $weights[2]['value'] = 3;
        $weights[2]['title'] = OW::getLanguage()->text('frmevaluation', 'requirement_important');

        $weights[3]['level'] = 'الزام اساسی';
        $weights[3]['value'] = 4;
        $weights[3]['title'] = OW::getLanguage()->text('frmevaluation', 'requirement_fundamental');

        return $weights;
    }
}
