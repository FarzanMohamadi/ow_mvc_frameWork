<?php
class FRMJCSE_CMP_ArticlesWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        $service = FRMJCSE_BOL_Service::getInstance();
        parent::__construct();
        $articles_most_dl = $service->getArticleListOfMostDownloaded();
        $items = array();
        foreach ($articles_most_dl as $article)
        {
            $items[] = array(
                'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                'title' => $article->title,
                'authors' => $this->getAuthorsText($article->id),
                'keywords' => $service->getKeywordListNameByArticleId($article->id)
            );
        }
        $this->assign("articles_dl",$items);

        $article_most_recent = $service->getArticleListOfLatest();
        $items = array();
        foreach ($article_most_recent as $article)
        {
            $items[] = array(
                'url' => OW::getRouter()->urlForRoute("frmjcse.article",["articleid"=>$article->id]),
                'title' => $article->title,
                'authors' => $this->getAuthorsText($article->id),
                'keywords' => $service->getKeywordListNameByArticleId($article->id)
            );
        }
        $this->assign("articles_latest",$items);
    }
    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
    public function getAuthorsText($id)
    {
        $authors = FRMJCSE_BOL_Service::getInstance()->getAuthorListByArticleId($id);
        $authorItems = [];
        foreach ($authors as $author) {
            $authorItems[] = '<span id="author-'.$author->id.'">'.$author->name.'</span>';
        }
        $authorItems = join(" • ", $authorItems);
        return $authorItems;
    }
    public static function getStandardSettingValueList()
    {
        $language = OW::getLanguage();
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_TITLE => $language->text('frmjcse', 'widget_title')
        );
    }
}