<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMJCSE_BOL_Service
{
    private static $classInstance;
    public static function getInstance()
    {
        if(self::$classInstance === null)
        {
            self::$classInstance=new self();
        }
        return self::$classInstance;
    }
    private function __construct()
    {
    }

    public function addIssue($title, $volume, $no, $posterfile,$file)
    {
        $issue = new FRMJCSE_BOL_Issue();
        $issue->title=$title;
        $issue->volume=$volume;
        $issue->no=$no;
        $issue->file=$file;
        $issue->posterfile=$posterfile;
        $issue->ts=date("Y-m-d H:i:s");
        FRMJCSE_BOL_IssueDao::getInstance()->save($issue);
    }
    public function editIssue($id,$title, $volume, $no, $posterfile,$file)
    {
        $issue = $this->getIssueById($id);
        $issue->title=$title;
        $issue->volume=$volume;
        $issue->no=$no;
        $issue->file=$file;
        $issue->posterfile=$posterfile;
        FRMJCSE_BOL_IssueDao::getInstance()->save($issue);
    }
    public function deleteIssue($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            FRMJCSE_BOL_IssueDao::getInstance()->deleteById($id);
        }
    }
    public function getIssueList()
    {
        $ex = new OW_Example;
        $ex->setOrder('`volume` DESC, `no` DESC');
        return FRMJCSE_BOL_IssueDao::getInstance()->findListByExample($ex);
    }

    /***
     * @param $id
     * @return FRMJCSE_BOL_Issue
     */
    public function getIssueById($id)
    {
        return FRMJCSE_BOL_IssueDao::getInstance()->findById($id);
    }
    public function getLatestIssueId()
    {
        $ex = new OW_Example;
        $ex->setOrder('`volume` DESC, `no` DESC');
        $ex->setLimitClause(0,1);
        return FRMJCSE_BOL_IssueDao::getInstance()->findIdByExample($ex);
    }
    public function addArticle($title, $abstract, $citation, $file, $active, $issueid,
                               $startPage, $endPage)
    {
        $article = new FRMJCSE_BOL_Article();
        $article->title=$title;
        $article->abstract=$abstract;
        $article->citation=$citation;
        $article->file=$file;
        $article->active=$active==null ? 0 : $active;
        $article->issueid=$issueid;
        $article->startPage=$startPage;
        $article->endPage=$endPage;
        $article->ts=date("Y-m-d H:i:s");
        $article->dltimes=0;
        FRMJCSE_BOL_ArticleDao::getInstance()->save($article);
        return $article;
    }
    public function editArticle($id,$title, $abstract, $citation, $file, $active, $issueid,
                                $startPage, $endPage, $dorl)
    {
        $article = $this->getArticleById($id);
        $article->title=$title;
        $article->abstract=$abstract;
        $article->citation=$citation;
        $article->file=$file;
        $article->active=$active;
        $article->issueid=$issueid;
        $article->startPage=$startPage;
        $article->endPage=$endPage;
        $article->setExtra('dorl', $dorl);
        FRMJCSE_BOL_ArticleDao::getInstance()->save($article);
    }

    public function plusDltimesArticle($id){
        if(OW::getSession()->isKeySet('article_'.(string)$id)){
            return;
        }
        OW::getSession()->set('article_'.(string)$id, true);

        $article = $this->getArticleById($id);
        $article->dltimes++;
        FRMJCSE_BOL_ArticleDao::getInstance()->save($article);
    }

    public function incrementViewsArticle($id){
        if(OW::getSession()->isKeySet('view_article_'.(string)$id)){
            return;
        }
        OW::getSession()->set('view_article_'.(string)$id, true);

        $article = $this->getArticleById($id);
        $article->views++;
        FRMJCSE_BOL_ArticleDao::getInstance()->save($article);
    }

    public function deleteArticle($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            FRMJCSE_BOL_ArticleDao::getInstance()->deleteById($id);
        }
    }
    public function getArticleListOfIssue($issueid, $active=null)
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('issueid', $issueid);
        $ex->setOrder('`ts` ASC');
        if(isset($active))
        {
            $ex->andFieldEqual('active', $active);
        }
        return FRMJCSE_BOL_ArticleDao::getInstance()->findListByExample($ex);
    }
    public function getArticleListOfMostDownloaded()
    {
        $ex = new OW_Example();
        $ex->andFieldEqual('active', 1);
        $ex->setOrder('`dltimes` DESC');
        $ex->setLimitClause(0,4);
        return FRMJCSE_BOL_ArticleDao::getInstance()->findListByExample($ex);
    }
    public function getArticleListOfLatest()
    {
        $issueid = $this->getLatestIssueId();
        $ex = new OW_Example();
        $ex->andFieldEqual('active', 1);
        if(isset($issueid)) {
            $ex->andFieldEqual('issueid', $issueid);
        }
        $ex->setOrder('`id` DESC');
        $ex->setLimitClause(0, 4);
        return FRMJCSE_BOL_ArticleDao::getInstance()->findListByExample($ex);
    }
    /***
    * @param $id
    * @return FRMJCSE_BOL_Article
    */
    public function getArticleById($id)
    {
        return FRMJCSE_BOL_ArticleDao::getInstance()->findById($id);
    }

    public function addAuthor($name, $articleid, $email='', $affliation='')
    {
        $name = trim($name);
        if(strlen($name)>1)
        {
            $author = new FRMJCSE_BOL_Author();
            $author->name = $name;
            $author->articleid = $articleid;
            $author->email=$email;
            $author->affliation = $affliation;
            FRMJCSE_BOL_AuthorDao::getInstance()->save($author);
        }
    }

    public function deleteAuthor($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            FRMJCSE_BOL_AuthorDao::getInstance()->deleteById($id);
        }
    }
    public function deleteKeywordsByArticleId($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            foreach ($this->getKeywordList() as $keyword)
            {
                if($keyword->articleid == $id)
                {
                    $this->deleteKeyword($keyword->id);
                }
            }
        }
    }
    public function deleteAuthorsByArticleId($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            foreach ($this->getAuthorList() as $author)
            {
                if($author->articleid == $id)
                {
                    $this->deleteAuthor($author->id);
                }
            }
        }
    }
    public function getAuthorList()
    {
        return FRMJCSE_BOL_AuthorDao::getInstance()->findAll();
    }

    public function getAuthorListNameByArticleId($articleId){
        $authors = array();
        foreach ($this->getAuthorList() as $author)
        {
            if($author->articleid == $articleId)
            {
                $authors[] = $author->name;
            }
        }
        return $authors;
    }
    public function getAuthorListByArticleId($articleId){
        $ex = new OW_Example();
        $ex->andFieldEqual('articleid', $articleId);
        return FRMJCSE_BOL_AuthorDao::getInstance()->findListByExample($ex);
    }
    public function getAuthorById($id)
    {
        return FRMJCSE_BOL_AuthorDao::getInstance()->findById($id);
    }
    public function getKeywordById($id)
    {
        return FRMJCSE_BOL_KeywordDao::getInstance()->findById($id);
    }
    public function addKeyword($name, $articleid)
    {
        $name = trim($name);
        if(strlen($name)>1)
        {
            $keyword = new FRMJCSE_BOL_Keyword();
            $keyword->name=$name;
            $keyword->articleid=$articleid;
            FRMJCSE_BOL_KeywordDao::getInstance()->save($keyword);
        }
    }

    public function deleteKeyword($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            FRMJCSE_BOL_KeywordDao::getInstance()->deleteById($id);
        }
    }
    public function getKeywordList()
    {
        return FRMJCSE_BOL_KeywordDao::getInstance()->findAll();
    }
    public function getKeywordListNameByArticleId($articleId)
    {
        $keywords[]=Null;
        foreach ($this->getKeywordList() as $keyword)
        {
            if($keyword->articleid == $articleId)
            {
                $keywords[] = $keyword->name;
            }
        }
        return $keywords;
    }
    public function getKeywordListByArticleId($articleId){
        $ex = new OW_Example();
        $ex->andFieldEqual('articleid', $articleId);
        return FRMJCSE_BOL_KeywordDao::getInstance()->findListByExample($ex);
    }
    public function searchInTitle($text)
    {
        $text = strtolower($text);
        $articles = FRMJCSE_BOL_ArticleDao::getInstance()->findAll();
        $items = array();
        foreach ($articles as $article) {
            if (strpos(strtolower($article->title), $text) !== false && !in_array($article,$items)) {
                array_push($items, $this->getArticleById($article->id));
            }
        }
        return $items;
    }
    public function searchInKeywords($text)
    {
        $text = strtolower($text);
        $keywords = FRMJCSE_BOL_KeywordDao::getInstance()->findAll();
        $items=array();
        foreach ($keywords as $keyword)
        {
            if(strpos(strtolower($keyword->name),$text) !== false && !in_array($this->getArticleById($keyword->articleid),$items))
            {
                array_push($items,$this->getArticleById($keyword->articleid));
            }
        }
        return $items;
    }
    public function searchInAuthors($text)
    {
        $text = strtolower($text);
        $authors = FRMJCSE_BOL_AuthorDao::getInstance()->findAll();
        $items=array();
        foreach ($authors as $author)
        {
            if(strpos(strtolower($author->name),$text) !== false && !in_array($this->getArticleById($author->articleid),$items))
            {
                array_push($items,$this->getArticleById($author->articleid));
            }
        }
        return $items;
    }
    public function searchInAbstracts($text)
    {
        $text = strtolower($text);
        $articles = FRMJCSE_BOL_ArticleDao::getInstance()->findAll();
        $items=array();
        foreach ($articles as $article)
        {
            if(strpos(strtolower($article->abstract),$text) !== false && !in_array($article,$items))
            {
                array_push($items,$this->getArticleById($article->id));
            }
        }
        return $items;
    }
    public function onCollectAuthLabels( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $event->add(
            array(
                'frmjcse' => array(
                    'label' => $language->text('frmjcse', 'auth_group_label'),
                    'actions' => array(
                        'edit' => $language->text('frmjcse', 'auth_action_label_edit')
                    )
                )
            )
        );
    }
    public function addCitationFormat($title,$format)
    {
        $citeFormat = new FRMJCSE_BOL_CitationFormat();
        $citeFormat->title=$title;
        $citeFormat->format=$format;
        FRMJCSE_BOL_CitationFormatDao::getInstance()->save($citeFormat);
    }
    public function getCitationList()
    {
        return FRMJCSE_BOL_CitationFormatDao::getInstance()->findAll();
    }
    public function deleteCitation($id)
    {
        $id = (int) $id;
        if ( $id > 0 )
        {
            FRMJCSE_BOL_CitationFormatDao::getInstance()->deleteById($id);
        }
    }
    public function getCitationFormats($articleid)
    {
        $citationFormatsList = $this->getCitationList();
        $createdFormats = array();
        foreach ($citationFormatsList as $citationFormat)
        {
            $formattedText = $citationFormat->format;
            if(strpos($formattedText,"{doNotInclude}")!== false){
                continue;
            }
            $authorsNameList = $this->getAuthorListNameByArticleId($articleid);
            $authorsText="";
            if(strpos($formattedText,"{authors-en}")!== false){
                $authorsCount=count($authorsNameList);
                switch ($authorsCount){
                    case 0:
                        $authorsText="No Author";
                    break;
                    case 1:
                        $formattedName = $this->enFormatSingleName($authorsNameList[0]);
                        $authorsText .= $formattedName;
                    break;
                    case 2:
                        $formattedName = $this->enFormatSingleName($authorsNameList[0]);
                        $authorsText .= $formattedName . " and ";
                        $formattedName = $this->enFormatSingleName($authorsNameList[1]);
                        $authorsText .= $formattedName;
                    break;
                    default:
                        $formattedName = $this->enFormatSingleName($authorsNameList[0]);
                        $authorsText .= $formattedName . ", ";
                        for($i=1;$i<$authorsCount-1;$i++){
                            $formattedName = $this->enFormatSingleName($authorsNameList[$i]);
                            $authorsText .= $formattedName . ", ";
                        }
                        $authorsText .= " and " . $this->enFormatSingleName($authorsNameList[$authorsCount-1]);
                }
                $formattedText = str_replace("{authors-en}",$authorsText,$formattedText);
            }else if(strpos($formattedText,"{authors-fa}")!== false){
                $authorsCount=count($authorsNameList);
                switch ($authorsCount){
                    case 0:
                        $authorsText="بدون نویسنده";
                        break;
                    case 1:
                        $formattedName = $this->faFormatSingleName($authorsNameList[0]);
                        $authorsText .= $formattedName;
                        break;
                    case 2:
                        $formattedName = $this->faFormatSingleName($authorsNameList[0]);
                        $authorsText .= $formattedName . " و ";
                        $formattedName = $this->faFormatSingleName($authorsNameList[1]);
                        $authorsText .= $formattedName;
                        break;
                    default:
                        $formattedName = $this->faFormatSingleName($authorsNameList[0]);
                        $authorsText = $formattedName . "، " . $authorsText;
                        for($i=1;$i<$authorsCount-1;$i++){
                            $formattedName = $this->faFormatSingleName($authorsNameList[$i]);
                            $authorsText = $formattedName . "، " . $authorsText;
                        }
                        $authorsText =  $authorsText . " و "  . $this->faFormatSingleName($authorsNameList[$authorsCount-1]);
                }
                $formattedText = str_replace("{authors-fa}",$authorsText,$formattedText);
            }else if(strpos($formattedText,"{authors-en2}")!== false){
                $authorsCount=count($authorsNameList);
                switch ($authorsCount){
                    case 0:
                        $authorsText="No Author";
                        break;
                    case 1:
                        $formattedName = $this->enFormatSingleNameReverse($authorsNameList[0]);
                        $authorsText = $formattedName;
                        break;
                    case 2:
                        $formattedName = $this->enFormatSingleNameReverse($authorsNameList[0]);
                        $authorsText = $formattedName . ", & ";
                        $formattedName = $this->enFormatSingleNameReverse($authorsNameList[1]);
                        $authorsText .= $formattedName;
                        break;
                    default:
                        $formattedName = $this->enFormatSingleNameReverse($authorsNameList[0]);
                        $authorsText = $formattedName . ", ";
                        for($i=1;$i<$authorsCount-1;$i++){
                            $formattedName = $this->enFormatSingleNameReverse($authorsNameList[$i]);
                            $authorsText .= $formattedName . ", ";
                        }
                        $authorsText .= " & " . $this->enFormatSingleNameReverse($authorsNameList[$authorsCount-1]);
                }
                $formattedText = str_replace("{authors-en2}",$authorsText,$formattedText);
            }else if(strpos($formattedText,"{authors-en3}")!== false){
                $authorsCount=count($authorsNameList);
                switch ($authorsCount){
                    case 0:
                        $authorsText="No Author";
                        break;
                    case 1:
                        $formattedName = $this->enFormatSingleNameReverseNoSpace($authorsNameList[0]);
                        $authorsText = $formattedName;
                        break;
                    case 2:
                        $formattedName = $this->enFormatSingleNameReverseNoSpace($authorsNameList[0]);
                        $authorsText = $formattedName . ", and ";
                        $formattedName = $this->enFormatSingleNameReverseNoSpace($authorsNameList[1]);
                        $authorsText .= $formattedName;
                        break;
                    default:
                        $formattedName = $this->enFormatSingleNameReverseNoSpace($authorsNameList[0]);
                        $authorsText = $formattedName . ", ";
                        for($i=1;$i<$authorsCount-1;$i++){
                            $formattedName = $this->enFormatSingleNameReverseNoSpace($authorsNameList[$i]);
                            $authorsText .= $formattedName . ", ";
                        }
                        $authorsText .= " and " . $this->enFormatSingleNameReverseNoSpace($authorsNameList[$authorsCount-1]);
                }
                $formattedText = str_replace("{authors-en3}",$authorsText,$formattedText);
            }
            // else if other authors format
            //other title formats should be checked here
            $formattedText = str_replace("{title}",$this->getArticleById($articleid)->title,$formattedText);
            $formattedText = str_replace("{vol}",$this->getIssueById($this->getArticleById($articleid)->issueid)->volume,$formattedText);
            $formattedText = str_replace("{no}",$this->getIssueById($this->getArticleById($articleid)->issueid)->no,$formattedText);
            preg_match_all('/\b\d{4}\b/', $this->getIssueById(($this->getArticleById($articleid)->issueid))->title, $matches);
            $formattedText = str_replace("{year}",$matches[0][0],$formattedText);
            $createdFormats[$citationFormat->title] = $formattedText;
        }
        return $createdFormats;
    }
    public function enFormatSingleName($authorName)
    {
        $formattedName="";
        $namePieces = explode(" ",$authorName);
        for($i=0;$i<count($namePieces)-1;$i++){
            $formattedName .= substr($namePieces[$i],0,1) . '. ';
        }
        $formattedName .= $namePieces[count($namePieces)-1];
        return $formattedName;
    }
    public function enFormatSingleNameReverse($authorName)
    {
        $formattedName="";
        $namePieces = explode(" ",$authorName);
        for($i=0;$i<count($namePieces)-1;$i++){
            $formattedName .= substr($namePieces[$i],0,1) . '. ';
        }
        $formattedName = $namePieces[count($namePieces)-1] . ', ' .$formattedName;
        return trim($formattedName);
    }
    public function enFormatSingleNameReverseNoDot($authorName)
    {
        // Curtiss, Larry A
        // Weinhold, Frank
        $formattedName="";
        $namePieces = explode(" ",$authorName);
        for($i=0;$i<count($namePieces)-1;$i++){
            $i == 0 ? $formattedName .= $namePieces[0] . ' ' : $formattedName .= substr($namePieces[$i],0,1) . ' ';
        }
        $formattedName = $namePieces[count($namePieces)-1] . ', ' .$formattedName;
        return trim($formattedName);
    }
    public function enFormatSingleNameReverseNoSpace($authorName)
    {
        $formattedName="";
        $namePieces = explode(" ",$authorName);
        for($i=0;$i<count($namePieces)-1;$i++){
            $formattedName .= substr($namePieces[$i],0,1) . '.';
        }
        $formattedName = $namePieces[count($namePieces)-1] . ', ' .$formattedName;
        return $formattedName;
    }

    public function faFormatSingleName($authorName)
    {
        $namePieces = explode(" ",$authorName);
        $formattedName = mb_substr($namePieces[0],0,1)  . ". " . $namePieces[count($namePieces)-1] ;
        return $formattedName;
    }
    public function getBibtexCite($articleid){
        $formattedText ="@article{{firsAuthorLastName}{year1}{firstTitleWord},\n  title={{fullTitle}},\n  author={{fullAuthors}},\n  journal={The CSI Journal on Computer Science and Engineering},\n  volume={{vol}},\n  number={{no}},\n  year={{year2}},\n  publisher={Computer Society of Iran}\n}";
        $authorsNameList = $this->getAuthorListNameByArticleId($articleid);
        $firstAuthorNameArray = explode(" ",$authorsNameList[0]);
        $formattedText = str_replace("{firsAuthorLastName}",strtolower($firstAuthorNameArray[count($firstAuthorNameArray)-1]),$formattedText);

        preg_match_all('/\b\d{4}\b/', $this->getIssueById(($this->getArticleById($articleid)->issueid))->title, $matches);
        $formattedText = str_replace("{year1}",$matches[0][0],$formattedText);
        $formattedText = str_replace("{year2}",$matches[0][0],$formattedText);


        $title = $this->getArticleById($articleid)->title;
        $titleArray = explode(" ",$title);
        $notInThese = array("a", "an", "the", "on", "in","how","what","why","which", "where");
        for ($i=0;$i<count($titleArray);$i++){
            if (in_array(strtolower($titleArray[$i]),$notInThese)){
                continue;
            }else{
                if(strpos($titleArray[$i],"-")!== false){
                    $length = strpos($titleArray[$i],"-");
                    $titleArray[$i] = substr($titleArray[$i],0,$length);
                }else if(strpos($titleArray[$i],":")!== false){
                    $titleArray[$i] = substr($titleArray[$i],0,-1);
                }
                $formattedText = str_replace("{firstTitleWord}",strtolower($titleArray[$i]),$formattedText);
                break;
            }
        }

        $formattedText = str_replace("{fullTitle}",$title,$formattedText);

        $authorsNameList = $this->getAuthorListNameByArticleId($articleid);
        $authorsText="";
        $authorsCount=count($authorsNameList);
        if($authorsCount > 0){
            for($i=0;$i<$authorsCount-1;$i++){
                $authorsText .= $this->enFormatSingleNameReverseNoDot($authorsNameList[$i]) . " and ";
            }
            $authorsText .= $this->enFormatSingleNameReverseNoDot($authorsNameList[$authorsCount - 1]);
        }else{
            $authorsText = "No Author";
        }
        $formattedText = str_replace("{fullAuthors}",$authorsText,$formattedText);

        $formattedText = str_replace("{vol}",$this->getIssueById($this->getArticleById($articleid)->issueid)->volume,$formattedText);

        $formattedText = str_replace("{no}",$this->getIssueById($this->getArticleById($articleid)->issueid)->no,$formattedText);

        return $formattedText;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $article
     * @param $issue
     * @return false|string|string[]
     */
    private function getArticleXMLContent($article, $issue){
        $xml = file_get_contents(OW::getPluginManager()->getPlugin('frmjcse')->getStaticDir() . 'xml_article_template.xml');

        $xml = str_replace('%PUBLISHER%', OW::getLanguage()->text('frmjcse', 'publisher'), $xml);
        $xml = str_replace('%JOURNALTITLE%', OW::getLanguage()->text('frmjcse', 'journal_title'), $xml);
        $xml = str_replace('%VOL%', $issue->volume, $xml);
        $xml = str_replace('%ISSUE%', $issue->no, $xml);

        $ts = (strtotime($article->ts));
        // year is a 4 digit number inside title
        // $xml = str_replace('%Y%', date('Y', $ts), $xml);
        $xml = str_replace('%M%', date('m', $ts), $xml);
        $xml = str_replace('%D%', date('d', $ts), $xml);

        $title = UTIL_HtmlTag::stripTagsAndJs($article->title);
        $title = preg_replace('/&(\w+);/i', '‌', $title);
        $abstract = UTIL_HtmlTag::stripTagsAndJs($article->abstract);
        $abstract = preg_replace('/&(\w+);/i', '‌', $abstract);
        $citation = str_replace("\n", "##", $article->citation);
        $citation = str_replace(array('&', '<', '>', '\'', '\"'), array('', '', '', '', ''), $citation);

        $issueTitle = UTIL_HtmlTag::stripTagsAndJs($issue->title);
        $issueTitle = preg_replace('/&(\w+);/i', '‌', $issueTitle);
        preg_match_all('/\d{4}/', $issueTitle, $matches);
        $year = isset($matches[0][0]) ? $matches[0][0] : "";
        $xml = str_replace('%Y%', $year, $xml);

        $xml = str_replace('%TITLE%', $title, $xml);
        $xml = str_replace('%ID%', $article->id, $xml);

        $startPage = (!isset($article->startPage)) ? 1 : $article->startPage;
        $xml = str_replace('%FPAGE%', $startPage, $xml);
        $endPage = (!isset($article->endPage)) ? 10 : $article->endPage;
        $xml = str_replace('%TPAGE%', $endPage, $xml);
        $pageNo = $endPage + 1 - $startPage;
        $xml = str_replace('%PAGENO%', $pageNo, $xml);

        $xml = str_replace('%ABSTRACT%', $abstract, $xml);
        $xml = str_replace('%VIEW_URL%', OW::getRouter()->urlForRoute("frmjcse.article",
            ["articleid" => $article->id]), $xml);
        $xml = str_replace('%DL_URL%', $this->getArticleById($article->id)->file, $xml);
        $xml = str_replace('%REFERENCES%', $citation, $xml);

        $authors = $this->getAuthorListByArticleId($article->id);
        $authorItems = '';
        foreach ($authors as $author) {
            $parts = explode(' ', $author->name);
            $authorItems .=
                '
                    <author>Farzan</author>
                        <name>plugin</name>
                        <MidName></MidName>
                        <Family>' . implode(' ', array_slice($parts, 1)) . '</Family>
                        <NameE></NameE>
                        <MidNameE></MidNameE>
                        <FamilyE></FamilyE>
                        <Organizations>
                            <Organization>' . $author->affliation . '</Organization>
                        </Organizations>
                        <Countries>
                            <Country>ایران</Country>
                        </Countries>
                        <EMAILS>
                            <Email>'. $author->email .'</Email>
                        </EMAILS>
                    </AUTHOR>';
        }
        $xml = str_replace('%AUTHORS%', $authorItems, $xml);

        $keywords = $this->getKeywordListByArticleId($article->id);
        $keywordItems = '';
        foreach ($keywords as $keyword) {
            $keywordItems .= '
                    <KEYWORD>
                        <KeyText>' . $keyword->name . '</KeyText>
                    </KEYWORD>';
        }
        return str_replace('%KEYWORDS%', $keywordItems, $xml);
    }

    /***
     * @param $articleIds
     * @return false|string|string[]
     * @throws Redirect404Exception
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     */
    public function getXMLContent($articleIds){
        $articles = FRMJCSE_BOL_ArticleDao::getInstance()->findByIdList($articleIds);
        if (empty($articles)) {
            throw new Redirect404Exception();
        }
        $article = $articles[0];
        $issue = $this->getIssueById($article->issueid);

        // create xml file contents
        $xml = file_get_contents(OW::getPluginManager()->getPlugin('frmjcse')->getStaticDir() . 'xml_template.xml');

        $xml = str_replace('%VOL%', $issue->volume, $xml);
        $xml = str_replace('%ISSUE%', $issue->no, $xml);
        $xml = str_replace('%PAGENO%', count($articles)*11 + ($article->issueid % 10), $xml);

        $issueTitle = UTIL_HtmlTag::stripTagsAndJs($issue->title);
        $issueTitle = preg_replace('/&(\w+);/i', '‌', $issueTitle);
        preg_match_all('/\d{4}/', $issueTitle, $matches);
        $year = isset($matches[0][0]) ? $matches[0][0] : "";
        $xml = str_replace('%Y%', $year, $xml);

        $xmlAllArticles = '';
        foreach($articles as $article) {
            $xmlAllArticles .= $this->getArticleXMLContent($article, $issue);
        }
        $xml = str_replace('%ARTICLES%', $xmlAllArticles, $xml);

        return $xml;
    }
}