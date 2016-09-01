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
use Backend\Models\UserModel;
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
                $userModel = new UserModel();
                $data = $this->request->getPost();
                $data['us_id'] = $this->user->us_id;
                $PostsModel->create($data);
                $validation = $PostsModel->getMessages();

                //Validation
                if (empty($validation) || is_null($validation)) {


                    $user = $userModel::findFirst($this->user->us_id);
                    $user->update(array('us_phone' => $data['us_phone']));
                    $this->createSession($user);
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
                    $this->sendMail($body, "Mã Dự Thưởng VATC - Anh Ngữ Việt Mỹ", $this->user->us_email, $this->user->us_name, 'VATC - Anh Ngữ Việt Mỹ');

                    //image share
                    $image_share=$this->addTextImageShare($PostsModel->hlv_id,$PostsModel->p_description,$PostsModel->p_id);

                    $respon['status'] = 1;
                    $respon['message'] = 'Thành Công';
                    $respon['description'] = $PostsModel->p_description;
                    $respon['image'] = $image_share;
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

    public function addTextImageShare($hlv_id, $text, $postId)
    {
        header('Content-Type: image/png');
        $rootFolder = $_SERVER['DOCUMENT_ROOT'] . "/public/";
        //font
        $font = $rootFolder . 'FrontendCore/fonts/Roboto-Black.ttf';
        //font size
        $font_size = 16;
        //image width
//        $width = 690;
        $width = 1140;
        //text margin
        $margin = 5;
        // lấy 1000 ký tự của text
        $text = substr($text, 0, 1000);

        //explode text by words
        $text_a = explode(' ', $text);
        $text_new = '';
        foreach ($text_a as $word) {
            //Create a new text, add the word, and calculate the parameters of the text
            $box = imagettfbbox($font_size, 0, $font, $text_new . ' ' . $word);
            //if the line fits to the specified width, then add the word with a space, if not then add word with new line
            if ($box[2] > $width - $margin * 2) {
                $text_new .= "\n" . $word;
            } else {
                $text_new .= " " . $word;
            }
        }
        //trip spaces
        $text_new = trim($text_new);
        //new text box parameters
        $box = imagettfbbox($font_size, 0, $font, $text_new);
        //new text height
        $height = $box[1] + $font_size + $margin * 2;

        //create image


        $im = imagecreatetruecolor($width, $height);

        //create colors
        $white = imagecolorallocate($im, 255, 255, 255);
        $colorText = imagecolorallocate($im, 0, 0, 0);
        //color image
        imagefilledrectangle($im, 0, 0, $width, $height, $white);

        //add text to image
        imagettftext($im, $font_size, 0, $margin, $font_size + $margin, $colorText, $font, $text_new);


//        $image_original = imagecreatefromjpeg($rootFolder . 'FrontendCore/images/share_' . $hlv_id . '.jpg');
        $image_original = imagecreatefromjpeg($rootFolder . 'FrontendCore/images/test_share.jpg');

        //forder share
        $folderShare = $rootFolder . "uploads/images/sharePosts";
        if (!is_dir($folderShare)) {
            mkdir($folderShare);
        }

        //image share
        $image_share = $folderShare ."/". $postId . '.jpg';


//        imagecopymerge($image_original, $im, 35, 90, 0, 0, $width, $height, 75);
        imagecopymerge($image_original, $im, 30, 30, 0, 0, $width, $height, 75);

        imagejpeg($image_original, $image_share, 100);
        //frees any memory associated with image
        imagedestroy($image_original);
        imagedestroy($im);
        return "public/uploads/images/sharePosts/". $postId . '.jpg';
    }


}
