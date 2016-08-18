<?php

namespace Backend\Models;

class ProductModel extends ModelBase
{

    public function getSource()
    {
        return "hq_product";
    }

    public function initialize()
    {
        parent::initialize();
        $this->belongsTo("ct_id", "\Backend\Models\CategoryModel", "ct_id", array('alias' => 'CategoryModel'));
        $this->hasMany("pr_id", "\Backend\Models\ProductAvatarModel", "pr_id", array('alias' => 'ProductAvatarModel'));
        $this->hasMany("pr_id", "\Backend\Models\ProductOptionDetailModel", "pr_id", array('alias' => 'ProductOptionDetailModel'));
        $this->hasMany("pr_id", "\Backend\Models\ProductAttributeModel", "pr_id", array('alias' => 'ProductAttributeModel'));
        $this->hasMany("pr_id", "\Backend\Models\ProductImageModel", "pr_id", array('alias' => 'ProductImageModel'));
        $this->hasMany("pr_id", "\Backend\Models\ProductPriceModel", "pr_id", array('alias' => 'ProductPriceModel'));
        $this->hasMany("pr_id", "\Backend\Models\CollectionModel", "pr_id", array('alias' => 'CollectionModel'));

    }

    public function beforeCreate()
    {
        if ($this->pr_quantity <= 0) {
            $this->pr_quantity = 0;
            $this->product_status = 0;
        }
    }

    public function beforeUpdate()
    {

        if ($this->pr_quantity <= 0) {
            $this->pr_quantity = 0;
            $this->product_status = 0;
        }
    }

    public function updateByID($data, $id)
    {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "pr_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

    public function getMinimumPrice()
    {
        $productPriceModel = new ProductPriceModel();
        $price = $productPriceModel::minimum(array("pr_id ='{$this->pr_id}'", "column" => "hqr_price"));
        if ($price) {
            return number_format($price) . " Vnđ";
        }
        return '';
    }

    public function getMaximumPrice($format = false)
    {
        $productPriceModel = new ProductPriceModel();
        $price = $productPriceModel::maximum(array("pr_id ='{$this->pr_id}'", "column" => "hqr_price"));
        if ($price) {
            if (!$format) {
                return number_format($price) . " Vnđ";
            } else {
                return $price;
            }

        }
        return '';
    }

    public function showPrice()
    {
        return number_format($this->pr_price) . " Vnđ";
    }


    public function showPricePromotion()
    {
        $date = date("Y-m-d");
        if ($this->pr_date_sale_from <= $date && $date <= $this->pr_date_sale_to) {
            if ($this->pr_price_promotion != 0) {
                return number_format($this->pr_price_promotion) . " Vnđ";
            } else {
                return false;
            }
        }
    }

    public function showImage()
    {
        $product = $this->getProductAvatarModel(array("pa_type = 1"));
        if ($product) {
            return $product[0]->pa_image_link;
        } else {
            return '';
        }
    }

    public function showImageHover()
    {
        $product = $this->getProductAvatarModel(array("pa_type = 2"));
        if ($product) {
            return $product[0]->pa_image_link;
        } else {
            return '';
        }
    }

    public function showSize()
    {
        $idSize = ProductListOptionModel::$Size;
        $listSize = array();
        $productOptionDetail = $this->getProductOptionDetailModel(array("pr_id = '{$this->pr_id}'"));
        if ($productOptionDetail) {
            foreach ($productOptionDetail as $option) {
                if (isset($option->ProductListOptionModel) && $option->ProductListOptionModel->getProductOptionModel(array("po_id = '{$idSize}'"))) {
                    $listSize[$option->ProductListOptionModel->plo_id] = $option->ProductListOptionModel->plo_name;
                }
            }
        }
        return $listSize;
    }

    public function showColor()
    {
        $idColor = ProductListOptionModel::$Color;
        $listColor = array();
        $productOptionDetail = $this->getProductOptionDetailModel(array("pr_id = '{$this->pr_id}'"));
        if ($productOptionDetail) {
            foreach ($productOptionDetail as $option) {
                if (isset($option->ProductListOptionModel) && $option->ProductListOptionModel->getProductOptionModel(array("po_id = '{$idColor}'"))) {
                    $listColor[] = (Object)array("plo_id" => $option->ProductListOptionModel->plo_id, "plo_detail" => $option->ProductListOptionModel->plo_detail, "plo_name" => $option->ProductListOptionModel->plo_name);
                }
            }
        }
        return $listColor;
    }

    public function showParentCategory()
    {
        $name = '';
        if (isset($this->CategoryModel)) {
            $categoryModel = new CategoryModel();
            $category = $categoryModel::findFirst(array("ct_id = '{$this->CategoryModel->ct_parent_id}'"));
            return (object)array('ct_id' => $category->ct_id, 'ct_name' => $category->ct_name, 'ct_seo_link' => $category->ct_seo_link);
        }
        return array();

    }

    public function showGalleryDefault()
    {
        $listColor = $this->showColor();
        if (isset($listColor)) {
            foreach ($listColor as $color) {
                $gallery = $this->getProductImageModel(array("plo_id = {$color->plo_id}"));
                if (count($gallery) > 0) {
                    return $gallery;
                }
            }

        }
        return false;
    }


    public function formatDate($date)
    {
        return date("d/m", strtotime($date));
    }

    public function countOrder()
    {
        $orderDetailModel = new OrderDetailModel();
        $orderProduct = $orderDetailModel::sum(array("column" => "od_quantity", "conditions" => "pr_id='{$this->pr_id}'"));
        if ($orderProduct) {
            return $orderProduct;
        } else {
            return 0;
        }
    }

    public function timeLeft()
    {
        return ceil((strtotime($this->pr_date_sale_to) - time()) / (60 * 60 * 24));
    }

    public function showQuantity()
    {
        if ($this->pr_quantity == 0 || empty($this->pr_quantity)) {
            return 'Hết Hàng';
        }
        return number_format($this->pr_quantity, 0);
    }

    public function getProductTopSale($top)
    {

        $topOrder = OrderDetailModel::find(array("columns" => "pr_id, SUM(od_quantity) as sum", "group" => "pr_id", "order" => "SUM(od_quantity) desc", "limit" => $top));
        if ($topOrder) {
            $string = '';
            foreach ($topOrder as $val) {
                $string .= $val->pr_id . ",";
            }
            $string = rtrim($string, ',');
            $product = self::find(array("pr_id in ({$string})"));
            return $product;
        }
    }

    public static function getAllPrice($idProduct, $format = true)
    {
        $product = self::findFirst($idProduct);
        $date = date("Y-m-d");
        $price = array();
        if ($product->pr_price_promotion != 0 && $product->pr_date_sale_from <= $date && $date <= $product->pr_date_sale_to) {
            $price['promotion'] = 1;
            if ($format) {
                $price['data'] = number_format($product->pr_price_promotion) . " Vnđ";
            } else {
                $price['data'] = $product->pr_price_promotion;
            }

        } else {
            $productPriceModel = new ProductPriceModel();
            $price['promotion'] = 0;
            $price['data'] = $productPriceModel::find(array("pr_id ='{$idProduct}'"));
        }

        return $price;
    }

}
