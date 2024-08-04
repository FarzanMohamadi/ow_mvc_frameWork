<?php
/**
 * Created by PhpStorm.
 * Date: 3/7/18
 * Time: 10:53 AM
 */
class FRMCHALLENGE_BOL_ServiceAdmin
{
    private static $INSTANCE;

    public static function getInstance()
    {
        if (self::$INSTANCE == null) {
            self::$INSTANCE = new self();
        }

        return self::$INSTANCE;
    }

    private function __construct()
    {
    }

    public function countQuestionByCategory($categoryId)
    {
        return FRMCHALLENGE_BOL_QuestionDao::getInstance()->countQuestionByCategory($categoryId);
    }

    public function addCategory($title)
    {
        $category = new FRMCHALLENGE_BOL_Category();
        $category->title = $title;
        FRMCHALLENGE_BOL_CategoryDao::getInstance()->save($category);
        return $category;
    }

    public function updateCategory($categoryId, $title)
    {
        $category = FRMCHALLENGE_BOL_CategoryDao::getInstance()->findById($categoryId);
        $category->title = $title;
        FRMCHALLENGE_BOL_CategoryDao::getInstance()->save($category);
    }

    public function findCategoryByTitle($title)
    {
        $category = FRMCHALLENGE_BOL_CategoryDao::getInstance()->findByTitle($title);
        return $category;
    }

    public function findCategory($categoryId)
    {
        return FRMCHALLENGE_BOL_CategoryDao::getInstance()->findById($categoryId);
    }

    public function findQuestions()
    {
        return FRMCHALLENGE_BOL_QuestionDao::getInstance()->findAll();
    }

    public function findQuestion($questionId)
    {
        return FRMCHALLENGE_BOL_QuestionDao::getInstance()->findById($questionId);
    }

    public function findQuestionByTitleAndCategory($title, $categoryId)
    {
        return FRMCHALLENGE_BOL_QuestionDao::getInstance()->findByTitleAndCategory($title, $categoryId);
    }

    public function findAnswerByTitleAndQuestion($title, $questionId)
    {
        return FRMCHALLENGE_BOL_AnswerDao::getInstance()->findByTitleAndQuestion($title, $questionId);
    }

    public function addQuestion($title, $point, $categoryId)
    {
        $question = new FRMCHALLENGE_BOL_Question();
        $question->title = $title;
        $question->point = $point;
        $question->categoryId = $categoryId;
        FRMCHALLENGE_BOL_QuestionDao::getInstance()->save($question);
        return $question;
    }

    public function updateQuestion($questionId, $title, $point, $categoryId)
    {
        $question = QUESTIONS_BOL_QuestionDao::getInstance()->findById($questionId);
        $question->title = $title;
        $question->point = $point;
        $question->categoryId = $categoryId;
        FRMCHALLENGE_BOL_QuestionDao::getInstance()->save($question);
    }

    public function addAnswer($questionId, $title, $correct)
    {
        $answer = new FRMCHALLENGE_BOL_Answer();
        $answer->title = $title;
        $answer->correct = $correct;
        $answer->questionId = $questionId;
        FRMCHALLENGE_BOL_AnswerDao::getInstance()->save($answer);
    }

    public function updateAnswer($answerId,$questionId, $title, $correct)
    {
        $answer = FRMCHALLENGE_BOL_AnswerDao::getInstance()->findById($answerId);
        $answer->title = $title;
        $answer->correct = $correct;
        $answer->questionId = $questionId;
        FRMCHALLENGE_BOL_AnswerDao::getInstance()->save($answer);
    }

    public function findAnswer($answerId){
        return FRMCHALLENGE_BOL_AnswerDao::getInstance()->findById($answerId);
    }

    public function findAnswersByQuestion($questionId){
        return FRMCHALLENGE_BOL_AnswerDao::getInstance()->findQuestionAnswer($questionId);
    }

    public function findQuestionsLike($query){
        return FRMCHALLENGE_BOL_QuestionDao::getInstance()->findByTitleLike($query);
    }

    public function questionHasCorrectAnswer($questionId){
        $answers =  FRMCHALLENGE_BOL_AnswerDao::getInstance()->findQuestionCorrectAnswer($questionId);
        return !empty($answers);
    }

    public function getAllCategories(){
        return FRMCHALLENGE_BOL_CategoryDao::getInstance()->getAllCategories();
    }

    public function getImportFileForm()
    {
        $formImport = new Form('questions_import');
        $formImport->setMethod(Form::METHOD_POST);
        $formImport->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);

        $file = new FileField('file');
        $formImport->addElement($file);

        $submit = new Submit('submit');
        $submit->setValue(OW::getLanguage()->text('frmchallenge', 'upload_file_submit_label'));
        $formImport->addElement($submit);

        return $formImport;
        
    }

    public function processImportFileForm()
    {
        $language = OW::getLanguage();

        $ignoreData = array();
        if(!((int)$_FILES['file']['error'] !== 0 ||
            !is_uploaded_file($_FILES['file']['tmp_name'])))
        {
            if(UTIL_File::getExtension($_FILES['file']['name']) != 'txt')
            {
                OW::getFeedback()->error(OW::getLanguage()->text('frmusersimport', 'error_import_extension'));
            }

            $path = $_FILES['file']['tmp_name'];
            $file = fopen($path, 'r');
            $data = fread($file, filesize($path));
            fclose($file);

            $lines = preg_split("/\\r\\n|\\r|\\n/", $data);
            if(sizeof($lines) == 0)
            {
                $ignoreData[] = $language->text('frmchallenge', 'lines_error');
            }
            $count = 0;
            foreach($lines as $line)
            {
                if($count != 0)
                {
                    $item = preg_split('/[\t]/', $line);
                    $item = $this->removeEmptyItemsFromArray($item);
                    //if(sizeof($item) != 1 || $item[0] != ""){
                        if (sizeof($item) == 0)
                        {
                            //empty line
                            //$ignoreData[] = $language->text('frmchallenge', 'line_error', array('line' => $count));
                        }
                        elseif(sizeof($item) == 8)
                        {
                                $titleCategory = $item[0];
                                $category = $this->findCategoryByTitle($titleCategory);
                                if($category == null)
                                {
                                    $category = $this->addCategory($titleCategory);
                                }

                                $question = $this->findQuestionByTitleAndCategory($item[1], $category->id);
                                if($question  == null)
                                {
                                    $question = $this->addQuestion($item[1], $item[2], $category->id);
                                    $correct = array(
                                        "1" => false,
                                        "2" => false,
                                        "3" => false,
                                        "4" => false,
                                    );

                                    $correct[$item[7]] = true;
                                    $this->addAnswer($question->id, $item[3], $correct[1]);
                                    $this->addAnswer($question->id, $item[4], $correct[2]);
                                    $this->addAnswer($question->id, $item[5], $correct[3]);
                                    $this->addAnswer($question->id, $item[6], $correct[4]);
                                }
                        }
                        else
                        {
                            $ignoreData[] = $language->text('frmchallenge', 'line_question_error', array('line' => $count));
                            $count++;
                            continue;
                        }
                   // }
                }
                $count++;
            }
            return $ignoreData;
        }
        else
        {
            OW::getFeedback()->error(OW::getLanguage()->text('frmchallenge', 'file_empty'));
        }

        return $ignoreData;
    }

    public function removeEmptyItemsFromArray($array){
        $newArray = array();
        foreach ($array as $item){
            if($item != ""){
                $newArray[] = $item;
            }
        }

        return $newArray;
    }
}