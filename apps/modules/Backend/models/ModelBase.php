<?php

namespace Backend\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Behavior\Timestampable;

//use Phalcon\Mvc\Model\Behavior\SoftDelete;

class ModelBase extends Model
{

    public $scenario;
    public $metaData;

//    const DELETED = 'D';
//    const NOT_DELETED = 'N';

    public function initialize()
    {
        $this->addBehavior(
            new Timestampable(
                array(
                    'beforeCreate' => array(
                        'field' => 'created_at',
                        'format' => 'Y-m-d H:i:s'
                    ),
                    'beforeUpdate' => array(
                        'field' => 'update_at',
                        'format' => 'Y-m-d H:i:s'
                    )
                )
            )
        );
//        $this->addBehavior(
//                new SoftDelete(
//                    array(
//                        'field' => 'deleted_at',
//                        'value' => self::DELETED
//                    )
//                )
//        );
    }


    public function setCreateDate()
    {
        if (!empty($this->created_at)) {
            return date("d-m-Y H:i:s", strtotime($this->created_at));
        } else {
            return '';
        }
    }

    public function setUpdateDate()
    {
        if (!empty($this->update_at)) {
            return date("d-m-Y H:i:s", strtotime($this->update_at));
        } else {
            return '';
        }
    }

    public function getSource()
    {
        return "hq_collection";
    }

    public static function vn_str_filter($str)
    {
        $str = str_replace(' ', '-', $str); // Replaces all spaces with hyphens.
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|Ỉ|Ĩ|Ị', 'O' =>
                'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
        );
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }
        $str = preg_replace('/[^A-Za-z0-9\-]/', '', $str); // Removes special chars.
        return $str;
    }

    public static function get_seo($str)
    {
        $str = self::vn_str_filter($str);
        $str = str_replace("--", "-", $str);
        return strtolower($str);
    }
}
