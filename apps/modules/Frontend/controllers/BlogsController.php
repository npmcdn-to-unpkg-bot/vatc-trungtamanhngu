<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryCollectionModel;
use Backend\Models\CategoryModel;
use Backend\Models\GalleryModel;
use Backend\Models\ManagerHlvModel;
use Backend\Models\ManagerPostsModel;
use Backend\Models\ManufacturerModel;
use Backend\Models\NewsModel;
use Backend\Models\ProductModel;
use Backend\Models\CollectionModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Mvc\Model\Query;

class BlogsController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $newsModel = new NewsModel();
        $bannerModel = new BannerModel();
        $this->view->sliders = $bannerModel::findFirst();
        $this->view->data = $newsModel::find(array("n_status=1", "order" => "n_id desc", "limit" => 6));
        $this->view->header_title = "Anh Ngữ Việt Mỹ";
    }

    public function loadmoreAction()
    {
        $data = false;
        if ($this->request->isPost()) {
            $number = $this->request->getPost('page');
            $newsModel = new NewsModel();
            $data = $newsModel::find(array("n_status=1", "limit" => 6, "offset" => $number * 6, "order" => "n_id desc"));
        }
        $this->view->data = $data;
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
    }
    public function detailAction($seo){
        if(empty($seo)){
            $this->response->redirect("blogs");
        }
        $newsModel = new NewsModel();
        $bannerModel = new BannerModel();
        $this->view->sliders = $bannerModel::findFirst();
        $this->view->relatedPosts=$newsModel::find(array("n_status=1", "order" => "n_id desc", "limit" => 6));
        $this->view->data=$newsModel::findFirst(array("n_seo_link = '{$seo}'"));
        $this->view->header_title = "Anh Ngữ Việt Mỹ";
    }

}
