<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryCollectionModel;
use Backend\Models\CategoryModel;
use Backend\Models\GalleryModel;
use Backend\Models\LogCodeModel;
use Backend\Models\ManagerPostsModel;
use Backend\Models\ManufacturerModel;
use Backend\Models\ProductModel;
use Backend\Models\CollectionModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;
use Phalcon\Mvc\Model\Query;


class PostsController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        if ($this->request->isPost()) {
            if ($this->user) {
                $PostsModel = new ManagerPostsModel();
                $galleryModel = new GalleryModel();
                $bannerModel = new BannerModel();
                $logCodeModel = new LogCodeModel();
                $data = $this->request->getPost();
                $data['us_id'] = $this->user->us_id;
                $PostsModel->create($data);
                $validation = $PostsModel->getMessages();

                //Validation
                if (empty($validation) || is_null($validation)) {



                    //Code User
                    $code = 'VATC' . time();
                    $data_code = array(
                        'us_id' => $this->user->us_id,
                        'lc_code' => $code
                    );
                    $logCodeModel->create($data_code);
                    $body = "Chào " . $this->user->us_name . ",<br><br>";
                    $body .= "Chúc mừng bạn đã đăng bài thi thành công.<br>";
                    $body .= "VATC - Anh Ngữ Việt Mỹ tặng bạn mã dự thưởng : " . $code . " .<br>";
                    $body .= "Thân,<br>";
                    $body .= "VATC - Anh Ngữ Việt Mỹ";
                    $this->sendMail($body,"Mã Dự Thưởng VATC - Anh Ngữ Việt Mỹ",$this->user->us_email,$this->user->us_name,'VATC - Anh Ngữ Việt Mỹ');

                    $respon['status'] = 1;
                    $respon['message'] = 'Thành Công';
                    $respon['description'] = $PostsModel->p_description;
                    $respon['image'] = $bannerModel::findFirst(array("bc_id =7"))->ba_image_link;
                } else {
                    $respon['status'] = 0;
                    $text = array();

                    foreach ($validation as $message) {

                        $text[$message->getField()] = $message->getMessage();
                    }
                    $respon['message'] = $text;
                }
                echo json_encode($respon);
                die;
            }

        }
        $this->view->header_title = "Siêu Giá Sĩ";
    }


}
