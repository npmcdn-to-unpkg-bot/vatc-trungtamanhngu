<?php

namespace Backend\Controllers;

use Backend\Models\CategoryCollectionModel;
use Backend\Models\CollectionModel;
use Backend\Models\ProductListOptionModel;
use Backend\Models\ProductModel;
use Backend\Models\ProductOptionModel;

class CollectionController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Category Collection'])) {
            return $this->response->redirect($this->config['rootUrl'] . $this->module_config_backend->main_route . "/");
        }
    }

    public function handleAction()
    {
        $output = [];
        if (method_exists($this, $this->request->getPost('method')))
            $output = $this->{$this->request->getPost('method')}($this->request->getPost());
        echo json_encode($output);
        die;
    }

    public function indexAction()
    {
        $dataModel = new CollectionModel();
        $dataCategoryModel = new CategoryCollectionModel();
        $productModel = new ProductModel();
        if ($this->request->isPost()) {
            $bc_id = $this->request->getPost('col_id');
            $this->view->data_search = $bc_id;
            $this->view->data = $dataModel::find(array("col_id = '{$bc_id}'"));
        } else {
            $this->view->data = $dataModel::find();
        }

        //get product
        $data = $dataModel::find();
        if (count($data)>0) {
            $temp_product = '';
            foreach ($dataModel::find() as $da) {
                $temp_product .= $da->pr_id . ",";
            }
            $temp_product = rtrim($temp_product, ",");
            $this->view->products = $productModel::find(array(
                "pr_status=1 and pr_id Not IN ({$temp_product})"
            ));
        }else{
            $this->view->products = $productModel::find(array(
                "pr_status=1 "
            ));
        }

        //category collection
        $category=$dataCategoryModel::find();
        if ($category) {
            $parent_list = array();
            $category_parent = array();

            foreach ($category as $key => $val) {

                $category_parent[$key]=(object) array('id'=>$val->col_id);

                if ($val->col_parent_id != 0 && $val->col_parent_id != null) {
                    $parent_list[$key] = $val;
                    $name_parent = $this->createParent($val->col_parent_id, array());
                    ksort($name_parent);
                    $category_parent[$key]->name_parent = $parent_list[$key]->name_parent = implode(" > ", $name_parent) . " <strong  >> " . $val->col_name . "</strong>";
                } else {
                    $parent_list[$key] = $val;

                    $category_parent[$key]->name_parent = $parent_list[$key]->name_parent = $val->col_name;
                }
            }
            $this->view->category_data = $category_parent;
        }
        $this->view->setLayout("map");
        $this->view->header_title = "Collection Manager";
    }

    public function choseProductAction()
    {
        if ($this->request->isPost()) {
            $id = $this->request->getPost("id");
            $productModel = new ProductModel();
            $optionProductModel = new ProductOptionModel();
            $avatarModel = new \Backend\Models\ProductAvatarModel();
            $this->view->category_option = $optionProductModel::find();
            $this->view->data = $productModel::findFirst($id);
            $this->view->image = $avatarModel::findFirst(array("pr_id = '{$id}' and pa_type=1"));
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    public function detailContent($input)
    {
        $dataModel = new CollectionModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;

        $data = json_decode($input['data'], true);
        $buildingModel = new CollectionModel();
        $buildingModel->create($data);
        $validation = $buildingModel->getMessages();
        //Validation
        if (empty($validation) || is_null($validation)) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';

        } else {
            $text = '';
            foreach ($validation as $message) {
                $text .= $message . "\n";
            }
            $respon['message'] = $text;
        }
        return $respon;
    }

    public function deleteContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $buildingModel = new CollectionModel();
        $data = $buildingModel::findFirst($input['data']);
        if ($data->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }
    public function createParent($thutuc, $array)
    {
        $arr = $array;
        $content_obj = new CategoryCollectionModel();
        $list_parent = $content_obj::findFirst("col_id= '{$thutuc}'");
        if ($list_parent) {
            if ($list_parent->col_parent_id != 0 && $list_parent->col_parent_id != null) {
                $arr[$thutuc] = $list_parent->col_name;
                $arr = $this->createParent($list_parent->col_parent_id, $arr);
            } else {
                $arr[$thutuc] = $list_parent->col_name;
            }
        }
        return $arr;
    }

}
