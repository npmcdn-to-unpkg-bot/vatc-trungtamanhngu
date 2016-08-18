<?php

namespace Backend\Controllers;

use Backend\Models\GalleryModel;

class GalleryController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Gallery'])) {
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

        $this->view->setLayout("map");
        $this->view->header_title = "Gallery Manager";
    }

    public function detailContentAction()
    {
        if ($this->request->isPost()) {
            $imageModel = new \Backend\Models\GalleryModel();
            $data_image = $imageModel::find(array("order" => "ga_id desc"));
            $respon = array(
                'image' => $data_image->toArray()
            );
            echo json_encode($respon);
            die;
        }
        $this->view->data = array();
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
    }

    public function uploadImageAction()
    {
        if ($this->request->hasFiles() == true) {
            $uploads = $this->request->getUploadedFiles();
            $img = array();
            foreach ($uploads as $upload) {
                $folder = $_SERVER['DOCUMENT_ROOT'] . $this->url->getBaseUri() . 'public/uploads/images/upload/';
                if (!is_dir($folder)) {
                    mkdir($folder);
                }
                $path = $folder . "/" . $upload->getname();
                $link = 'public/uploads/images/upload/' . $upload->getname();
                $upload->moveTo($path);
                $img[] = $link;

                //save image
                $buildingModel = new GalleryModel();
                $data_insert = array(
                    "hlv_id" => null,
                    "ga_image_link" => $link
                );
                $buildingModel->save($data_insert);
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
        $buildingModel = new GalleryModel();
//        $buildingModel::find()->delete();
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        die;
        if (isset($data['pi_image_link']) && !empty($data['pi_image_link'])) {

            foreach ($data['pi_image_link'] as $img) {
                $buildingModel = new GalleryModel();
                $data_insert = array(
                    "hlv_id" => null,
                    "ga_image_link" => $img
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
        $buildingModel = new GalleryModel();
        if ($buildingModel::findFirst($input['id'])->delete()) {
            $respon['status'] = 1;
            $respon['message'] = 'Thành Công';
        } else {
            $respon['message'] = 'Không thể xóa ';
        }
        return $respon;
    }

}
