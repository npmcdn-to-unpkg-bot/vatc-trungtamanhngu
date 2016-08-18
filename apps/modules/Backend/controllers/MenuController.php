<?php

namespace Backend\Controllers;

use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

class MenuController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Menu'])) {
            return $this->response->redirect($this->config['rootUrl'] . $this->module_config_backend->main_route . "/");
        }
    }

    public function handleAction() {
        $output = [];
        if (method_exists($this, $this->request->getPost('method')))
            $output = $this->{$this->request->getPost('method')}($this->request->getPost());
        echo json_encode($output);
        die;
    }

    public function indexAction() {
        $dataModel = new \Backend\Models\MenuModel();
        $data = $dataModel::find(array("order" => "mn_parent_id asc"));
        if ($data) {
            $data = $data->toArray();
            $parent_list = array();
            $category_parent = array();
            foreach ($data as $key => $val) {
                $category_parent[$key]['id'] = $val['mn_id'];
                if ($val['mn_parent_id'] != 0 && $val['mn_parent_id'] != null) {
                    $parent_list[$key] = $val;
                    $name_parent = $this->createParent($val['mn_parent_id'], array());
                    ksort($name_parent);
                    $category_parent[$key]['name_parent'] = $parent_list[$key]['name_parent'] = implode(" > ", $name_parent) . " <strong  >> " . $val['mn_name'] . "</strong>";
                } else {
                    $parent_list[$key] = $val;
                    $category_parent[$key]['name_parent'] = $parent_list[$key]['name_parent'] = $val['mn_name'];
                }
            }
            $this->view->category_data = $category_parent;
            $this->view->data = $parent_list;
        }
        $this->view->setLayout("map");
        $this->view->header_title = "Menu Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\MenuModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input) {

        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        $buildingModel = new \Backend\Models\MenuModel();
        
        //Validation
        $validation = $buildingModel->validationRequest($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation
        $data['mn_status'] = isset($data['mn_status']) ? 1 : 0;
        if (empty($data['mn_id']) || $data['mn_id'] == NULL) {
            //insert
            unset($data['mn_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['mn_id']);
            if ($data['mn_id'] == $data['mn_parent_id']) {
                $data['mn_parent_id'] = 0;
            }
            if ($update->update($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Update';
            }
        }
        return $respon;
    }

    public function deleteContent($input) {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $buildingModel = new \Backend\Models\MenuModel();
        $string_delete = substr($this->createParentDelete($input['data'], ''), 0, -1);
        if ($buildingModel::find('mn_id IN (' . $string_delete . ')')->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

    public function createParent($thutuc, $array) {
        $arr = $array;
        $content_obj = new \Backend\Models\MenuModel();
        $list_parent = $content_obj::findFirst("mn_id= '{$thutuc}'");
        $list_parent = $list_parent->toArray();
        if ($list_parent['mn_parent_id'] != 0 && $list_parent['mn_parent_id'] != null) {
            $arr[$thutuc] = $list_parent['mn_name'];
            $arr = $this->createParent($list_parent['mn_parent_id'], $arr);
        } else {
            $arr[$thutuc] = $list_parent['mn_name'];
        }
        return $arr;
    }

    public function createParentDelete($thutuc) {
        $str = $thutuc . ",";
        $content_obj = new \Backend\Models\MenuModel();
        $list_parent = $content_obj::findFirst("mn_parent_id= '{$thutuc}'");
        if ($list_parent) {
            $list_parent = $list_parent->toArray();
            if ($list_parent['mn_parent_id'] != 0 && $list_parent['mn_parent_id'] != null) {
                $str.=$list_parent['mn_id'] . ",";
                $content_obj = new \Backend\Models\MenuModel();
                $list_parent = $content_obj::findFirst("mn_parent_id= '{$list_parent['mn_id']}'");
                if ($list_parent) {
                    $list_parent = $list_parent->toArray();
                    $str .= $this->createParentDelete($list_parent['mn_id']);
                }
            } else {
                $str.=$list_parent['mn_id'] . ",";
            }
        }
        return $str;
    }

}
