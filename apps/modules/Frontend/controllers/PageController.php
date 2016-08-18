<?php

namespace Frontend\Controllers;

use Backend\Models\OrtherPageModel;

class PageController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        
    }

    public function indexAction($seo_link) {
        $newsModel = new \Backend\Models\OrtherPageModel();
        $news = $newsModel::findFirst(array("p_status=1 and p_seo_link='{$seo_link}'"));
        if (!$news) {
            return $this->response->redirect();
        }
        $this->view->news = $news;
        $this->view->setLayout("index");
        $this->view->header_title = $news->p_name;
    }


}
