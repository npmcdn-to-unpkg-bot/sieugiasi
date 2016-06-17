<?php

namespace Frontend\Controllers;

use Backend\Models\OrtherPageModel;
use Phalcon\Mvc\Controller;
use Backend\Models\InformationModel;

class ControllerBase extends Controller
{
    protected $fee_tranfer = 55000;

    public function initialize()
    {
        //layout defaulte
        $this->view->setLayout("index");
        //session user login
        $this->user = $this->view->user = $this->getUser();
        //Menu page
        $menuModel = new \Backend\Models\MenuModel();
        $this->view->menu_main = $menuModel::find(array("mn_parent_id=0 and mn_status=1", "order" => "mn_sort asc"));
        //Category News
        $newsCategoryModel = new \Backend\Models\NewsCategoryModel();
        $this->view->category_news = $newsCategoryModel::find(array("nc_status=1"));
        //Information page
        $informationModel = new InformationModel();
        $this->view->information = $informationModel::findFirst();
        //Orther Page
        $ortherPageModel = new OrtherPageModel();
        $this->view->orther_page = $ortherPageModel::find(array("p_status=1"));
    }

    public function createSession($user)
    {
        $this->kichOut($user->us_id);
        $this->memsession->set('USER_HOME', $user);
        $log_sess = $this->memcache->get('login_session_home');
        $log_sess[$user->us_id] = $this->memsession->getId();
        $this->memcache->save('login_session_home', $log_sess);
    }

    public function getUser()
    {
        $user_info = $this->memsession->get('USER_HOME', null);
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
        foreach ($cart['product'] as $key => $product) {
            $cart['weight'] += $product['pr_weight'] * $product['cart_quantity'];
            $cart['product'][$key]['total_price'] = $product['price'] * $product['cart_quantity'];
            $totalPriceOrder +=  $cart['product'][$key]['total_price'];
        }

        $cart['fee_drive'] = $cart['weight'] * $this->fee_tranfer;
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

}
