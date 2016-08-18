<?php

namespace Backend\Controllers;

class ProductGalleryController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Product'])) {
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
        $productModel = new \Backend\Models\ProductModel();
        $this->view->product = $productModel::find(array("order" => "pr_id desc"));
        $this->view->setLayout("map");
        $this->view->header_title = "Product Gallery Manager";
    }

    public function detailContentAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $imageModel = new \Backend\Models\ProductImageModel();
            $data_image = $imageModel::find(array("plo_id = '{$data['color_id']}' and pr_id='{$data['product_id']}'"));
            $respon = array(
                'image' => $data_image->toArray()
            );
            echo json_encode($respon);
            die;
        }
        $id = $this->request->getQuery("id");
        $dataModel = new \Backend\Models\ProductModel();
        $optionModel = new \Backend\Models\ProductOptionModel();
        $this->view->data = $dataModel::findFirst($id);
        $this->view->option = $optionModel::findFirst(1);
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
    }

    public function uploadImageAction()
    {
        $pr_id = $this->request->getQuery("pr_id");
        $plo_id = $this->request->getQuery("plo_id");
//        if ($plo_id == '-1') {
//            $respon = array("status" => 0, "message" => "Vui Lòng Chọn Color");
//            echo json_encode($respon);
//            die;
//        }
        if ($this->request->hasFiles() == true) {
            $uploads = $this->request->getUploadedFiles();
            $img = array();
            foreach ($uploads as $upload) {
                $folder = $_SERVER['DOCUMENT_ROOT'] . $this->url->getBaseUri() . 'public/uploads/images/upload/' . $pr_id;
                if (!is_dir($folder)) {
                    mkdir($folder);
                }
                $path = $folder . "/" . $upload->getname();
                $link = 'public/uploads/images/upload/' . $folder . "/" . $upload->getname();
                $upload->moveTo($path);
                $img[] = $link;
            }
            $respon = array("status" => 1, "message" => "sucess", "img" => $img);
            echo json_encode($respon);
            die;
        }
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);
        $data['plo_id'] = -1;
//        if (!isset($data['plo_id']) || $data['plo_id'] == '-1') {
//            $respon['message'] = 'Vui lòng chọn Color. ';
//            return $respon;
//        }
//        $data['product_status'] = isset($data['product_status']) ? 1 : 0;
//        $optionDetailModel = new \Backend\Models\ProductOptionDetailModel();
//        $optionDetail = $optionDetailModel::findFirst(array("pr_id = '{$data['pr_id']}' and plo_id='{$data['plo_id']}'"));
//        $optionDetail->product_status = $data['product_status'];
//        $optionDetail->save();
        $buildingModel = new \Backend\Models\ProductImageModel();
        $buildingModel::find(array("pr_id = '{$data['pr_id']}' and plo_id='{$data['plo_id']}'"))->delete();
        if (isset($data['pi_image_link']) && !empty($data['pi_image_link'])) {

            foreach ($data['pi_image_link'] as $img) {
                $buildingModel = new \Backend\Models\ProductImageModel();
                $data_insert = array(
                    "pr_id" => $data['pr_id'],
                    "plo_id" => $data['plo_id'],
                    "pi_image_link" => $img
                );
                $buildingModel->save($data_insert);
            }
        }
        $respon['status'] = 1;
        $respon['message'] = 'Thành Công';
        return $respon;
    }

    public function deleteContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $buildingModel = new \Backend\Models\ProductOptionModel();
        $bannerModel = new \Backend\Models\ProductListOptionModel();
        if ($buildingModel::findFirst($input['data'])->delete()) {
            $bannerModel::find(array("plo_id='{$input['data']}'"))->delete();
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
