<?php

namespace Backend\Controllers;

class CategoryProductController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
        if (!in_array($this->user['usa_username'], $this->userRole['Admin']) && !in_array($this->user['usa_username'], $this->userRole['Category Product'])) {
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
        $dataModel = new \Backend\Models\CategoryModel();
        $manufacturerModel = new \Backend\Models\ManufacturerModel();
        $data = $dataModel::find();
        if ($data) {
            $parent_list = array();
            $category_parent = array();

            foreach ($data as $key => $val) {

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

            $this->view->category_data = $category_parent;
            $this->view->data = $parent_list;
        }

        $this->view->manufacturer_cate = $manufacturerModel::find();
        $this->view->setLayout("map");
        $this->view->header_title = "Category Product Manager";
    }

    public function detailContent($input)
    {
        $dataModel = new \Backend\Models\CategoryModel();
        $data = $dataModel::findFirst($input['id']);
        echo json_encode($data);
        die;
    }

    public function newEditContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($input['data'], true);

        if (empty($data['ct_name'])) {
            $respon['message'] = 'Không được để trống Title và Link ';
            return $respon;
        }
        if (empty($data['ma_id']) || !isset($data['ma_id']) || $data['ma_id'] == 0) {
            $respon['message'] = 'Chưa chọn Manufacturer';
            return $respon;
        }
        $data['ct_status'] = isset($data['ct_status']) ? 1 : 0;
        if ($this->get_seo($data['ct_name']) != $data['ct_seo_link']) {
            $data['ct_seo_link'] = $this->get_seo($data['ct_name']);
        }
        $data['ct_description'] = $data['description'];
        unset($data['description']);

        $buildingModel = new \Backend\Models\CategoryModel();
        if (empty($data['ct_id']) || $data['ct_id'] == NULL) {
            //insert
            unset($data['ct_id']);
            if ($buildingModel->create($data)) {
                $respon['status'] = 1;
                $respon['message'] = 'Thành Công';
            } else {
                $respon['message'] = 'Không thể Thêm';
            }
        } else {
            //update
            $update = $buildingModel::find($data['ct_id']);
            if ($data['ct_id'] == $data['ct_parent_id']) {
                $data['ct_parent_id'] = 0;
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

    public function deleteContent($input)
    {
        $respon['status'] = 0;
        if (empty($input['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $buildingModel = new \Backend\Models\CategoryModel();
        $string_delete = substr($this->createParentDelete($input['data'], ''), 0, -1);
        $data = $buildingModel::find('ct_id IN (' . $string_delete . ')');
        foreach ($data as $cate) {
            if (count($cate->ProductModel) > 0) {
                foreach ($cate->ProductModel as $pro) {
                    $productModel = new \Backend\Models\ProductModel();
                    $product = $productModel::findFirst($pro->pr_id);
                    $product->ProductAvatarModel->delete();
                    $product->ProductOptionDetailModel->delete();
                    $product->ProductAttributeModel->delete();
                    $product->ProductImageModel->delete();
                }
                $cate->ProductModel->delete();
            }
        }
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
        if ($list_parent) {
            $list_parent = $list_parent->toArray();
            if ($list_parent['ct_parent_id'] != 0 && $list_parent['ct_parent_id'] != null) {
                $arr[$thutuc] = $list_parent['ct_name'];
                $arr = $this->createParent($list_parent['ct_parent_id'], $arr);
            } else {
                $arr[$thutuc] = $list_parent['ct_name'];
            }
        }
        return $arr;
    }

    public function createParentDelete($id)
    {
        $str = $id . ",";
        $content_obj = new \Backend\Models\CategoryModel();
        $list_parent = $content_obj::find("ct_parent_id= '{$id}'");
        if ($list_parent) {
            foreach ($list_parent as $list) {
                if ($list->ct_parent_id != 0 && $list->ct_parent_id != null) {
                    $str .= $this->createParentDelete($list->ct_id);
                } else {
                    $str .= $list->ct_id . ",";
                }
            }
        }
        return $str;
    }

    public function changeManufactureAction()
    {
        if ($this->request->isPost()) {
            $id = $this->request->getPost('id');
            $categoryModel = new \Backend\Models\CategoryModel();
            $category = $categoryModel::find(array("ma_id = '{$id}'"));

            $parent_list = array();
            $category_parent = array();
            if ($category) {
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
            }
            $this->view->category_data = $category_parent;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

}
