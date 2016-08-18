<?php

namespace Backend\Models;

class ManufacturerModel extends ModelBase
{

    public static $Ninomaxx = 3;
    public static $N_M = 2;

    public function getSource()
    {
        return "hq_manufacturer";
    }

    public function initialize()
    {
        parent::initialize();
        $this->hasMany("ma_id", "\Backend\Models\CategoryModel", "ma_id", array('alias' => 'CategoryModel'));
    }

    public function updateByID($data, $id)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "ma_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

    public function getCategory()
    {
        $temp = array();
        if (count($this->CategoryModel)) {
            foreach ($this->CategoryModel as $cate) {
                if ($cate->ct_parent_id == 0 || is_null($cate->ct_parent_id)) {
                    $temp[] = $cate;
                }
            }
        }
        return $temp;
    }

    public function getProduct()
    {
        $product = $arr_category = array();
        if (count($this->CategoryModel)) {
            foreach ($this->CategoryModel as $cate) {
                $arr_category[] = $cate->ct_id;
            }

            $string = implode(",", $arr_category);
            $productModel = new ProductModel();
            $product = $productModel::find(array("ct_id in ({$string}) and pr_status =1", "order" => "pr_create_date desc", "limit" => 10));
        }
        return $product;
    }
}
