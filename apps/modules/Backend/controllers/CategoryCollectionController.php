<?php

namespace Backend\Controllers;

use Backend\Models\CategoryCollectionModel;

class CategoryCollectionController extends ControllerBase
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
        $dataModel = new CategoryCollectionModel();
        $data = $dataModel::find();
        if ($data) {
            $parent_list = array();
            $category_parent = array();

            foreach ($data as $key => $val) {

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
            $this->view->data = $parent_list;
        }
        $this->view->setLayout("map");
        $this->view->header_title = "Category Collection Manager";
    }

    public function detailContent($input)
    {
        $dataModel = new CategoryCollectionModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;

        $data = json_decode($input['data'], true);
        $data['col_status'] = isset($data['col_status']) ? 1 : 0;
        $data['col_description'] = $data['description'];
        unset($data['description']);

        $buildingModel = new CategoryCollectionModel();
        if (empty($data['col_id']) || $data['col_id'] == NULL) {
            //insert
            unset($data['col_id']);
            $buildingModel->create($data);
            $validation = $buildingModel->getMessages();
        } else {
            //update
            $update = $buildingModel::find($data['col_id']);
            $update->update($data);
            $validation = $update->getMessages();
        }
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
        $buildingModel = new CategoryCollectionModel();
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
