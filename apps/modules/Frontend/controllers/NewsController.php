<?php

namespace Frontend\Controllers;

use Backend\Models\NewsCategoryModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class NewsController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        
    }

    public function indexAction() {
        $newsModel = new \Backend\Models\NewsModel();
        $this->view->news = $newsModel::find(array("n_status=1", "order" => "created_at desc", "limit" => 13));
        $this->view->setLayout("index");
        $this->view->header_title = "News";
    }

    public function detailAction($seo_link) {
        $newsModel = new \Backend\Models\NewsModel();
        $news = $newsModel::findFirst(array("n_status=1 and n_seo_link='{$seo_link}'"));
        if (!$news) {
            return $this->response->redirect("news");
        }
        $this->view->news = $news;
        $this->view->setLayout("index");
        $this->view->categoryNewsID = $news->nc_id;
        $this->view->header_title = $news->n_name;
    }

    public function listAction($ct_news_id=NULL) {
        $newsModel = new \Backend\Models\NewsModel();
        $newsCategoryModel=new NewsCategoryModel();
        $currentPage = $this->request->getQuery('page', 'int');
        if (isset($ct_news_id)) {
            $listNews = $newsModel::find(array("n_status=1 and nc_id='{$ct_news_id}' ", "order" => "created_at desc"));
        } else {
            $listNews = $newsModel::find(array("n_status=1 ", "order" => "created_at desc"));
        }
        $paginator = new PaginatorModel(
                array(
            "data" => $listNews,
            "limit" => 10,
            "page" => $currentPage
                )
        );
        $page = $paginator->getPaginate();
        if (isset($ct_news_id)) {
            $this->view->textList = $newsCategoryModel::findFirst($ct_news_id)->nc_name;
        }
        $this->view->listNews = $page;
        $this->view->categoryNewsID = $ct_news_id;
        $this->view->setLayout("index");
        $this->view->header_title = "News";
    }

}
