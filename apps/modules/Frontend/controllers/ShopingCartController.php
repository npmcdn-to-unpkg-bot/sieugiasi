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
        $productListOptionModel = new ProductListOptionModel();
        $buyProduct = $this->memsession->get('BUYPRODUCT', null);
        if (empty($buyProduct)) {
            $respon['status'] = 0;
            $respon['message'] = 'Bạn chưa chọn sản phẩm';
            return $respon;
        }
        $cart = $this->memsession->get('CART', null);
        if (!isset($cart)) {
            $cart = array(
                'product' => array(),
                'total_price_product' => 0,
                'weight' => 0,
                'fee_drive' => 0,
                'coupon' => 0,
                'total_price_order' => 0,
            );
        }
        foreach ($buyProduct as $idProduct => $pro) {
            $productModel = new ProductModel();
            $product = $productModel::findFirst($idProduct);
            if ($product) {
                foreach ($pro['color'] as $idColor => $color) {
                    foreach ($color as $idSize => $quantity) {
                        $productImage = $product->showImage();
                        $newproduct = $product->toArray();
                        $newproduct['pa_image_link'] = $productImage;
                        $newproduct['color'] = $idColor;
                        $newproduct['color_name'] = $productListOptionModel::findFirst($idColor)->plo_name;
                        $newproduct['size'] = $idSize;
                        $newproduct['size_name'] = $productListOptionModel::findFirst($idSize)->plo_name;
                        $newproduct['cart_quantity'] = $quantity;
                        $newproduct['price'] = $pro['price'];
                        $newproduct['total_price'] = $pro['price'] * $quantity;
                        $cart['product'][] = $newproduct;
                    }

                }
            }


        }
        $reCart = $this->recalculationCart($cart);
        $this->memsession->set('CART', $reCart);
        $this->memsession->remove('BUYPRODUCT');
        $respon['status'] = 1;
        $respon['message'] = 'Sản phẩm đã được thêm vào giỏ hàng.';
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

        if ($cart['product'][$id]) {
            $cart['product'][$id]['cart_quantity'] = $quantity;
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


    public function getSizeColorAction($product_id)
    {
        $productModel = new ProductModel();
        $this->view->product = $productModel::findFirst($product_id);
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
    }
    public function removeAllCartAction(){
        $this->memsession->remove('BUYPRODUCT');
        $this->memsession->remove('CART');
        return $this->response->redirect('shoping-cart');
    }
}
