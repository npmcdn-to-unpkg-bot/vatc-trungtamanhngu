<?php

namespace Backend\Controllers;

class ManufacturerController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Manufacturer'])) {
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
        $dataModel = new \Backend\Models\ManufacturerModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find();
        $this->view->header_title = "Manufacturer Manager";
    }

    public function detailContent($input) {
        $dataModel = new \Backend\Models\ManufacturerModel();
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
        $buildingModel = new \Backend\Models\ManufacturerModel();
        $check_title = $buildingModel::findFirst(array("ma_name='{$data['ma_name']}' and ma_id!='{$data['ma_id']}'"));
        if ($check_title) {
            $respon['message'] = 'Title đã tồn tại';
            return $respon;
        }
        if (empty($data['ma_id']) || $data['ma_id'] == NULL) {
            //insert
            unset($data['ma_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['ma_id']);
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
        $buildingModel = new \Backend\Models\ManufacturerModel();

        $data = $buildingModel::findFirst($input['data']);
        if (count($data->CategoryModel) > 0) {
            foreach ($data->CategoryModel as $cate) {
                $categoryModel = new \Backend\Models\CategoryModel();
                $category = $categoryModel::findFirst($cate->ct_id);
                if (count($categoryModel) > 0 && count($category->ProductModel) > 0) {
                    foreach ($category->ProductModel as $pro) {
                        $productModel = new \Backend\Models\ProductModel();
                        $product = $productModel::findFirst($pro->pr_id);
                        if (count($product) > 0) {
                            if (count($product->ProductAvatarModel) > 0) {
                                $product->ProductAvatarModel->delete();
                            }
                            if (count($product->ProductOptionDetailModel) > 0) {
                                $product->ProductOptionDetailModel->delete();
                            }
                            if (count($product->ProductAttributeModel) > 0) {
                                $product->ProductAttributeModel->delete();
                            }
                            if (count($product->ProductImageModel) > 0) {
                                $product->ProductImageModel->delete();
                            }
                        }
                    }
                    $category->ProductModel->delete();
                }
            }

            $data->CategoryModel->delete();
        }
        if ($data->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa Animation';
        }
        return $respon;
    }

}
