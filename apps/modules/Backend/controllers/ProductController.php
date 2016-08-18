<?php

namespace Backend\Controllers;

use Backend\Models\ProductPriceModel;

class ProductController extends ControllerBase
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
        $dataModel = new \Backend\Models\ProductModel();
        $this->view->setLayout("map");
        $this->view->data = $dataModel::find(array("order" => "pr_create_date desc"));
        $this->view->header_title = "Product Manager";
    }

    public function detailContentAction()
    {
        $productModel = new \Backend\Models\ProductModel();
        $categoryModel = new \Backend\Models\CategoryModel();
        $typeGalleryModel = new \Backend\Models\TypeGaleryModel();
        $optionProductModel = new \Backend\Models\ProductOptionModel();
        $avatarModel = new \Backend\Models\ProductAvatarModel();
        $id = $this->request->getQuery("id");
        if (!empty($id)) {
            $data = $productModel::findFirst($id);
            if ($data) {
                $this->view->data = $data;
                $this->view->image = $avatarModel::findFirst(array("pr_id = '{$id}' and pa_type=1"));
                $this->view->image_hover = $avatarModel::findFirst(array("pr_id = '{$id}' and pa_type=2"));
            } else {
                $this->response->redirect($this->queryUrl . "/detail-content");
                return false;
            }
        }

        //category select boxs
        $category = $categoryModel::find();
        if ($category) {
            $parent_list = array();
            $category_parent = array();

            foreach ($category as $key => $val) {
                $category_parent[$key]['id'] = $val->ct_id;
                if ($val->ct_parent_id != 0 && $val->ct_parent_id != null) {
                    $parent_list[$key] = $val->toArray();
                    $name_parent = $this->createParent($val->ct_parent_id, array());
                    ksort($name_parent);
                    $category_parent[$key]['name_parent'] = $parent_list[$key]['name_parent'] = implode(" > ", $name_parent) . " <strong  >> " . $val->ct_name . "</strong>";
                } else {
                    $parent_list[$key] = $val->toArray();

                    $category_parent[$key]['name_parent'] = $parent_list[$key]['name_parent'] = $val->ct_name;
                }
                $parent_list[$key]['manufacturer_name'] = $val->ManufacturerModel->ma_name;
            }
            $this->view->category_product = $parent_list;
        }
        $this->view->category_type = $typeGalleryModel::find();
        $this->view->category_option = $optionProductModel::find();
        $this->view->setLayout("map");
        $this->view->header_title = "Product Detail Manager";
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $data = json_decode($input['data'], true);
        if (!isset($data['ct_id']) || $data['ct_id'] == '-1') {
            $respon['message'] = 'Không được để trống Category ';
            return $respon;
        }
        if (isset($data['attribute']) && empty($data['attribute'])) {
            $respon['message'] = 'Attribute không được để trống';
            return $respon;
        }
        if (isset($data['price']) && empty($data['price'])) {
            $respon['message'] = 'Giá không được để trống';
            return $respon;
        }
        //check price and quntity from
        foreach ($data['price'] as $pri) {
            if (is_null($pri['hqr_price']) || is_null($pri['hqr_quantity_from'])) {
                $respon['message'] = 'Giá và Số lượng không được để trống';
                return $respon;
                break;
            }
        }

        $buildingModel = new \Backend\Models\ProductModel();
        $optionModel = new \Backend\Models\ProductOptionModel();

        $check_title = $buildingModel::findFirst(array("pr_name='{$data['pr_name']}' and pr_id!='{$data['pr_id']}'"));
        if ($check_title) {
            $respon['message'] = 'Title đã tồn tại';
            return $respon;
        }
        if (!empty($data['pr_public_id'])) {
            $check_public = $buildingModel::findFirst(array("pr_public_id='{$data['pr_public_id']}' and pr_id!='{$data['pr_id']}'"));
            if ($check_public) {
                $respon['message'] = 'Public Id đã tồn tại';
                return $respon;
            }
        }
        $data['pr_status'] = isset($data['pr_status']) ? 1 : 0;
        $data['product_status'] = isset($data['product_status']) ? 1 : 0;
        if ($this->get_seo($data['pr_name']) != $data['pr_seo_link']) {
            $data['pr_seo_link'] = $this->get_seo($data['pr_name']);
        }
        $data['pr_decription'] = $data['description'];
        unset($data['description']);
        if (empty($data['pr_id']) || $data['pr_id'] == NULL) {
            //insert
            unset($data['pr_id']);
            $data['pr_create_date'] = date("Y-m-d H:i:s");
            if ($buildingModel->create($data)) {

                $data['pr_id'] = $buildingModel->pr_id;
                //insert Option detail product
                $option = $optionModel::find();
                foreach ($option as $opt) {
                    if (isset($data[$opt->po_name]) && !empty($data[$opt->po_name])) {
                        foreach ($data[$opt->po_name] as $opt_detai) {
                            $optionDetailModel = new \Backend\Models\ProductOptionDetailModel();
                            $tem_arr = array(
                                'pr_id' => $data['pr_id'],
                                'plo_id' => $opt_detai
                            );
                            $optionDetailModel->save($tem_arr);
                        }
                    }
                }
                //end insert Option detail product
                //insert Attribute
                if (isset($data['attribute']) && !empty($data['attribute'])) {
                    foreach ($data['attribute'] as $attr) {
                        $attributeModel = new \Backend\Models\ProductAttributeModel();
                        $attr_data = array(
                            'pr_id' => $data['pr_id'],
                            'hpa_name' => $attr['hpa_name'],
                            'hpa_description' => $attr['hpa_description'],
                        );
                        $attributeModel->save($attr_data);
                    }
                }
                //end insert Attribute
                //insert Price
                if (isset($data['price']) && !empty($data['price'])) {
                    foreach ($data['price'] as $attr) {
                        $priceModel = new ProductPriceModel();
                        $attr['pr_id'] = $data['pr_id'];
                        if (!isset($attr['hqr_quantity_to']) || is_null($attr['hqr_quantity_to'])) {
                            $attr['hqr_quantity_to'] = Null;
                        }
                        $priceModel->save($attr);
                        $priceMin=end($data['price']);
                        $buildingModel->pr_price=$priceMin['hqr_price'];
                        $buildingModel->update();
                    }
                }
                //end insert Price
                //insert Avatar
                if (!empty($data['ava_img']) || !empty($data['ava_img_hover'])) {
                    $avatar_data = array(
                        array(
                            'pr_id' => $data['pr_id'],
                            'la_id' => 1,
                            'pa_image_link' => $data['ava_img'] ? $data['ava_img'] : '',
                            'pa_type' => 1
                        ),
                        array(
                            'pr_id' => $data['pr_id'],
                            'la_id' => 1,
                            'pa_image_link' => isset($data['ava_img_hover']) ? $data['ava_img_hover'] : '',
                            'pa_type' => 2,
                        ),
                    );
                    foreach ($avatar_data as $ava) {
                        $avatarModel = new \Backend\Models\ProductAvatarModel();
                        $avatarModel->save($ava);
                    }
                }
                // end insert Avatar

                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            $check_product = $buildingModel::findFirst($data['pr_id']);
            if (!$check_product) {
                $respon['message'] = 'Product not exist';
                return $respon;
            }
            //update
            if ($check_product->update($data)) {
                //update Option detail product
                $optionDetailModel = new \Backend\Models\ProductOptionDetailModel();
                $optionDetailModel::find(array("pr_id='{$data['pr_id']}'"))->delete();
                $option = $optionModel::find();
                foreach ($option as $opt) {
                    if (isset($data[$opt->po_name]) && !empty($data[$opt->po_name])) {
                        foreach ($data[$opt->po_name] as $opt_detai) {
                            $optionDetailModel = new \Backend\Models\ProductOptionDetailModel();
                            $tem_arr = array(
                                'pr_id' => $data['pr_id'],
                                'plo_id' => $opt_detai
                            );
                            $optionDetailModel->save($tem_arr);
                        }
                    }
                }
                //update insert Option detail product
                //update Attribute
                $attributeModel = new \Backend\Models\ProductAttributeModel();
                $attributeModel::find(array("pr_id='{$data['pr_id']}'"))->delete();
                if (isset($data['attribute']) && !empty($data['attribute'])) {
                    foreach ($data['attribute'] as $attr) {
                        $attributeModel = new \Backend\Models\ProductAttributeModel();
                        $attr_data = array(
                            'pr_id' => $data['pr_id'],
                            'hpa_name' => $attr['hpa_name'],
                            'hpa_description' => $attr['hpa_description'],
                        );
                        $attributeModel->save($attr_data);
                    }
                }
                //end update Attribute
                //update Price
                $priceModel = new ProductPriceModel();
                $priceModel::find(array("pr_id='{$data['pr_id']}'"))->delete();
                if (isset($data['price']) && !empty($data['price'])) {
                    foreach ($data['price'] as $attr) {
                        $priceModel = new ProductPriceModel();
                        $attr['pr_id'] = $data['pr_id'];
                        $priceModel->save($attr);
                    }
                    $priceMin=end($data['price']);
                    $check_product->pr_price=$priceMin['hqr_price'];
                    $check_product->update();
                }
                //end update Price
                //update Avatar
                $avatarModel = new \Backend\Models\ProductAvatarModel();
                $avatarModel::find(array("pr_id='{$data['pr_id']}'"))->delete();
                if (!empty($data['ava_img']) || !empty($data['ava_img_hover'])) {
                    $avatar_data = array(
                        array(
                            'pr_id' => $data['pr_id'],
                            'la_id' => 1,
                            'pa_image_link' => $data['ava_img'] ? $data['ava_img'] : '',
                            'pa_type' => 1
                        ),
                        array(
                            'pr_id' => $data['pr_id'],
                            'la_id' => 1,
                            'pa_image_link' => isset($data['ava_img_hover']) ? $data['ava_img_hover'] : '',
                            'pa_type' => 2,
                        ),
                    );
                    foreach ($avatar_data as $ava) {
                        $avatarModel = new \Backend\Models\ProductAvatarModel();
                        $avatarModel->save($ava);
                    }
                }
                // end update Avatar
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Update';
            }
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
        $buildingModel = new \Backend\Models\ProductModel();
        $data = $buildingModel::findFirst($input['data']);
        $data->ProductAvatarModel->delete();
        $data->ProductOptionDetailModel->delete();
        $data->ProductAttributeModel->delete();
        $data->ProductImageModel->delete();
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
        $content_obj = new \Backend\Models\CategoryModel();
        $list_parent = $content_obj::findFirst("ct_id= '{$thutuc}'");
        $list_parent = $list_parent->toArray();
        if ($list_parent['ct_parent_id'] != 0 && $list_parent['ct_parent_id'] != null) {
            $arr[$thutuc] = $list_parent['ct_name'];
            $arr = $this->createParent($list_parent['ct_parent_id'], $arr);
        } else {
            $arr[$thutuc] = $list_parent['ct_name'];
        }
        return $arr;
    }

}
