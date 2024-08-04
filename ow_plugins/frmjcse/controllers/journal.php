<?php
class FRMJCSE_CTRL_Journal extends OW_ActionController
{

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }

    public function issues()
    {
        $this->setPageTitle($this->text('frmjcse', 'issue_index_page_title'));
//        $this->setPageHeading($this->text('frmjcse', 'issue_index_page_heading'));
        $isAdmin = null;
        $redirectUrl = null;
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit')){
            $isAdmin = true;
            $redirectUrl = OW::getRouter()->urlForRoute("frmjcse.admin");
        }
        $this->assign("isAdmin",$isAdmin);
        $this->assign("redirectUrl",$redirectUrl);

        $items = array();
        $issues = FRMJCSE_BOL_Service::getInstance()->getIssueList();
        foreach ($issues as $issue)
        {
            $items[] = array(
                'url' => OW::getRouter()->urlForRoute("frmjcse.issue",["issueid"=>$issue->id]),
                'title' => $issue->title,
                'volume' => $issue->volume,
                'no' => $issue->no,
                'file' => $issue->file
            );
        }
        $this->assign("issues",$items);
    }

    public function issue($params)
    {
        if(!isset($params['issueid'])) {
            throw new Redirect404Exception();
        }
        $service = FRMJCSE_BOL_Service::getInstance();
        $issueid = $params['issueid'];
        if($issueid =='latest'){
            $issueid = $service->getLatestIssueId();
        }
        $issue = $service->getIssueById($issueid);
        if(!isset($issue)){
            throw new Redirect404Exception();
        }
        $this->setPageTitle($issue->title);
        $this->setPageHeading($issue->title);
        $isAdmin = null;
        $redirectUrl = null;
        if(OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit')){
            $isAdmin = true;
            $redirectUrl = OW::getRouter()->urlForRoute("frmjcse.admin.issue",["issueid"=>$issueid]);
        }
        $this->assign("downloadFile",$issue->file);
        $this->assign("posterFile",$issue->posterfile);
        $this->assign("isAdmin",$isAdmin);
        $this->assign("redirectUrl",$redirectUrl);
        if (OW::getLanguage()->getCurrentId() == 3) { // only persian lang
            $xmlUrl = OW::getRouter()->urlForRoute("frmjcse.issue.xml", ["id" => $issueid]);
            $this->assign("xmlUrl",$xmlUrl);
        }
        $this->assign("staticUrl", OW::getPluginManager()->getPlugin('frmjcse')->getStaticUrl());

        $items = array();
        $articles = $service->getArticleListOfIssue($issueid,1);
        foreach ($articles as $article)
        {
            $authors = $service->getAuthorListByArticleId($article->id);
            $authorItems = [];
            foreach ($authors as $author) {
                $authorItems[] = '<span id="author-'.$author->id.'">'.$author->name.'</span>';
            }
            $authorItems = join(" • ", $authorItems);

            $items[] = array(
                'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                'id' => $article->id,
                'title' => $article->title,
                'abstract' => $article->abstract,
                'file' => $article->file,
                'authors' => $authorItems
            );
        }
        $isEmpty=null;
        if(count($items)==0) $isEmpty=true;
        $this->assign("isEmpty",$isEmpty);
        $this->assign("articles",$items);
    }

    public function article($params)
    {
        if(!isset($params['articleid'])) {
            throw new Redirect404Exception();
        }
        $articleid = $params['articleid'];
        $article = FRMJCSE_BOL_Service::getInstance()->getArticleById($articleid);
        if(!isset($article)){
            throw new Redirect404Exception();
        }

        FRMJCSE_BOL_Service::getInstance()->incrementViewsArticle($articleid);

        $this->setPageTitle($article->title);
        $this->setPageHeading($article->title);
        $downloadFile = $article->file;

        if (OW::getLanguage()->getCurrentId() == 3) { // only persian lang
            $xmlUrl = OW::getRouter()->urlForRoute("frmjcse.article.xml", ["id" => $articleid]);
            $this->assign("xmlUrl",$xmlUrl);
        }

        $this->assign("downloadFile",$downloadFile);
        $this->assign("staticUrl", OW::getPluginManager()->getPlugin('frmjcse')->getStaticUrl());
        $this->assign("articleid",$articleid);
        $this->assign("dltimes",$article->dltimes);
        $this->assign("views",$article->views);

        $isAdmin = null;
        $editArticleUrl = null;
        $editCitationUrl = null;
        if (OW::getUser()->isAuthenticated() && OW::getUser()->isAuthorized('frmjcse','edit')) {
            $isAdmin = true;
            $editArticleUrl = OW::getRouter()->urlForRoute("frmjcse.admin.article.edit", ['articleid' => $articleid]);
            $editCitationUrl = OW::getRouter()->urlForRoute("frmjcse.admin.citation");
        }
        $this->assign("isAdmin", $isAdmin);
        $this->assign("editArticleUrl", $editArticleUrl);
        $this->assign("editCitationUrl", $editCitationUrl);

        $citeList = FRMJCSE_BOL_Service::getInstance()->getCitationList();
        $existCite = count($citeList) ? true : null;
        $this->assign("existCite", $existCite);

        $citation = $article->citation;
        $citation = explode ("\n", $citation);

        $authors = FRMJCSE_BOL_Service::getInstance()->getAuthorListByArticleId($articleid);
        $authorItems = array();
        $affliations = [];
        foreach ($authors as $author) {
            $authorItems[] = array(
                'id' => $author->id,
                'name' => $author->name,
                'articleid' => $author->articleid,
                'authorSearch' => OW::getRouter()->urlForRoute('frmjcse.search',['search_text' => $author->name . ":0:0:0:1" ])
            );
            $aff = $author->affliation;
            if(strlen($aff)>4){
                $aff = str_replace('‌',' ', $aff);
                $affliations[] =  $aff;
            }
        }
        $this->assign("authors", $authorItems);

        $items = array(
            'id' => $article->id,
            'title' => $article->title,
            'abstract' => $article->abstract,
            'file' => $article->file,
            'citations' => $citation,
            'affliation' => implode(', ', array_unique($affliations)),
            'startPage' => $article->startPage,
            'endPage' => $article->endPage
        );
        if(!empty($article->getExtra('dorl'))){
            $items['dorl'] = $article->getExtra('dorl');
        }
        $this->assign("article", $items);

        $issue = FRMJCSE_BOL_Service::getInstance()->getIssueById($article->issueid);
        $this->assign("issue", ['title'=>$issue->title, 'url'=>OW::getRouter()->urlForRoute("frmjcse.issue",["issueid"=>$issue->id])]);


        $keywords = FRMJCSE_BOL_Service::getInstance()->getKeywordListByArticleId($articleid);
        $keywordItems = array();
        foreach ($keywords as $keyword) {
            $keywordItems[] = array(
                'id' => $keyword->id,
                'name' => $keyword->name,
                'articleid' => $keyword->articleid,
                'keywordSearch' => OW::getRouter()->urlForRoute('frmjcse.search',['search_text' => $keyword->name . ":0:0:1:0" ])

            );
        }
        $this->assign("keywords", $keywordItems);

        $form = new Form('download_form');
        $form->setAttribute('target', '_blank');
        $this->addForm($form);
//        $fieldCaptcha = new CaptchaField('captcha');
//        $fieldCaptcha->setLabel($this->text('frmjcse', 'captcha_label'));
//        $form->addElement($fieldCaptcha);
        $submit = new Submit('submit');
        $submit->setValue($this->text('frmjcse', 'download_article_button'));
        $form->addElement($submit);

        if ( OW::getRequest()->isPost() )
        {
            $this->assign("submitted", true);
            if ( $form->isValid($_POST) )
            {
                FRMJCSE_BOL_Service::getInstance()->plusDltimesArticle($articleid);
                $this->redirect($downloadFile);
//                header('Pragma: public');
//                header('Expires: 0');
//                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
//                header('Cache-Control: private', false);
//                header('Content-Type: application/pdf');
//                header('Content-Description: File Transfer');
//                header('Content-Disposition: attachment; filename="' . basename($downloadFile) . '";');
//                header('Content-Transfer-Encoding: binary');
//                header('Content-Length: ' . filesize($downloadFile));
//                ob_end_clean();
//                readfile($downloadFile);
            }
        }
    }

    /***
     * @param $params
     * @throws Redirect404Exception
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function xml($params)
    {
        if (!isset($params['id'])) {
            throw new Redirect404Exception();
        }
        $articleid = $params['id'];
        $xml = FRMJCSE_BOL_Service::getInstance()->getXMLContent([$articleid]);

        // display
        header('Content-Type: application/xml');
        print($xml);
        exit();
    }

    /***
     * @param $params
     * @throws Redirect404Exception
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function xmlIssue($params)
    {
        if (!isset($params['id'])) {
            throw new Redirect404Exception();
        }
        $issueId = $params['id'];
        $articles = FRMJCSE_BOL_Service::getInstance()->getArticleListOfIssue($issueId,1);
        $articleIds = [];
        foreach ($articles as $article) {
            $articleIds[] = $article->id;
        }
        $xml = FRMJCSE_BOL_Service::getInstance()->getXMLContent($articleIds);

        // display
        header('Content-Type: application/xml');
        print($xml);
        exit();
    }

    public function search($params)
    {
        $searchText="";
        $fieldSearchInTitleActive = true;
        $fieldSearchInKeywordActive = false;
        $fieldSearchInAbstractActive = false;
        $fieldSearchInAuthorActive = false;;
        if(isset($params['search_text'])) {
            $str=urldecode($params['search_text']);
            $exploded=explode(":",$str);
            if(count($exploded) > 4){
                $searchText = $exploded[0];
                $fieldSearchInAbstractActive = $exploded[1]=='1';
                $fieldSearchInTitleActive = $exploded[2]=='1';
                $fieldSearchInKeywordActive = $exploded[3]=='1';
                $fieldSearchInAuthorActive = $exploded[4]=='1';
            }else if(count($exploded) > 3){
                $searchText = $exploded[0];
                $fieldSearchInAbstractActive = $exploded[1]=='1';
                $fieldSearchInTitleActive = $exploded[2]=='1';
                $fieldSearchInKeywordActive = $exploded[3]=='1';
            }else if(count($exploded) > 2){
                $searchText = $exploded[0];
                $fieldSearchInAbstractActive = $exploded[1]=='1';
                $fieldSearchInTitleActive = $exploded[2]=='1';
            }else if(count($exploded) > 1 ){
                $searchText = $exploded[0];
                $fieldSearchInAbstractActive = $exploded[1]=='1';
            }else{
                $searchText = $exploded[0];
            }
        }

        $this->assign("fieldSearchInAbstractActive",$fieldSearchInAbstractActive);
        $this->assign("fieldSearchInTitleActive",$fieldSearchInTitleActive);
        $this->assign("fieldSearchInKeywordActive",$fieldSearchInKeywordActive);
        $this->assign("fieldSearchInAuthorActive",$fieldSearchInAuthorActive);

        $this->setPageTitle($this->text('frmjcse','search_page_title'));
        $this->setPageHeading($this->text('frmjcse','search_page_header'));

        $form = new Form('search_form');
        $this->addForm($form);

        $fieldSearch = new TextField('search');
        $fieldSearch->setLabel($this->text('frmjcse','search_field_label'));
        $fieldSearch->setValue($searchText);
        $form->addElement($fieldSearch);

        $fieldSearchInTitle = new CheckboxField('checkbox_search_title');
        $fieldSearchInTitle->setValue($fieldSearchInTitleActive);
        $fieldSearchInTitle->setLabel($this->text('frmjcse','label_checkbox_search_title'));
        $form->addElement($fieldSearchInTitle);

        $fieldSearchInAuthor = new CheckboxField('checkbox_search_author');
        $fieldSearchInAuthor->setValue($fieldSearchInAuthorActive);
        $fieldSearchInAuthor->setLabel($this->text('frmjcse','label_checkbox_search_author'));
        $form->addElement($fieldSearchInAuthor);

        $fieldSearchInKeyword = new CheckboxField('checkbox_search_keyword');
        $fieldSearchInKeyword->setValue($fieldSearchInKeywordActive);
        $fieldSearchInKeyword->setLabel($this->text('frmjcse','label_checkbox_search_keyword'));
        $form->addElement($fieldSearchInKeyword);

        $fieldSearchInAbstract = new CheckboxField('checkbox_search_abstract');
        $fieldSearchInAbstract->setValue($fieldSearchInAbstractActive);
        $fieldSearchInAbstract->setLabel($this->text('frmjcse','label_checkbox_search_abstract'));
        $form->addElement($fieldSearchInAbstract);

        $submit = new Submit('search_submit');
        $submit->setValue($this->text('frmjcse', 'submit_search'));
        $form->addElement($submit);

        $hasResult = true;
        if($searchText!="")
        {
            $items = array();

            $service = FRMJCSE_BOL_Service::getInstance();
            if($fieldSearchInTitleActive){
                $articlesInTitleSearch = $service->searchInTitle($searchText);
                if(count($articlesInTitleSearch)!=0){
                    $hasResultInTitles = true;
                    $this->assign("hasResultInTitles",$hasResultInTitles);
                    foreach ($articlesInTitleSearch as $article)
                    {

                        $authors = $service->getAuthorListByArticleId($article->id);
                        $authorItems = [];
                        foreach ($authors as $author) {
                            $authorItems[] = '<span id="author-'.$author->id.'">'.$author->name.'</span>';
                        }
                        $authorItems = join(" • ", $authorItems);
                        $keywords = $service->getKeywordListByArticleId($article->id);
                        $keywordItems = [];
                        foreach ($keywords as $keyword) {
                            $keywordItems[] = '<span id="keyword-'.$keyword->id.'">'.$keyword->name.'</span>';
                        }
                        $keywordItems = join(" • ", $keywordItems);
                        $item = array(
                            'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                            'id' => $article->id,
                            'title' => $article->title,
                            'abstract' => $article->abstract,
                            'file' => $article->file,
                            'authors' => $authorItems,
                            'keywords'=>$keywordItems,
                            'volume'=>$service->getIssueById($article->issueid)->volume,
                            'no'=>$service->getIssueById($article->issueid)->no
                        );
                        if(!in_array($item,$items)){
                            $items[] = $item;
                        }

                    }
                }
            }
            if($fieldSearchInKeywordActive) {
                $articlesInKeywordsSearch = $service->searchInKeywords($searchText);
                if(count($articlesInKeywordsSearch)!=0){
                    $hasResultInKeywords = true;
                    $this->assign("hasResultInKeywords",$hasResultInKeywords);
                    foreach ($articlesInKeywordsSearch as $article)
                    {
                        $authors = $service->getAuthorListByArticleId($article->id);
                        $authorItems = [];
                        foreach ($authors as $author) {
                            $authorItems[] = '<span id="author-'.$author->id.'">'.$author->name.'</span>';
                        }
                        $authorItems = join(" • ", $authorItems);
                        $keywords = $service->getKeywordListByArticleId($article->id);
                        $keywordItems = [];
                        foreach ($keywords as $keyword) {
                            $keywordItems[] = '<span id="keyword-'.$keyword->id.'">'.$keyword->name.'</span>';
                        }
                        $keywordItems = join(" • ", $keywordItems);
                        $item = array(
                            'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                            'id' => $article->id,
                            'title' => $article->title,
                            'abstract' => $article->abstract,
                            'file' => $article->file,
                            'authors' => $authorItems,
                            'keywords'=> $keywordItems,
                            'volume'=>$service->getIssueById($article->issueid)->volume,
                            'no'=>$service->getIssueById($article->issueid)->no
                        );
                        if(!in_array($item,$items)){
                            $items[] = $item;
                        }
                    }
                }
            }
            if($fieldSearchInAuthorActive) {
                $articlesInAuthorsSearch = $service->searchInAuthors($searchText);
                if(count($articlesInAuthorsSearch)!=0){
                    $hasResultInAuthors = true;
                    $this->assign("hasResultInAuthors",$hasResultInAuthors);
                    foreach ($articlesInAuthorsSearch as $article)
                    {
                        $authors = $service->getAuthorListByArticleId($article->id);
                        $authorItems = [];
                        foreach ($authors as $author) {
                            $authorItems[] = '<span id="author-'.$author->id.'">'.$author->name.'</span>';
                        }
                        $authorItems = join(" • ", $authorItems);
                        $keywords = $service->getKeywordListByArticleId($article->id);
                        $keywordItems = [];
                        foreach ($keywords as $keyword) {
                            $keywordItems[] = '<span id="keyword-'.$keyword->id.'">'.$keyword->name.'</span>';
                        }
                        $keywordItems = join(" • ", $keywordItems);
                        $item = array(
                            'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                            'id' => $article->id,
                            'title' => $article->title,
                            'abstract' => $article->abstract,
                            'file' => $article->file,
                            'authors' => $authorItems,
                            'keywords'=> $keywordItems,
                            'volume'=>$service->getIssueById($article->issueid)->volume,
                            'no'=>$service->getIssueById($article->issueid)->no
                        );
                        if(!in_array($item,$items)){
                            $items[] = $item;
                        }
                    }
                }
            }
            if($fieldSearchInAbstractActive) {
                $articlesInAbstractsSearch = $service->searchInAbstracts($searchText);
                if(count($articlesInAbstractsSearch)!=0){
                    $hasResultInAbstracts = true;
                    $this->assign("hasResultInAbstracts",$hasResultInAbstracts);
                    foreach ($articlesInAbstractsSearch as $article)
                    {
                        $authors = $service->getAuthorListByArticleId($article->id);
                        $authorItems = [];
                        foreach ($authors as $author) {
                            $authorItems[] = '<span id="author-'.$author->id.'">'.$author->name.'</span>';
                        }
                        $authorItems = join(" • ", $authorItems);
                        $keywords = $service->getKeywordListByArticleId($article->id);
                        $keywordItems = [];
                        foreach ($keywords as $keyword) {
                            $keywordItems[] = '<span id="keyword-'.$keyword->id.'">'.$keyword->name.'</span>';
                        }
                        $keywordItems = join(" • ", $keywordItems);
                        $item = array(
                            'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                            'id' => $article->id,
                            'title' => $article->title,
                            'abstract' => $article->abstract,
                            'file' => $article->file,
                            'authors' => $authorItems,
                            'keywords'=> $keywordItems,
                            'volume'=>$service->getIssueById($article->issueid)->volume,
                            'no'=>$service->getIssueById($article->issueid)->no
                        );
                        if(!in_array($item,$items)){
                            $items[] = $item;
                        }
                    }

                }
            }
            if(count($items)!=0){
				function sort1($a,$b)
                {
                    if($a["volume"] > $b["volume"]){
                        return -1;
                    } else if($a["volume"] < $b["volume"]){
                        return 1;
                    }else{
                        return $a["no"] >= $b["no"] ? -1 : 1;
                    }
                }
                usort($items, "sort1");
                $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? $_GET['page'] : 1;
                $perPage = 10;
                $all_count=count($items);
                $first = ($page - 1) * $perPage;
                $count = $perPage;
                $items = array_slice($items,$first, $count);
                $this->assign("searchResult",$items);
                $this->assign('hasResult',$hasResult);
                $paging = new BASE_CMP_Paging($page, ceil($all_count / $perPage), 2);
                $this->addComponent('paging',$paging);
            }else{
                $items = array();
                $this->assign("searchResult",$items);
                $hasResult = false;
                $this->assign('hasResult',$hasResult);
            }

        }else{
            $items = array();
            $this->assign("searchResult",$items);
            $this->assign('hasResult',$hasResult);
        }

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $text = "";
                $text .= $data['search'];
                $text .= ":";
                $text .= $data['checkbox_search_abstract'] ? '1' : '0';
                $text .=  ":";
                $text .= $data['checkbox_search_title'] ? '1' : '0';
                $text .=  ":";
                $text .= $data['checkbox_search_keyword'] ?'1':'0';
                $text .= ":";
                $text .=$data['checkbox_search_author']? '1' : '0';
                $url=OW::getRouter()->urlForRoute('frmjcse.search', ['search_text' => $text ]);
                $this->redirect($url);
            }
        }
    }


}