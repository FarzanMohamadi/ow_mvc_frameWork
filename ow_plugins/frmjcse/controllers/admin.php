<?php
class FRMJCSE_CTRL_Admin extends OW_ActionController
{
    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }

    public function index()
    {
        if( !OW::getUser()->isAuthenticated() && !OW::getUser()->isAuthorized('frmjcse','edit')) {
            throw new Redirect404Exception();
        }
        $this->setPageTitle($this->text('frmjcse','admin_issue_title'));
        $this->setPageHeading($this->text('frmjcse','admin_issue_heading'));
        $issueDetails = array();
        $deleteUrls = array();
        $issues = FRMJCSE_BOL_Service::getInstance()->getIssueList();
        foreach ( $issues as $issue)
        {
            $issueDetails[$issue->id]['id'] = $issue->id;
            $issueDetails[$issue->id]['title'] = $issue->title;
            $issueDetails[$issue->id]['volume'] = $issue->volume;
            $issueDetails[$issue->id]['no'] = $issue->no;
            $issueDetails[$issue->id]['posterfile'] = $issue->posterfile;
            $issueDetails[$issue->id]['file'] = $issue->file;
            $issueDetails[$issue->id]['editIssue'] = OW::getRouter()->urlForRoute("frmjcse.admin.issue.edit", ['issueid'=>$issue->id]);
            $issueDetails[$issue->id]['addArticle'] = OW::getRouter()->urlForRoute("frmjcse.admin.issue", ['issueid'=>$issue->id]);
            $deleteUrls[$issue->id] = OW::getRouter()->urlFor(__CLASS__,'deleteIssue',array('id'=>$issue->id));
        }
        $this->assign("issues",$issueDetails);
        $this->assign("deleteUrls",$deleteUrls);
        $form = new Form('add_issue');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setLabel($this->text('frmjcse', 'label_issue_title'));
        $form->addElement($fieldTitle);

        $fieldVolume = new TextField('volume');
        $fieldVolume->setRequired();
        $fieldVolume->setLabel($this->text('frmjcse', 'label_issue_volume'));
        $form->addElement($fieldVolume);

        $fieldNo = new TextField('no');
        $fieldNo->setRequired();
        $fieldNo->setLabel($this->text('frmjcse', 'label_issue_no'));
        $form->addElement($fieldNo);

        $fieldPoseterfile = new FileField('posterfile');
        $fieldPoseterfile->setLabel($this->text('frmjcse', 'label_issue_poseterfile'));
        $form->addElement($fieldPoseterfile);

        $fieldFile = new FileField('file');
        $fieldFile->setLabel($this->text('frmjcse', 'label_issue_file'));
        $form->addElement($fieldFile);

        $submit = new Submit('add_issue');
        $submit->setValue($this->text('frmjcse', 'form_add_issue_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $posterUrl = '';
                $fileUrl = '';
                if (!empty($_FILES['posterfile']["tmp_name"]))
                {
                    $bundle = FRMSecurityProvider::generateUniqueId();
                    $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['posterfile'],$bundle);
                    BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                    $posterUrl = $dtoArr['url'];
                }
                if (!empty($_FILES['file']["tmp_name"]))
                {
                    $bundle = FRMSecurityProvider::generateUniqueId();
                    $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['file'], $bundle);
                    BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                    $fileUrl = $dtoArr['url'];
                }
                FRMJCSE_BOL_Service::getInstance()->addIssue($data['title'], $data['volume'], $data['no'], $posterUrl, $fileUrl);
                $this->redirect();
            }
        }

    }

    public function issue($params)
    {
        if(!isset($params['issueid']) || !(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit'))) {
            throw new Redirect404Exception();
        }
        $issueid = $params['issueid'];
        $this->setPageTitle($this->text('frmjcse','admin_article_title'));
        $this->setPageHeading($this->text('frmjcse','admin_article_heading') . " : " . FRMJCSE_BOL_Service::getInstance()->getIssueById($issueid)->title);
        $redirectUrl = OW::getRouter()->urlForRoute("frmjcse.admin");
        $this->assign("redirectUrl",$redirectUrl);
        $articleDetails = array();
        $deleteUrls = array();
        $articles = FRMJCSE_BOL_Service::getInstance()->getArticleListOfIssue($issueid);
        foreach ( $articles as $article)
        {
            /* @var FRMJCSE_BOL_Article $article */
            $articleDetails[$article->id]['id'] = $article->id;
            $articleDetails[$article->id]['title'] = $article->title;
            $articleDetails[$article->id]['abstract'] = $article->abstract;
            $articleDetails[$article->id]['citation'] = $article->citation;
            $articleDetails[$article->id]['file'] = $article->file;
            $articleDetails[$article->id]['active'] = $article->active;
            $articleDetails[$article->id]['issueid'] = $article->issueid;
            $articleDetails[$article->id]['startPage'] = $article->startPage;
            $articleDetails[$article->id]['endPage'] = $article->endPage;
            $articleDetails[$article->id]['keywords'] = FRMJCSE_BOL_Service::getInstance()->getKeywordListNameByArticleId($article->id);
            $articleDetails[$article->id]['authors'] = FRMJCSE_BOL_Service::getInstance()->getAuthorListNameByArticleId($article->id);
            $articleDetails[$article->id]['editArticle'] = OW::getRouter()->urlForRoute("frmjcse.admin.article.edit", ['articleid'=>$article->id]);
            $deleteUrls[$article->id] = OW::getRouter()->urlFor(__CLASS__,'deleteArticle',array('id'=>$article->id,'issueid'=>$issueid));
        }
        $this->assign("articles",$articleDetails);
        $this->assign("deleteUrls",$deleteUrls);
        $form = new Form('add_article');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setLabel($this->text('frmjcse', 'label_article_title'));
        $form->addElement($fieldTitle);

        $fieldAbstract = new WysiwygTextarea('abstract', 'frmjcse');
        $fieldAbstract->setLabel($this->text('frmjcse', 'label_article_abstract'));
        $form->addElement($fieldAbstract);

        $fieldFile = new FileField('file');
        $fieldFile->setLabel($this->text('frmjcse', 'label_article_file'));
        $form->addElement($fieldFile);

        $fieldActive = new CheckboxField('active');
        $fieldActive->setLabel($this->text('frmjcse', 'label_article_active'));
        $fieldActive->setValue(true);
        $form->addElement($fieldActive);

        $fieldKeywords = new TagsInputField('keywords');
        $fieldKeywords->setLabel($this->text('frmjcse', 'label_article_keywords'));
        $form->addElement($fieldKeywords);

        $fieldCitation = new Textarea('citation');
        $fieldCitation->setLabel($this->text('frmjcse', 'label_article_citation'));
        $form->addElement($fieldCitation);

        $fieldStartPage = new TextField('startPage');
        $fieldStartPage->setRequired();
        $fieldStartPage->setLabel($this->text('frmjcse', 'label_article_start_page'));
        $validator = new IntValidator();
        $validator->setMaxValue(99999);
        $validator->setMinValue(1);
        $fieldStartPage->addValidator($validator);
        $fieldStartPage->setValue(1);
        $form->addElement($fieldStartPage);

        $fieldEndPage = new TextField('endPage');
        $fieldEndPage->setRequired();
        $fieldEndPage->setLabel($this->text('frmjcse', 'label_article_end_page'));
        $validator = new IntValidator();
        $validator->setMaxValue(99999);
        $validator->setMinValue(1);
        $fieldEndPage->addValidator($validator);
        $fieldEndPage->setValue(10);
        $form->addElement($fieldEndPage);

        $submit = new Submit('add_article');
        $submit->setValue($this->text('frmjcse', 'form_add_article_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $fileUrl = '';
                if (!empty($_FILES['file']["tmp_name"]))
                {
                    $bundle = FRMSecurityProvider::generateUniqueId();
                    $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['file'],$bundle);
                    BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                    $fileUrl = $dtoArr['url'];
                }
                $article = null;
                $article = FRMJCSE_BOL_Service::getInstance()->addArticle($data['title'], $data['abstract'], $data['citation'],
                    $fileUrl, $data['active'], $issueid, $data['startPage'], $data['endPage']);
                $articleid = $article->getId();

                foreach ($data['keywords'] as $keyword)
                {
                    FRMJCSE_BOL_Service::getInstance()->addKeyword($keyword,$articleid);
                }

                for($i=0;$i<count($_POST['author_name']); $i++)
                {
                    if(empty($_POST['author_name'][$i])){
                        continue;
                    }
                    $name = $_POST['author_name'][$i];
                    $email = (empty($_POST['author_email'][$i]))?'':$_POST['author_email'][$i];
                    $affliation = (empty($_POST['author_affliation'][$i]))?'':$_POST['author_affliation'][$i];
                    FRMJCSE_BOL_Service::getInstance()->addAuthor($name,$articleid,$email,$affliation);
                }
                $this->redirect();
            }
        }

    }

    public function editArticle($params)
    {
        if(!isset($params['articleid']) || !(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit'))) {
            throw new Redirect404Exception();
        }
        $articleid = $params['articleid'];
        $this->setPageTitle($this->text('frmjcse','admin_article_edit_title'));
        $this->setPageHeading($this->text('frmjcse','admin_article_edit_heading') . " : " . FRMJCSE_BOL_Service::getInstance()->getArticleById($articleid)->title);
        $article = FRMJCSE_BOL_Service::getInstance()->getArticleById($articleid);
        $redirectUrl = OW::getRouter()->urlForRoute("frmjcse.admin.issue",["issueid"=> $article->issueid]);
        $this->assign("redirectUrl",$redirectUrl);
        $form = new Form('edit_article');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setLabel($this->text('frmjcse', 'label_article_title'));
        $fieldTitle->setValue($article->title);
        $form->addElement($fieldTitle);

        $fieldAbstract = new WysiwygTextarea('abstract', 'frmjcse');
        $fieldAbstract->setLabel($this->text('frmjcse', 'label_article_abstract'));
        $fieldAbstract->setValue($article->abstract);
        $form->addElement($fieldAbstract);

        $fieldFile = new FileField('file');
        $fieldFile->setLabel($this->text('frmjcse', 'label_article_file'));
        $fieldChangeFile = new CheckboxField('change_file');
        $fieldChangeFile->setLabel($this->text('frmjcse','label_change_fileInput'));
        $form->addElement($fieldFile);
        $form->addElement($fieldChangeFile);

        $fieldActive = new CheckboxField('active');
        $fieldActive->setLabel($this->text('frmjcse', 'label_article_active'));
        $fieldActive->setValue($article->active);
        $form->addElement($fieldActive);

        $fieldKeywords = new TagsInputField('keywords');
        $fieldKeywords->setLabel($this->text('frmjcse', 'label_article_keywords'));
        $fieldKeywords->setValue(FRMJCSE_BOL_Service::getInstance()->getKeywordListNameByArticleId($articleid));
        $form->addElement($fieldKeywords);

        $this->assign('author_data',FRMJCSE_BOL_Service::getInstance()->getAuthorListByArticleId($articleid));

        $fieldCitation = new Textarea('citation');
        $fieldCitation->setLabel($this->text('frmjcse', 'label_article_citation'));
        $fieldCitation->setValue($article->citation);
        $form->addElement($fieldCitation);

        $fieldStartPage = new TextField('startPage');
        $fieldStartPage->setLabel($this->text('frmjcse', 'label_article_start_page'));
        $validator = new IntValidator();
        $validator->setMaxValue(99999);
        $validator->setMinValue(1);
        $fieldStartPage->addValidator($validator);
        $fieldStartPage->setValue($article->startPage);
        $form->addElement($fieldStartPage);

        $fieldEndPage = new TextField('endPage');
        $fieldEndPage->setLabel($this->text('frmjcse', 'label_article_end_page'));
        $validator = new IntValidator();
        $validator->setMaxValue(99999);
        $validator->setMinValue(1);
        $fieldEndPage->addValidator($validator);
        $fieldEndPage->setValue($article->endPage);
        $form->addElement($fieldEndPage);

        $fieldDorl = new TextField('dorl');
        $fieldDorl->setLabel('DORL.net Code');
        $fieldDorl->setValue($article->getExtra('dorl'));
        $form->addElement($fieldDorl);

        $submit = new Submit('edit_article');
        $submit->setValue($this->text('frmjcse', 'form_edit_article_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                if($data['change_file']==1)
                {
                    $fileUrl = '';
                    if (!empty($_FILES['file']["tmp_name"]))
                    {
                        $bundle = FRMSecurityProvider::generateUniqueId();
                        $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['file'],$bundle);
                        BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                        $fileUrl = $dtoArr['url'];
                    }
                    FRMJCSE_BOL_Service::getInstance()->editArticle($articleid,$data['title'], $data['abstract'], $data['citation'],
                        $fileUrl, $data['active'], $article->issueid, $data['startPage'], $data['endPage'], $data['dorl']);

                } else{
                    FRMJCSE_BOL_Service::getInstance()->editArticle($articleid,$data['title'], $data['abstract'], $data['citation'],
                        $article->file, $data['active'], $article->issueid, $data['startPage'], $data['endPage'], $data['dorl']);
                }

                FRMJCSE_BOL_Service::getInstance()->deleteKeywordsByArticleId($articleid);
                foreach ($data['keywords'] as $keyword)
                {
                    FRMJCSE_BOL_Service::getInstance()->addKeyword($keyword,$articleid);
                }

                FRMJCSE_BOL_Service::getInstance()->deleteAuthorsByArticleId($articleid);
                for($i=0;$i<count($_POST['author_name']); $i++)
                {
                    if(empty($_POST['author_name'][$i])){
                        continue;
                    }
                    $name = $_POST['author_name'][$i];
                    $email = (empty($_POST['author_email'][$i]))?'':$_POST['author_email'][$i];
                    $affliation = (empty($_POST['author_affliation'][$i]))?'':$_POST['author_affliation'][$i];
                    FRMJCSE_BOL_Service::getInstance()->addAuthor($name,$articleid,$email,$affliation);
                }
                $this->redirect(OW::getRouter()->urlForRoute('frmjcse.admin.issue',['issueid'=>$article->issueid]));
            }
        }

    }

    public function editIssue($params)
    {
        if(!isset($params['issueid']) || !(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit'))) {
            throw new Redirect404Exception();
        }
        $issueid = $params['issueid'];
        $issue = FRMJCSE_BOL_Service::getInstance()->getIssueById($issueid);
        $this->setPageTitle($this->text('frmjcse','admin_issue_edit_title'));
        $this->setPageHeading($this->text('frmjcse','admin_issue_edit_heading') . " : " . FRMJCSE_BOL_Service::getInstance()->getIssueById($issueid)->title);
        $redirectUrl = OW::getRouter()->urlForRoute("frmjcse.admin");
        $this->assign("redirectUrl",$redirectUrl);
        $form = new Form('edit_issue');
        $form->setEnctype(Form::ENCTYPE_MULTYPART_FORMDATA);
        $this->addForm($form);

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setLabel($this->text('frmjcse', 'label_issue_title'));
        $fieldTitle->setValue($issue->title);
        $form->addElement($fieldTitle);

        $fieldVolume = new TextField('volume');
        $fieldVolume->setRequired();
        $fieldVolume->setLabel($this->text('frmjcse', 'label_issue_volume'));
        $fieldVolume->setValue($issue->volume);
        $form->addElement($fieldVolume);

        $fieldNo = new TextField('no');
        $fieldNo->setRequired();
        $fieldNo->setLabel($this->text('frmjcse', 'label_issue_no'));
        $fieldNo->setValue($issue->no);
        $form->addElement($fieldNo);

        $fieldPosterfile = new FileField('posterfile');
        $fieldPosterfile->setLabel($this->text('frmjcse', 'label_issue_posterfile'));
        $fieldChangeFile = new CheckboxField('change_posterfile');
        $fieldChangeFile->setLabel($this->text('frmjcse','label_change_posterfileInput'));
        $form->addElement($fieldPosterfile);
        $form->addElement($fieldChangeFile);

        $fieldFile = new FileField('file');
        $fieldFile->setLabel($this->text('frmjcse', 'label_issue_file'));
        $fieldChangeFile = new CheckboxField('change_file');
        $fieldChangeFile->setLabel($this->text('frmjcse','label_change_fileInput'));
        $form->addElement($fieldFile);
        $form->addElement($fieldChangeFile);

        $submit = new Submit('edit_issue');
        $submit->setValue($this->text('frmjcse', 'form_edit_issue_submit'));
        $form->addElement($submit);
        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                if($data['change_file']==1 && $data['change_posterfile']==1)
                {
                    $fileUrl = '';
                    $posterfileUrl = '';
                    if (!empty($_FILES['file']["tmp_name"]))
                    {
                        $bundle = FRMSecurityProvider::generateUniqueId();
                        $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['file'],$bundle);
                        BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                        $fileUrl = $dtoArr['url'];
                    }
                    if (!empty($_FILES['posterfile']["tmp_name"]))
                    {
                        $bundle = FRMSecurityProvider::generateUniqueId();
                        $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['posterfile'],$bundle);
                        BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                        $posterfileUrl = $dtoArr['url'];
                    }
                    FRMJCSE_BOL_Service::getInstance()->editIssue($issueid,$data['title'], $data['volume'], $data['no'], $posterfileUrl, $fileUrl);

                }else if($data['change_file']==1){
                    $fileUrl = '';
                    if (!empty($_FILES['file']["tmp_name"]))
                    {
                        $bundle = FRMSecurityProvider::generateUniqueId();
                        $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['file'],$bundle);
                        BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                        $fileUrl = $dtoArr['url'];
                    }
                    FRMJCSE_BOL_Service::getInstance()->editIssue($issueid,$data['title'], $data['volume'], $data['no'], $issue->posterfile, $fileUrl);
                }else if($data['change_posterfile']==1){
                    $posterfileUrl = '';
                    if (!empty($_FILES['posterfile']["tmp_name"]))
                    {
                        $bundle = FRMSecurityProvider::generateUniqueId();
                        $dtoArr = BOL_AttachmentService::getInstance()->processUploadedFile('frmjcse', $_FILES['posterfile'],$bundle);
                        BOL_AttachmentService::getInstance()->updateStatusForBundle('frmjcse',$bundle,1);
                        $posterfileUrl = $dtoArr['url'];
                    }
                    FRMJCSE_BOL_Service::getInstance()->editIssue($issueid,$data['title'], $data['volume'], $data['no'], $posterfileUrl, $issue->file);

                }
                else{
                    FRMJCSE_BOL_Service::getInstance()->editIssue($issueid,$data['title'], $data['volume'], $data['no'], $issue->posterfile, $issue->file);
                }
                $this->redirect(OW::getRouter()->urlForRoute('frmjcse.admin'));
            }
        }
    }

    public function deleteIssue($params)
    {
        if(isset($params['id']))
        {
            FRMJCSE_BOL_Service::getInstance()->deleteIssue((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmjcse.admin'));
    }

    public function deleteArticle($params)
    {
        if(isset($params['id']))
        {
            FRMJCSE_BOL_Service::getInstance()->deleteArticle((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute("frmjcse.admin.issue",['issueid'=>$params['issueid']]));
    }
    public function citationSetting()
    {
        if(!(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit'))){
            throw new Redirect404Exception();
        }
        $this->setPageTitle($this->text('frmjcse','admin_citation_setting_title'));
        $this->setPageHeading($this->text('frmjcse','admin_citation_setting_heading'));
        $citationDetails = array();
        $deleteUrls = array();
        $citations = FRMJCSE_BOL_Service::getInstance()->getCitationList();
        foreach ( $citations as $citation)
        {
            $citationDetails[$citation->id]['id'] = $citation->id;
            $citationDetails[$citation->id]['title'] = $citation->title;
            $citationDetails[$citation->id]['format'] = $citation->format;
            $deleteUrls[$citation->id] = OW::getRouter()->urlFor(__CLASS__,'deleteCitation',array('id'=>$citation->id));
        }
        $this->assign("citations",$citationDetails);
        $this->assign("deleteUrls",$deleteUrls);
        $form = new Form('add_citation_format');
        $this->addForm($form);

        $fieldTitle = new TextField('title');
        $fieldTitle->setRequired();
        $fieldTitle->setLabel($this->text('frmjcse', 'label_citation_title'));
        $form->addElement($fieldTitle);

        $fieldFormat = new Textarea('format');
        $fieldFormat->setRequired();
        $fieldFormat->setLabel($this->text('frmjcse', 'label_citation_format'));
        $form->addElement($fieldFormat);

        $submit = new Submit('add');
        $submit->setValue($this->text('frmjcse', 'add_citation_format_submit'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                FRMJCSE_BOL_Service::getInstance()->addCitationFormat($data['title'], $data['format']);
                $this->redirect();
            }
        }
    }
    public function deleteCitation($params)
    {
        if(isset($params['id']))
        {
            FRMJCSE_BOL_Service::getInstance()->deleteCitation((int) $params['id']);
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmjcse.admin.citation'));
    }
}