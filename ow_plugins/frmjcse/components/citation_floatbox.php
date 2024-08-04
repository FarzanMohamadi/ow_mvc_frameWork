<?php
class FRMJCSE_CMP_CitationFloatbox extends OW_Component
{
    public function __construct( $articleId)
    {
        parent::__construct();
        $service = FRMJCSE_BOL_Service::getInstance();
        $citations = $service->getCitationFormats($articleId);
        $this->assign("citations",$citations);
        $citationFormatList = $service->getCitationList();
        foreach ($citationFormatList as $citationFormat){
            if(strtolower($citationFormat->title) == "bibtex"){
                $this->assign("bibtex",$citationFormat->title);
                $bibtex_citation = $service->getBibtexCite($articleId);
                $this->assign("bibtex_citation",$bibtex_citation);
                break;
            }
        }
    }

}