<?php

namespace Frontend\Controllers;

use Backend\Models\OrderModel;
use Backend\Models\OrtherPageModel;
use Backend\Models\ProductModel;
use Phalcon\Mvc\Controller;
use Backend\Models\InformationModel;
require $_SERVER['DOCUMENT_ROOT'] . '/apps/libraries/PHPMailer/PHPMailerAutoload.php';

class ControllerBase extends Controller
{

    public function initialize()
    {
        //layout defaulte
        $this->view->setLayout("index");
        //session user login
        $this->user = $this->view->user = $this->getUser();
        //Menu page
       /* $menuModel = new \Backend\Models\MenuModel();
        $this->view->menu_main = $menuModel::find(array("mn_parent_id=0 and mn_status=1", "order" => "mn_sort asc"));

        //Information page
        $informationModel = new InformationModel();
        $this->view->information = $informationModel::findFirst();
        //Orther Page
        $ortherPageModel = new OrtherPageModel();
        $this->view->orther_page = $ortherPageModel::find(array("p_status=1"));*/


    }

    public function createSession($user)
    {
        $user = (object)$user->toArray();
        $this->kichOut($user->us_id);
        $this->memsession->set('USER_HOME', $user);
        $log_sess = $this->memcache->get('login_session_home');
        $log_sess[$user->us_id] = $this->memsession->getId();
        $this->memcache->save('login_session_home', $log_sess);
    }

    public function getUser()
    {
        $user_info = $this->memsession->get('USER_HOME');
        $log_sess = $this->memcache->get('login_session_home');

        if (!isset($user_info) || $log_sess[$user_info->us_id] != $this->memsession->getId()) {
            $user_info = FALSE;
        }

        return $user_info;
    }

    public function destroySession()
    {
        $this->memsession->remove('USER_HOME');
    }

    public function kichOut($user_id)
    {
        $log_sess = $this->memcache->get('login_session_home');
        if (isset($log_sess->$user_id)) {
            $this->memsession->destroy($log_sess->$user_id);
        }
    }

    public function recalculationCart($cart)
    {
        $totalPriceOrder = 0;
        $totalWeight = 0;
        $quantityForProduct = $this->getQuantityProductCart($cart);
        foreach ($cart['product'] as $key => $product) {
            $totalWeight += $product['pr_weight'] * $product['cart_quantity'];
            //check Promotion
            $productModel = new ProductModel();
            $allPrice = $productModel::getAllPrice($product['pr_id'], false);
            if ($allPrice['promotion'] == 0) {
                foreach ($allPrice['data'] as $priceProduct) {
                    if (!empty($priceProduct->hqr_quantity_to)) {
                        if ($priceProduct->hqr_quantity_from < $quantityForProduct[$product['pr_id']] && $priceProduct->hqr_quantity_to >= $quantityForProduct[$product['pr_id']]) {
                            $cart['product'][$key]['price'] = $priceProduct->hqr_price;
                            break;
                        }
                    } else {
                        $cart['product'][$key]['price'] = $priceProduct->hqr_price;
                        break;
                    }
                }
            } else {
                $cart['product'][$key]['price'] = $allPrice['data'];
            }
            //end
            $cart['product'][$key]['total_price'] = $product['price'] * $product['cart_quantity'];
            $totalPriceOrder += $cart['product'][$key]['total_price'];
        }
        $cart['weight'] = $totalWeight;
        $cart['total_quantity'] = array_sum($quantityForProduct);
        $cart['fee_drive'] = $cart['weight'] * OrderModel::Fee_tranfer;
        $cart['total_price_product'] = $totalPriceOrder;
        $cart['total_price_order'] = $totalPriceOrder + $cart['fee_drive'] - $cart['coupon'];

        if (isset($cart['coupon_code'])) {
            $couponModel = new CouponModel();
            $date = date("Y-m-d");
            $coupon = $couponModel::findFirst(array("co_code =:code: and co_status = 1 and co_number > 0 and co_date_start <= :date: and co_date_end >= :date: ",
                "bind" => (array(
                    "code" => $cart['coupon_code'],
                    "date" => $date,
                ))));
            if ($coupon) {
                //discount
                if ($coupon->co_type == 1) {
                    // Discount for percent

                    $discount = ($cart['total_price_product'] + $cart['fee_drive']) / 100 * $coupon->co_discount;
                    if ($discount > $coupon->co_total) {
                        $discount = $coupon->co_total;
                    }
                } else {
                    $discount = $coupon->co_discount;
                    // Discount for fixed amount
                }
                $cart['coupon'] = $discount;
            } else {
                unset($cart['coupon']);
                unset($cart['coupon_code']);
            }
        }
        return $cart;
    }

    public function getQuantityProductCart($cart)
    {
        $arr = array();
        $price = array();
        $quantity = array();
        foreach ($cart['product'] as $key => $item) {
            $arr[$item['pr_id']][$key] = $item;
        }
        foreach ($arr as $key => $product) {
            foreach ($product as $val) {
                if (isset($quantity[$key])) {
                    $quantity[$key] += $val['cart_quantity'];
                } else {
                    $quantity[$key] = $val['cart_quantity'];
                }
            }
        }
        return $quantity;
    }
    public function sendMail($body,$title,$emailSend,$nameSend,$company){
        $mail = new \PHPMailer;
        $mail->isSMTP();
        $mail->IsHTML(true);
        $mail->CharSet = "UTF-8";
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;
        $mail->Username = $this->module_config_frontend->ACC_GMAIL_SEND_MAIL;
        $mail->Password = $this->module_config_frontend->PASS_GMAIL_SEND_MAIL;
        $mail->setFrom($this->module_config_frontend->NO_REPLY, $company);
        $mail->addAddress($emailSend, $nameSend);
        $mail->Subject = $title;
        $mail->Body = $body;
        $mail->send();

    }

}
