<?php

namespace Backend\Controllers;

class TestController extends ControllerBase {

    public function handleAction() {
        $output = [];
        if (method_exists($this, $_POST['method']))
            $output = $this->{$_POST['method']}($_POST);
        echo json_encode($output);
        die;
    }

    public function indexAction() {
        $this->setScript();
        $this->view->setLayout("access");
        $this->view->header_title = "Map";
    }

    protected function setScript($page = "") {
        $this->assets->addCss('public/BackendMD/css/main.css', true);
        $this->assets->collection("head")
                ->addJs('public/BackendMD/js/plugin/jquery-ui-1.11.4.min.js')
                ->addJs('public/BackendMD/js/plugin/velocity.min.js')
                ->addJs('public/BackendMD/js/plugin/Animate.js')
                ->addJs('public/BackendMD/js/plugin/Scroller.js')
                ->addJs('public/BackendMD/js/plugin/render.js')
                ->addJs('public/BackendMD/js/plugin/nice-scroll.js')
                ->addJs('public/BackendMD/js/plugin/js_excanvas.min.js')
                ->addJs('public/BackendMD/js/animation/map-appear-animation.js');
        $this->assets->collection("inline")
                ->addJs('public/BackendMD/js/object/map.js')
                ->addJs('public/BackendMD/js/main.js');
    }

    public function getMap() {
        if ($this->cacheData->exists("object_map")) {
            $cacheMap = $this->cacheData->get("object_map", 315360000);
        } else {
            $cacheMap = $this->cacheObjectMap();
            $cacheMap = $this->cacheData->get("object_map", 315360000);
        }
        echo $cacheMap;
        die;
    }

    public function getEventClick($input) {
        $data = json_decode($input['data'], true);
        $respon['status'] = 0;
        if (empty($data)) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }
        $EventModel = new \Backend\Models\EventModel();
        $checkObject = $EventModel::findFirst(array("ob_id='{$data['objectID']}' and cte_id=1"));
        if (!$checkObject) {
            $respon['message'] = 'Không tồn tại Object ID';
            
        } else {
            $respon['status'] = 1;
            $respon['message'] = $checkObject->ce_event;
        }
        return $respon;
    }

}
