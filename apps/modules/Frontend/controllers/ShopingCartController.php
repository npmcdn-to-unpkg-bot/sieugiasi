<?php

namespace Frontend\Controllers;


use Backend\Models\CouponLogModel;
use Backend\Models\CouponModel;
use Backend\Models\ProductListOptionModel;
use Backend\Models\ProductModel;

class ShopingCartController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
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
        $this->view->cart = $this->memsession->get('CART', null);
        $this->view->header_title = "Shoping Cart";
    }

    public function addCart($request)
    {
        $cart = $this->memsession->get('CART', null);
        if (!isset($cart)) {
            $cart = array(
                'product' => array(),
                'total_price_product' => 0,
                'fee_drive' => 0,
                'coupon' => 0,
                'total_price_order' => 0,
            );
        }

        $temp_arr = array();
        $totalPriceOrder = 0;
        $productModel = new ProductModel();
        $productListOptionModel=new ProductListOptionModel();
        $product = $productModel::findFirst($request['id']);
        if ($product) {

            $productImage = $product->showImage();
            $product = $product->toArray();
            $product['pa_image_link'] = $productImage;
            $quantity = 1;
            //Chỉ dc mua tối đa 5 sản phẩm 1 loại
            if ($quantity > 5) {
                $respon['status'] = 0;
                $respon['message'] = 'Chỉ được mua tối đa 5 sản phẩm cùng loại';
                return $respon;
            }
            if ($product['pr_quantity'] < $quantity) {
                $respon['status'] = 0;
                $respon['message'] = 'Số lượng hàng trong kho không đủ';
                return $respon;
            }
            if ($product['pr_price_promotion'] > 0) {
                $totalPrice = $product['pr_price_promotion'] * $quantity;
            } else {
                $totalPrice = $product['pr_price'] * $quantity;
            }

            $temp_arr = $product;
            $size=$request['size'];
            $color=$request['color'];
            $temp_arr['size']=$size;
            $temp_arr['size_name']=$productListOptionModel::findFirst($size)->plo_name;
            $temp_arr['color']=$color;
            $temp_arr['color_name']=$productListOptionModel::findFirst($color)->plo_name;
            $temp_arr['cart_quantity'] = $quantity;
            $temp_arr['total_price'] = $totalPrice;
            $cart['product'][] = $temp_arr;

            $reCart = $this->recalculationCart($cart);
            $this->memsession->set('CART', $reCart);
            $respon['status'] = 1;
            $respon['message'] = 'Sản phẩm đã được thêm vào giỏ hàng.';
            return $respon;
        }
        $respon['status'] = 0;
        $respon['message'] = 'Không thể Thêm';
        return $respon;
    }

    public function removeProductCart($request)
    {
        $cart = $this->memsession->get('CART', null);
        $id = $request['id'];
        unset($cart['product'][$id]);
        $reCart = $this->recalculationCart($cart);
        $this->memsession->set('CART', $reCart);
        $respon['total_price_product'] = number_format($reCart['total_price_product']) . " Vnđ";
        $respon['total_price_order'] = number_format($reCart['total_price_order']) . " Vnđ";
        $respon['fee_drive'] = $reCart['fee_drive'] > 0 ? number_format($reCart['fee_drive']) . " Vnđ" : 'Miễn Phí';
        $respon['coupon'] = number_format($reCart['coupon']) . " Vnđ";
        $respon['status'] = 1;
        return $respon;
    }

    public function changeNumberCart($request)
    {
        $cart = $this->memsession->get('CART', null);
        $id = $request['id'];
        $quantity = $request['quantity'];
        //Chỉ dc mua tối đa 5 sản phẩm 1 loại
        if ($quantity > 5 || $quantity < 0) {
            $respon['status'] = 0;
            $respon['message'] = 'Số lượng không hợp lệ';
            return $respon;
        }

        if ($cart['product'][$id]) {
            $productModel = new ProductModel();
            $product = $productModel::findFirst($cart['product'][$id]['pr_id']);
            if ($product->pr_quantity < $quantity) {
                $respon['status'] = 0;
                $respon['message'] = 'Số lượng hàng trong kho không đủ';
                return $respon;
            }

            $cart['product'][$id]['cart_quantity'] = $quantity;
            if ($cart['product'][$id]['pr_price_promotion'] > 0) {
                $cart['product'][$id]['total_price'] = $cart['product'][$id]['pr_price_promotion'] * $quantity;
            } else {
                $cart['product'][$id]['total_price'] = $cart['product'][$id]['pr_price'] * $quantity;
            }

            $reCart = $this->recalculationCart($cart);
            $this->memsession->set('CART', $reCart);

            $respon['total_price'] = number_format($cart['product'][$id]['total_price']) . " Vnđ";
            $respon['total_price_product'] = number_format($reCart['total_price_product']) . " Vnđ";
            $respon['total_price_order'] = number_format($reCart['total_price_order']) . " Vnđ";
            $respon['fee_drive'] = $reCart['fee_drive'] > 0 ? number_format($reCart['fee_drive']) . " Vnđ" : 'Miễn Phí';
            $respon['coupon'] = number_format($reCart['coupon']) . " Vnđ";
            $respon['status'] = 1;
            return $respon;
        }
        $respon['status'] = 0;
        $respon['message'] = 'Không thể Thêm';
        return $respon;
    }

    public function addCoupon($request)
    {
        if ($this->user) {
            $code = $request['code'];
            if (empty($code)) {
                $respon['status'] = 0;
                $respon['message'] = 'Vui lòng nhập Mã Giảm Giá';
                return $respon;
            }
            $cart = $this->memsession->get('CART', null);
            if (!isset($cart) || empty($cart['product'])) {
                $respon['status'] = 0;
                $respon['message'] = 'Giỏ hàng không có sản phẩm nào';
                return $respon;
            }
            $couponModel = new CouponModel();
            $couponLogModel = new CouponLogModel();

            //find coupon
            $date = date("Y-m-d");
            $coupon = $couponModel::findFirst(array("co_code =:code: and co_status = 1 and co_number > 0 and co_date_start <= :date: and co_date_end >= :date: ",
                "bind" => (array(
                    "code" => $code,
                    "date" => $date,
                ))));
            if (!$coupon) {
                $respon['status'] = 0;
                $respon['message'] = 'Mã Giảm Giá không hợp lệ hoặc đã hết hạn.';
                return $respon;
            }

            //Check user use coupon

            $checkUser = $couponLogModel::find(array("co_code= '{$code}' and us_id = '{$this->user->us_id}' "));
            if (count($checkUser) >= $coupon->co_uses_total) {
                $respon['status'] = 0;
                $respon['message'] = 'Bạn đã sử dụng mã giảm giá này rồi.';
                return $respon;
            }

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
            $cart['coupon_code'] = $code;
            $reCart = $this->recalculationCart($cart);
            $this->memsession->set('CART', $reCart);
            $respon['status'] = 1;
            $respon['message'] = 'Mã Giảm Giá hợp lệ.';
            return $respon;
        }
    }

    public function recalculationCart($cart)
    {
        $totalPriceOrder = 0;
        $totalPrice = 0;
        foreach ($cart['product'] as $key => $product) {
            if ($product['pr_price_promotion'] > 0) {
                $totalPrice = $product['pr_price_promotion'] * $product['cart_quantity'];
            } else {
                $totalPrice = $product['pr_price'] * $product['cart_quantity'];
            }
            $cart['product'][$key]['total_price'] = $totalPrice;
            $totalPriceOrder += $totalPrice;
        }


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

    public function getSizeColorAction($product_id)
    {
        $productModel=new ProductModel();
        $this->view->product=$productModel::findFirst($product_id);
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
    }
}
