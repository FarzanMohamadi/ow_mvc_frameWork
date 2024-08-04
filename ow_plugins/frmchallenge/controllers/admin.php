<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmchallenge.controllers
 * @since 1.0
 */
class FRMCHALLENGE_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function index($params)
    {
        $section = 1;
        if (isset($params['section']))
            $section = (int)$params['section'];

        $language = OW::getLanguage();

        $this->assign('section1_url', OW::getRouter()->urlForRoute('frmchallenge.admin.section', array('section' => 1)));
        $this->assign('section1_label', $language->text('frmchallenge', 'categories'));
        $this->assign('section2_url', OW::getRouter()->urlForRoute('frmchallenge.admin.section', array('section' => 2)));
        $this->assign('section2_label', $language->text('frmchallenge', 'questions'));
        $this->assign('section3_url', OW::getRouter()->urlForRoute('frmchallenge.admin.section', array('section' => 3)));
        $this->assign('section3_label', $language->text('frmchallenge', 'general_setting'));

        $this->assign('section', $section);
        switch ($section) {
            case 1:
                $formName = 'addCategory';
                $this->assign('formName', $formName);
                $form = new Form($formName);
                $form->setMethod(Form::METHOD_POST);

                $field = new TextField('category');
                $field->setRequired(true);
                $field->setLabel($language->text('frmchallenge', 'category'));
                $form->addElement($field);

                $field = new Submit('submit');
                $form->addElement($field);

                $this->addForm($form);

                $categories = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->getAllCategories();
                $categoriesList = array();
                foreach ($categories as $category) {
                    $count = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->countQuestionByCategory($category->getId());

                    $categoriesList[] = array(
                        'title' => $category->title,
                        'count' => $count,
                        'editUrl' => OW::getRouter()->urlForRoute('frmchallenge.admin.category.edit', array('catId' => $category->getId()))
                    );
                }
                $this->assign('categories', $categoriesList);

                if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                    $categoryTitle = $form->getValues()['category'];
                    if (isset($categoryTitle)) {
                        $categoryTitle = trim($categoryTitle);
                        $event = OW_EventManager::getInstance()->trigger(new OW_Event('frmwordscorrection.correct_words', array('words' => array($categoryTitle))));
                        if ($event->getData() !== null && is_array($event->getData())) {
                            $result = $event->getData();
                            if (isset($result) && !empty($result))
                                $categoryTitle = $result[0];
                        }
                        if (strlen($categoryTitle) == 0) {
                            OW::getFeedback()->error($language->text('frmchallenge', 'changes_not_successful'));
                        } else {
                            $cat = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findCategoryByTitle($categoryTitle);
                            if (!empty($cat)) {
                                OW::getFeedback()->error($language->text('frmchallenge', 'category_exists'));
                            } else {
                                FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->addCategory($categoryTitle);
                                OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                                $this->redirect();
                            }
                        }
                    }
                }
                break;
            case 2:
                $editTitle = $language->text('frmchallenge','edit');
                OW::getDocument()->addOnloadScript('
                    var input = $("#search");
                    input.on("input",function(e){
                        $.ajax({
                            url: "'.OW::getRouter()->urlForRoute('frmchallenge.admin.question.search').'",
                            type: "POST",
                            data: {query: input.val()},
                            dataType: "json",
                            success: function (data) {
                            debugger;
                                 $("#s1").empty();
                                if (data.result != false) {
                                    for (i = 0; i < data.result.length; i++) {
                                        $("#s1").append(
                                            \'<tr class="ow_high1 draggable-lang-item ow_tr_last">\' +
                                            \'<td>\'+data.result[i][\'title\']+\'</td>\' +
                                            \'<td>\'+data.result[i][\'category\']+\'</td>\' +
                                            \'<td>\'+data.result[i][\'point\']+\'</td>\' +
                                            \'<td class="features">\' +
                                            \'<a class="ow_lbutton ow_green" href="\'+data.result[i][\'editUrl\']+\'" style="display: inline-block;">'.$editTitle.'</a>\' +
                                            \'</td>\' +
                                            \'</tr>\');
                                    }
                                }
                            }
                        });
                    });
                ');
                $formName = 'addQuestion';
                $this->assign('formName', $formName);
                $form = new Form($formName);
                $form->setMethod(Form::METHOD_POST);

                $field = new TextField('title');
                $field->setRequired(true);
                $field->setLabel($language->text('frmchallenge', 'title'));
                $form->addElement($field);

                $field = new TextField('point');
                $field->setRequired(true);
                $field->addValidator(new IntValidator(1));
                $field->setLabel($language->text('frmchallenge', 'point'));
                $form->addElement($field);

                $categories = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->getAllCategories();
                $options = array();
                foreach ($categories as $questionDto) {
                    $options[$questionDto->getId()] = $questionDto->title;
                }

                $category = new Selectbox('category');
                $category->setLabel(OW::getLanguage()->text('frmchallenge', 'category'));
                $category->setHasInvitation(false);
                $category->setOptions($options);
                $category->setRequired(true);
                $form->addElement($category);

                $field = new Submit('submit');
                $form->addElement($field);

                $this->addForm($form);

                $questions = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findQuestions();
                $questionList = array();
                foreach ($questions as $question) {
                    $questionDto = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findCategory($question->categoryId);
                    if (isset($questionDto))
                        $questionList[] = array(
                            'title' => $question->title,
                            'point' => $question->point,
                            'category' => $questionDto->title,
                            'editUrl' => OW::getRouter()->urlForRoute('frmchallenge.admin.question.edit',array('id'=>$question->getId())),
                        );
                }
                $this->assign('questions', $questionList);

                if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
                    $values = $form->getValues();
                    if (isset($values['title']) && isset($values['point']) && isset($values['category'])) {
                        $title = $values['title'];
                        $point = $values['point'];
                        $category = $values['category'];
                        $title = trim($title);
                        $event = OW_EventManager::getInstance()->trigger(new OW_Event('frmwordscorrection.correct_words', array('words' => array($title))));
                        if ($event->getData() !== null && is_array($event->getData())) {
                            $result = $event->getData();
                            if (isset($result) && !empty($result))
                                $title = $result[0];
                        }
                        if (strlen($title) == 0) {
                            OW::getFeedback()->error($language->text('frmchallenge', 'changes_not_successful'));
                        } else {
                            $questionDto = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findQuestionByTitleAndCategory($title,$category);
                            if (!empty($questionDto)) {
                                OW::getFeedback()->error($language->text('frmchallenge', 'question_category_exists'));
                            } else {
                                FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->addQuestion($title,$point,$category);
                                OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                                $this->redirect();
                            }
                        }
                    }
                }
                break;
            case 3:
                $formName = 'general_setting';
                $this->assign('formName', $formName);
                $form = new Form($formName);
                $form->setMethod(Form::METHOD_POST);

                $field = new TextField('solitary_question_count');
                $field->setRequired(true);
                $field->addValidator(new IntValidator(1));
                $field->setValue(OW::getConfig()->getValue('frmchallenge','solitary_question_count'));
                $field->setLabel($language->text('frmchallenge', 'solitary_question_count'));
                $form->addElement($field);

                $field = new TextField('solitary_answer_time');
                $field->setRequired(true);
                $field->addValidator(new IntValidator(1));
                $field->setValue(OW::getConfig()->getValue('frmchallenge','solitary_answer_time') / (60 * 60));
                $field->setLabel($language->text('frmchallenge', 'solitary_answer_time'));
                $form->addElement($field);

                $field = new TextField('universal_question_count');
                $field->setRequired(true);
                $field->addValidator(new IntValidator(1));
                $field->setValue(OW::getConfig()->getValue('frmchallenge','universal_question_count'));
                $field->setLabel($language->text('frmchallenge', 'universal_question_count'));
                $form->addElement($field);

                $field = new TextField('universal_answer_time');
                $field->setRequired(true);
                $field->addValidator(new IntValidator(1));
                $field->setValue(OW::getConfig()->getValue('frmchallenge','universal_answer_time') / (60 * 60));
                $field->setLabel($language->text('frmchallenge', 'universal_answer_time'));
                $form->addElement($field);

                if (!OW::getConfig()->configExists('frmchallenge', 'solitary_enable')) {
                    OW::getConfig()->saveConfig('frmchallenge', 'solitary_enable', 1);
                }
                $field = new Selectbox('solitary_enable');
                $field->setHasInvitation(false);
                $options = array(
                    0 => 0,
                    1 => ow::getLanguage()->text('admin', 'user_active_status')
                );
                $field->setOptions($options);
                $field->setLabel(ow::getLanguage()->text('frmchallenge', 'solitary_challenge_label'));
                $field->setValue(OW::getConfig()->getValue('frmchallenge', 'solitary_enable'));
                $form->addElement($field);

                $field = new Submit('submit');
                $form->addElement($field);

                $this->addForm($form);


                $formImport = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()
                              ->getImportFileForm();
                $this->addForm($formImport);


                if (OW::getRequest()->isPost())
                {

                    if($formImport->isValid($_POST) &&
                        isset($_FILES['file']) &&
                        isset($_FILES['file']['size']) &&
                        $_FILES['file']['size'] > 0)
                    {
                        //process
                        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event('frmclamav.is_file_clean', array('path' => $_FILES['ow_file_attachment']['tmp_name'])));
                        if(isset($checkAnotherExtensionEvent->getData()['clean'])){
                            $isClean = $checkAnotherExtensionEvent->getData()['clean'];
                            if(!$isClean)
                            {
                                OW::getFeedback()->error($language->text('frmclamav', 'file contains virus'));
                                $this->redirect();
                            }
                        }
                        $resultError = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()
                            ->processImportFileForm();
                        if(sizeof($resultError) == 0)
                        {
                            OW::getFeedback()->info($language->text('frmchallenge', 'import_successfully'));
                        }
                        else
                        {
                            $this->assign("linesError", $resultError);
                        }

                    }
                    else if($form->isValid($_POST)) {
                        $values = $form->getValues();
                        if (isset($values['solitary_question_count']))
                        {
                            OW::getConfig()->saveConfig('frmchallenge', 'solitary_question_count', (int)$values['solitary_question_count']);
                        }
                        if (isset($values['solitary_answer_time'])) {
                            OW::getConfig()->saveConfig('frmchallenge', 'solitary_answer_time', (int)$values['solitary_answer_time'] * 60 * 60);
                        }
                        if (isset($values['universal_question_count'])) {
                            OW::getConfig()->saveConfig('frmchallenge', 'universal_question_count', (int)$values['universal_question_count']);
                        }
                        if (isset($values['universal_answer_time'])) {
                            OW::getConfig()->saveConfig('frmchallenge', 'universal_answer_time', (int)$values['universal_answer_time'] * 60 * 60);
                        }
                        if (isset($values['solitary_enable'])) {
                            OW::getConfig()->saveConfig('frmchallenge', 'solitary_enable', (int)$values['solitary_enable']);
                        }
                        OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                        $this->redirect();
                    }else{
                        $this->redirect();
                    }

                }
                break;
            default:
                throw new Redirect404Exception();
        }
    }

    public function editCategory($params)
    {
        if (!isset($params['catId'])) {
            throw new Redirect404Exception();
        }
        $categoryId = (int)$params['catId'];
        $category = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findCategory($categoryId);
        if (!isset($category)) {
            throw new Redirect404Exception();
        }
        $this->assign('returnUrl', OW::getRouter()->urlForRoute('frmchallenge.admin.section', array('section' => 1)));

        $language = OW::getLanguage();

        $form = new Form('editCategory');
        $form->setMethod(Form::METHOD_POST);

        $field = new TextField('category');
        $field->setRequired(true);
        $field->setLabel($language->text('frmchallenge', 'category'));
        $field->setValue($category->title);
        $form->addElement($field);

        $field = new Submit('submit');
        $form->addElement($field);

        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $categoryTitle = $form->getValues()['category'];
            if (isset($categoryTitle)) {
                $categoryTitle = trim($categoryTitle);
                $event = OW_EventManager::getInstance()->trigger(new OW_Event('frmwordscorrection.correct_words', array('words' => array($categoryTitle))));
                if ($event->getData() !== null && is_array($event->getData())) {
                    $result = $event->getData();
                    if (isset($result) && !empty($result))
                        $categoryTitle = $result[0];
                }
                if (strlen($categoryTitle) == 0) {
                    OW::getFeedback()->error($language->text('frmchallenge', 'changes_not_successful'));
                } else {
                    $cat = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findCategoryByTitle($categoryTitle);
                    if (!empty($cat) && $cat->getId() != $category->getId()) {
                        OW::getFeedback()->error($language->text('frmchallenge', 'category_exists'));
                    } else {
                        FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->updateCategory($categoryId, $categoryTitle);
                        OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                        $this->redirect();
                    }
                }
            }
        }
    }

    public function editQuestion($params){
        if (!isset($params['id'])) {
            throw new Redirect404Exception();
        }
        $questionId = (int)$params['id'];
        $question = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findQuestion($questionId);
        if (!isset($question)) {
            throw new Redirect404Exception();
        }
        $this->assign('returnUrl', OW::getRouter()->urlForRoute('frmchallenge.admin.section', array('section' => 2)));

        $language = OW::getLanguage();
        $form = new Form('editQuestion');
        $form->setMethod(Form::METHOD_POST);

        $field = new TextField('title');
        $field->setRequired(true);
        $field->setValue($question->title);
        $field->setLabel($language->text('frmchallenge', 'title'));
        $form->addElement($field);

        $field = new TextField('point');
        $field->setRequired(true);
        $field->addValidator(new IntValidator(1));
        $field->setValue($question->point);
        $field->setLabel($language->text('frmchallenge', 'point'));
        $form->addElement($field);

        $categories = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->getAllCategories();
        $options = array();
        foreach ($categories as $answerDto) {
            $options[$answerDto->getId()] = $answerDto->title;
        }

        $category = new Selectbox('category');
        $category->setLabel(OW::getLanguage()->text('frmchallenge', 'category'));
        $category->setHasInvitation(false);
        $category->setOptions($options);
        $category->setRequired(true);
        $category->setValue($question->categoryId);
        $form->addElement($category);

        $field = new Submit('submit');
        $form->addElement($field);

        $this->addForm($form);

        if (OW::getRequest()->isPost() && $form->isValid($_POST)) {
            $values = $form->getValues();
            if (isset($values['title']) && isset($values['point']) && isset($values['category'])) {
                $title = $values['title'];
                $correct = $values['point'];
                $category = $values['category'];
                $title = trim($title);
                $event = OW_EventManager::getInstance()->trigger(new OW_Event('frmwordscorrection.correct_words', array('words' => array($title))));
                if ($event->getData() !== null && is_array($event->getData())) {
                    $result = $event->getData();
                    if (isset($result) && !empty($result))
                        $title = $result[0];
                }
                if (strlen($title) == 0) {
                    OW::getFeedback()->error($language->text('frmchallenge', 'changes_not_successful'));
                } else {
                    $answerDto = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findQuestionByTitleAndCategory($title,$category);
                    if (!empty($answerDto) && $answerDto->getId() != $question->getId()) {
                        OW::getFeedback()->error($language->text('frmchallenge', 'question_category_exists'));
                    } else {
                        FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->updateQuestion($question->getId(),$title,$correct,$category);
                        OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                        $this->redirect();
                    }
                }
            }
        }

        $answers = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findAnswersByQuestion($questionId);
        $answerList = array();
        foreach ($answers as $answer){
            $answerList[] = array(
                'title' => $answer->title,
                'correct' => $answer->correct === '1',
                'editUrl' => OW::getRouter()->urlForRoute('frmchallenge.admin.answer.edit',array('id'=>$answer->getId())),
            );
        }
        $this->assign('answers',$answerList);

        $answerForm = new Form('addAnswer');
        $answerForm->setMethod(Form::METHOD_POST);

        $field = new TextField('ans_title');
        $field->setRequired(true);
        $field->setLabel($language->text('frmchallenge', 'title'));
        $answerForm->addElement($field);

        $field = new CheckboxField('correct');
        if (FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->questionHasCorrectAnswer($questionId)) {
            $field->addAttribute('disabled', true);
        }
        $field->setValue(false);
        $field->setLabel($language->text('frmchallenge', 'correct'));
        $answerForm->addElement($field);

        $field = new Submit('submit');
        $answerForm->addElement($field);
        $this->addForm($answerForm);
        if(OW::getRequest()->isPost() && $answerForm->isValid($_POST)){
            $values = $answerForm->getValues();
            if (isset($values['ans_title'])) {
                $title = $values['ans_title'];
                $correct = isset($values['correct']);
                $title = trim($title);
                $event = OW_EventManager::getInstance()->trigger(new OW_Event('frmwordscorrection.correct_words', array('words' => array($title))));
                if ($event->getData() !== null && is_array($event->getData())) {
                    $result = $event->getData();
                    if (isset($result) && !empty($result))
                        $title = $result[0];
                }
                if (strlen($title) == 0) {
                    OW::getFeedback()->error($language->text('frmchallenge', 'changes_not_successful'));
                } else {
                    $answerDto = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findAnswerByTitleAndQuestion($title,$questionId);
                    if (!empty($answerDto)) {
                        OW::getFeedback()->error($language->text('frmchallenge', 'answer_question_exists'));
                    } else {
                        if($correct && FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->questionHasCorrectAnswer($questionId)){
                            OW::getFeedback()->error($language->text('frmchallenge', 'question_has_correct_answer'));
                        }else {
                            FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->addAnswer($question->getId(), $title, $correct);
                            OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                            $this->redirect();
                        }
                    }
                }
            }
        }
    }

    public function editAnswer($params){
        if (!isset($params['id']))
            throw new Redirect404Exception();
        $answerId = (int)$params['id'];
        $answer = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findAnswer($answerId);
        if (!isset($answer))
            throw new Redirect404Exception();
        $language = OW::getLanguage();
        $answerForm = new Form('editAnswer');
        $answerForm->setMethod(Form::METHOD_POST);

        $field = new TextField('title');
        $field->setRequired(true);
        $field->setValue($answer->title);
        $field->setLabel($language->text('frmchallenge', 'title'));
        $answerForm->addElement($field);

        $field = new CheckboxField('correct');
        if (FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->questionHasCorrectAnswer($answer->questionId) && !$answer->correct) {
            $field->addAttribute('disabled', true);
        }
        $field->setValue($answer->correct);
        $field->setLabel($language->text('frmchallenge', 'correct'));
        $answerForm->addElement($field);

        $field = new Submit('submit');
        $answerForm->addElement($field);
        $this->addForm($answerForm);
        if(OW::getRequest()->isPost() && $answerForm->isValid($_POST)){
            $values = $answerForm->getValues();
            if (isset($values['title'])) {
                $title = $values['title'];
                $correct = isset($values['correct']);
                $title = trim($title);
                $event = OW_EventManager::getInstance()->trigger(new OW_Event('frmwordscorrection.correct_words', array('words' => array($title))));
                if ($event->getData() !== null && is_array($event->getData())) {
                    $result = $event->getData();
                    if (isset($result) && !empty($result))
                        $title = $result[0];
                }
                if (strlen($title) == 0) {
                    OW::getFeedback()->error($language->text('frmchallenge', 'changes_not_successful'));
                } else {
                    $answerDto = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findAnswerByTitleAndQuestion($title,$answer->questionId);
                    if (!empty($answerDto) && $answerDto[0]->getId() != $answer->getId()) {
                        OW::getFeedback()->error($language->text('frmchallenge', 'answer_question_exists'));
                    } else {
                        if($correct && $correct != $answer->correct && FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->questionHasCorrectAnswer($answer->questionId)){
                            OW::getFeedback()->error($language->text('frmchallenge', 'question_has_correct_answer'));
                        }else {
                            FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->updateAnswer($answer->getId(), $answer->questionId, $title, $correct);
                            OW::getFeedback()->info($language->text('frmchallenge', 'changes_successful'));
                            $this->redirect();
                        }
                    }
                }
            }
        }
    }

    public function search($params){
        if(OW::getRequest()->isAjax()){
            $query = $_POST['query'];
            $questions = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findQuestionsLike($query);
            $questionList = array();
            foreach ($questions as $question) {
                $questionDto = FRMCHALLENGE_BOL_ServiceAdmin::getInstance()->findCategory($question->categoryId);
                if (isset($questionDto))
                    $questionList[] = array(
                        'title' => $question->title,
                        'point' => $question->point,
                        'category' => $questionDto->title,
                        'editUrl' => OW::getRouter()->urlForRoute('frmchallenge.admin.question.edit',array('id'=>$question->getId())),
                    );
            }
            exit(json_encode(array('result'=>$questionList)));
        }
        exit();
    }
}