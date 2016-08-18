<?php

namespace Backend\Controllers;

class StatisticController extends ControllerBase {

    public function getMonthOrderAction() {
        $statisticModel = new \Backend\Models\OrderModel();
        $month = date("n");
        $year = date("Y");
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $month = $data['month'];
            $year = $data['year'];
        }
        $array_data = array();
        $statistic = $statisticModel::find(array(
                    "columns" => array(
                        "value" => "count(or_id)",
                        "time" => "date(or_create_date)"
                    ),
                    "YEAR(or_create_date)='{$year}' and MONTH(or_create_date)='{$month}' GROUP BY date(or_create_date)",
        ));
        if ($statistic) {

            $data = $this->mergeStatistic($statistic, $month, $year);
            $array_data['xAxis'] = array_keys($this->createStatisticArray($month, $year));
            $array_data['data'] = array(
                array(
//                    'type' => "column",
                    'name' => 'Orders',
                    'data' => $data
                ),
            );
            $array_data['data'][] = array(
                'type' => 'pie',
                'name' => 'Tổng',
                'data' => array(
                    array(
                        'name' => 'Orders',
                        'y' => array_sum($data)
                    ),
                ),
                'center' => array('90%', 0),
                'size' => 100,
                'showInLegend' => false,
                'dataLabels' => array(
                    'enabled' => false
                )
            );
            $array_data['title'] = 'Biểu Đồ Thống Kê Đơn Hàng Tháng ' . $month . '-' . $year;
        }
        echo json_encode($array_data);
        die;
    }

    public function getMonthSaleAction() {
        $statisticModel = new \Backend\Models\OrderModel();
        $month = date("n");
        $year = date("Y");
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $month = $data['month'];
            $year = $data['year'];
        }
        $array_data = array();
        $statistic = $statisticModel::find(array(
                    "columns" => array(
                        "value" => "sum(or_total)",
                        "time" => "date(or_create_date)"
                    ),
                    "YEAR(or_create_date)='{$year}' and MONTH(or_create_date)='{$month}' and os_id = 2 GROUP BY date(or_create_date)",
        ));
        if ($statistic) {

            $data = $this->mergeStatistic($statistic, $month, $year);
            $array_data['xAxis'] = array_keys($this->createStatisticArray($month, $year));
            $array_data['data'] = array(
                array(
//                    'type' => "column",
                    'name' => 'Sale',
                    'data' => $data
                ),
            );
            $array_data['data'][] = array(
                'type' => 'pie',
                'name' => 'Tổng',
                'data' => array(
                    array(
                        'name' => 'Sale',
                        'y' => array_sum($data)
                    ),
                ),
                'center' => array('90%', 0),
                'size' => 100,
                'showInLegend' => false,
                'dataLabels' => array(
                    'enabled' => false
                )
            );
            $array_data['title'] = 'Sales Statistic Monthly ' . $month . '-' . $year;
        }
        echo json_encode($array_data);
        die;
    }

    public function getMonthUserAction() {
        $statisticModel = new \Backend\Models\UserModel();
        $month = date("n");
        $year = date("Y");
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $month = $data['month'];
            $year = $data['year'];
        }
        $array_data = array();
        $statistic = $statisticModel::find(array(
                    "columns" => array(
                        "value" => "count(us_id)",
                        "time" => "date(created_at)"
                    ),
                    "YEAR(created_at)='{$year}' and MONTH(created_at)='{$month}' GROUP BY date(created_at)",
        ));
        if ($statistic) {

            $data = $this->mergeStatistic($statistic, $month, $year);
            $array_data['xAxis'] = array_keys($this->createStatisticArray($month, $year));
            $array_data['data'] = array(
                array(
//                    'type' => "column",
                    'name' => 'Customers',
                    'data' => $data
                ),
            );
            $array_data['data'][] = array(
                'type' => 'pie',
                'name' => 'Tổng',
                'data' => array(
                    array(
                        'name' => 'Customers',
                        'y' => array_sum($data)
                    ),
                ),
                'center' => array('90%', 0),
                'size' => 100,
                'showInLegend' => false,
                'dataLabels' => array(
                    'enabled' => false
                )
            );
            $array_data['title'] = 'Customers Statistic Monthly ' . $month . '-' . $year;
        }
        echo json_encode($array_data);
        die;
    }

    public function getYearStatisticAction() {
        $statisticModel = new \Multiple\Backend\Models\StatisticLauncherModel();
        $year = date("Y");
        if ($this->request->isPost()) {
            $year = $this->request->getPost("year");
        }
        $array_data = array();
        $statistic = $statisticModel::find(array(
                    "columns" => array(
                        "date" => "MONTH(date)",
                        "install" => "SUM(install)",
                        "uninstall" => "SUM(uninstall)",
                        "first_login" => "SUM(first_login)",
                        "download" => "SUM(download)",
                        "play" => "SUM(play)",
                        "active_user" => "SUM(active_user)",
                        "daily_active" => "SUM(daily_active)",
                    ),
                    "YEAR(date)='{$year}' GROUP BY MONTH(date)",
        ));
        if ($statistic) {
            $data_install = array();
            $data_uninstall = array();
            $data_first_login = array();
            $data_download = array();
            $data_play = array();
            $data_active_user = array();
            $data_daily_active = array();
            foreach ($statistic as $sta) {
                $data_install[] = (int) $sta->install;
                $data_uninstall[] = (int) $sta->uninstall;
                $data_first_login[] = (int) $sta->first_login;
                $data_download[] = (int) $sta->download;
                $data_play[] = (int) $sta->play;
                $data_active_user[] = (int) $sta->active_user;
                $data_daily_active[] = (int) $sta->daily_active;
                $array_data['xAxis'][] = (int) $sta->date;
            }
            $array_data['data'] = array(
                array(
                    'type' => "column",
                    'name' => 'Install',
                    'data' => $data_install
                ),
                array(
                    'type' => "column",
                    'name' => 'Uninstall',
                    'data' => $data_uninstall
                ),
                array(
                    'type' => "column",
                    'name' => 'First Login',
                    'data' => $data_first_login
                ),
                array(
                    'type' => "column",
                    'name' => 'Download',
                    'data' => $data_download
                ),
                array(
                    'type' => "column",
                    'name' => 'Play',
                    'data' => $data_play
                ),
                array(
                    'type' => "column",
                    'name' => 'Active User',
                    'data' => $data_active_user
                ),
                array(
                    'type' => "column",
                    'name' => 'Daily Active',
                    'data' => $data_daily_active
                ),
            );
            $array_data['data'][] = array(
                'type' => 'pie',
                'name' => 'Tổng',
                'data' => array(
                    array(
                        'name' => 'Install',
                        'y' => array_sum($data_install)
                    ),
                    array(
                        'name' => 'Uninstall',
                        'y' => array_sum($data_uninstall)
                    ),
                    array(
                        'name' => 'First Login',
                        'y' => array_sum($data_first_login)
                    ),
                    array(
                        'name' => 'Download',
                        'y' => array_sum($data_download)
                    ),
                    array(
                        'name' => 'Play',
                        'y' => array_sum($data_play)
                    ),
                    array(
                        'name' => 'Active User',
                        'y' => array_sum($data_active_user)
                    ),
                    array(
                        'name' => 'Daily Active',
                        'y' => array_sum($data_daily_active)
                    ),
                ),
                'center' => array('90%', 0),
                'size' => 100,
                'showInLegend' => false,
                'dataLabels' => array(
                    'enabled' => false
                )
            );
            $array_data['title'] = 'FirePlay Statistic Yearly ' . $year;
        }
        echo json_encode($array_data);
        die;
    }

    public function getTodayStatisticAction() {
//        $orderModel = new \Backend\Models\OrderModel();
        $userModel = new \Backend\Models\UserModel();
        $today = date("Y-m-d");
//        $order = $orderModel::find(array("date(or_create_date)='{$today}'"));
//        $order_new = $orderModel::find(array("date(or_create_date)='{$today}' and os_id='7'"));
//        $total_sale = $orderModel::findFirst(array(
//                    "columns" => array(
//                        "total" => "SUM(or_total)",
//                    ),
//                    "date(or_create_date)='{$today}' and os_id='2'",
//        ));
        $user = $userModel::find(array("date(created_at)='{$today}'"));
//        $statistic['order'] = count($order);
//        $statistic['order_new'] = count($order_new);
//        $statistic['total_sale'] = $total_sale->total;
        $statistic['customer'] = count($user);
        echo json_encode($statistic);
        die;
    }

    private function mergeStatistic($array, $month = false, $year = false) {
        $array_date = array();
        if (!empty($array)) {
            foreach ($array as $item) {
                $time = explode(' ', $item['time']);
                $array_date[$time[0]] = intval($item['value']);
            }
        }

        $array_date_create = $this->createStatisticArray($month, $year);
        $statistic = array_merge($array_date_create, $array_date);
        $array_date_create = array_keys(array_keys($array_date_create));
        ksort($statistic);
        return array_combine($array_date_create, $statistic);
    }

    private function createStatisticArray($month, $year) {
        $arr = array();
        $m = $month < 10 ? '0' . $month : $month;
        $number_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        for ($i = 1; $i <= $number_days; $i++) {
            $d = $i < 10 ? '0' . $i : $i;
            $arr[$year . '-' . $m . '-' . $d] = 0;
        }
        return $arr;
    }

}
