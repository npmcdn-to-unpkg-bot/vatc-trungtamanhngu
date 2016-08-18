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

class IndexController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $galleryModel = new GalleryModel();
        $managerHlvModel = new ManagerHlvModel();
        $postModel = new ManagerPostsModel();
        $newsModel = new NewsModel();
        $this->view->blogs = $newsModel::find(array("n_status=1", "order" => "n_id desc", "limit"=> 3));
        $this->view->posts = $postModel::find(array("order" => "p_id desc"));
        $this->view->gallery = $galleryModel::findFirst(array("order" => "RAND()"));
        $this->view->coachs = $managerHlvModel::find(array("order" => "hlv_id asc", "limit" => 3));
        $this->view->header_title = "Anh Ngữ Việt Mỹ";
    }

    public function searchAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $postModel = new ManagerPostsModel();
            $posts = $postModel::find();
            $result = array();
            if (!empty($data['created_at'])) {
                $time = date("Y-m-d", strtotime($data['created_at']));
                $posts = $postModel::find(array("DATE(created_at) = '{$time}'"));
            }
            if (count($posts) > 0) {
                foreach ($posts as $po) {
                    if ($po->ManagerHlvModel->hlv_gender == $data['hlv_gender']) {
                        $result[]=$po;
                    }
                }
            }
            $this->view->posts=$result;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }
}