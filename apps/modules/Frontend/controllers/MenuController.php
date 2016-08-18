<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryCollectionModel;
use Backend\Models\CategoryModel;
use Backend\Models\ManufacturerModel;
use Backend\Models\ProductModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class MenuController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction($id='')
    {
        $manufactureModel=new ManufacturerModel();
        $this->view->categoryParent=$manufactureModel::findFirst($id);
        $this->view->header_title = "Siêu Giá Sĩ";
        $this->assets->addCss('public/FrontendCore/css/ae-mobile.css', true);
    }


    public function childCategoryAction($id='')
    {
        $categoryModel=new CategoryModel();
        $this->view->category=$categoryModel::findFirst($id);
        $this->view->categoryChild=$categoryModel::find(array("ct_parent_id = '{$id}'"));
        $this->view->header_title = "Siêu Giá Sĩ";
        $this->assets->addCss('public/FrontendCore/css/ae-mobile.css', true);
    }

}
