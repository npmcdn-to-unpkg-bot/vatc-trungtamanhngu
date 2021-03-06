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
use Backend\Models\OrderModel;
use Backend\Models\OrtherPageModel;
use Backend\Models\ProductModel;
use Backend\Models\CollectionModel;
use Backend\Models\UserModel;
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
        $bannerModel = new BannerModel();
        $ortherpageModel = new OrtherPageModel();
        $this->view->isPost = $this->request->getQuery('post');
        $this->view->isHlv = $this->request->getQuery('hlv');
        $this->view->thele = $ortherpageModel::findFirst();
        $this->view->sliders = $bannerModel::find(array("bc_id =2"));
        $this->view->infographic = $bannerModel::findFirst(array("bc_id =3"));
        $this->view->blogs = $newsModel::find(array("n_status=1", "order" => "n_id desc", "limit" => 3));
        $this->view->posts = $postModel::find(array("p_status=1","order" => "p_id desc"));
        $this->view->gallery = $bannerModel::findFirst(array("bc_id =7"));
        $this->view->coachs = $managerHlvModel::find(array("order" => "hlv_id asc", "limit" => 3));
        $this->view->header_title = "Anh Ngữ Việt Mỹ";
    }

    public function index2Action()
    {
        $galleryModel = new GalleryModel();
        $managerHlvModel = new ManagerHlvModel();
        $postModel = new ManagerPostsModel();
        $newsModel = new NewsModel();
        $bannerModel = new BannerModel();
        $ortherpageModel = new OrtherPageModel();
        $this->view->thele = $ortherpageModel::findFirst();
        $this->view->sliders = $bannerModel::find(array("bc_id =2"));
        $this->view->infographic = $bannerModel::findFirst(array("bc_id =3"));
        $this->view->blogs = $newsModel::find(array("n_status=1", "order" => "n_id desc", "limit" => 3));
        $this->view->posts = $postModel::find(array("order" => "p_id desc"));
        $this->view->gallery = $bannerModel::findFirst(array("bc_id =7"));
        $this->view->coachs = $managerHlvModel::find(array("order" => "hlv_id asc", "limit" => 3));
        $this->view->header_title = "Anh Ngữ Việt Mỹ";
    }

    public function searchAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();

            $postModel = new ManagerPostsModel();
            $userModel = new UserModel();
            $query = '';
            if ($data['hlv_id'] != '-1') {
                $query .= "hlv_id = '{$data['hlv_id']}' and ";
            }
            $result = array();
            if (!empty($data['created_at'])) {
                $time = date("Y-m-d", strtotime($data['created_at']));
                $query .= "DATE(created_at) = '{$time}' and ";
            }
            if (!empty($data['us_name'])) {
                $users_id = array();
                $users = $userModel::find(array("us_name LIKE '%{$data['us_name']}%' "));

                if (count($users) > 0) {

                    foreach ($users as $us) {
                        $users_id[] = $us->us_id;
                    }
                    $query .= "us_id IN (" . implode(",", $users_id) . ") and ";
                }

            }
            $query = rtrim($query, "and ");

            $posts = $postModel::find(array($query));
            $this->view->posts = $posts;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    public function searchPostAction()
    {
        if ($this->request->isPost()) {
            $id = $this->request->getPost("id");
            $postModel = new ManagerPostsModel();
            $data = $postModel::findFirst($id);
            $respon['status'] = 0;
            if ($data) {
                $respon['status'] = 1;
                $respon['data'] = $data;
            }
            echo json_encode($respon);
            die;
        }
    }
}
