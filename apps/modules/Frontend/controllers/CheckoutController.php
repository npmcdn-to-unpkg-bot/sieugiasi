<?php

namespace Frontend\Controllers;

use Backend\Models\CouponLogModel;
use Backend\Models\OrderDetailModel;
use Backend\Models\OrderLogModel;
use Backend\Models\OrderModel;
use Backend\Models\ProductModel;
use Backend\Models\ZoneDistrictModel;
use Backend\Models\ZoneProvinceModel;
use Backend\Models\ZoneWardModel;

require $_SERVER['DOCUMENT_ROOT'] . '/ninomax/apps/libraries/Payment/NL_Checkoutv3.php';

class CheckoutController extends ControllerBase
{
    protected $MERCHANT_ID = '46594';
    protected $MERCHANT_PASS = 'fde95097199a35450d25c143ce048250';
    protected $RECEIVER = 'namtrung804@gmail.com';
    protected $URL_API = 'https://www.nganluong.vn/checkout.api.nganluong.post.php';


    public function initialize()
    {
        parent::initialize();
        $cart = $this->memsession->get('CART', null);
        if (!isset($cart) && $this->dispatcher->getActionName() != 'paymentsuccess') {
            return $this->response->redirect('category-product');
        }
        $this->view->cart = $cart;
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
        $provinceModel = new ZoneProvinceModel();
        $districtModel = new ZoneDistrictModel();
        $wardModel = new ZoneWardModel();
        $this->view->province = $provinceModel::find(array("order" => "zp_name asc"));
        $this->view->district = $districtModel::find();
        $this->view->ward = $wardModel::find();
        $this->view->header_title = "Ninomaxx";
    }

    public function payment($request)
    {
        $data_address = array();
        $respon['status'] = 0;
        $option_payment = $request['option_payment'];
        $bankcode = isset($request['bankcode']) ? $request['bankcode'] : '';
        $form_id = $request['form_id'];
        foreach ($request['data_address'] as $val) {
            $data_address[$val['name']] = $val['value'];
        }
        $orderModel = new OrderModel();
        $couponLogModel = new CouponLogModel();
        //Validation
        $validation = $orderModel->validationRequest($data_address);
        if ($validation) {

            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation
        $cart = $this->memsession->get('CART', null);
        $nlcheckout = new  \NL_CheckOutV3($this->MERCHANT_ID, $this->MERCHANT_PASS, $this->RECEIVER, $this->URL_API);
        // Param transaction
        $data_address['or_public_id'] = $transaction_id = "N" . $this->user->us_id . time();
        $URL_CALLBACK = $this->config['rootUrl'] . 'checkout/payment-callback';
        $URL_CANCEL = $this->config['rootUrl'] . 'checkout/payment-cancel?orderid=' . $transaction_id;
        $description = isset($data_address['or_description']) ? $data_address['or_description'] : '';
        $data_address['us_id'] = $this->user->us_id;
        $data_address['or_total'] = $cart['total_price_order'];
        $data_address['or_fee_drive'] = $cart['fee_drive'];
        $data_address['or_discount'] = $cart['coupon'];
        $data_address['os_id'] = 7; // Trạng thái mạc định của order
        $data_address['or_description'] = $description;
        $data_address['or_payment_type'] = $option_payment;
        $data_address['or_create_date'] = date("Y-m-d H:i:s");
        switch ($option_payment) {
            case 'ATM_ONLINE':
                $nl_result = $nlcheckout->BankCheckout(
                    $transaction_id, $cart['total_price_order'], $bankcode, 1,
                    $description, 0, 0, 0,
                    $URL_CALLBACK, $URL_CANCEL, $data_address['or_name'], $data_address['or_email'],
                    $data_address['or_phone'], $data_address['or_address'] . "|" . $data_address['zd_id'] . "|" . $data_address['zp_id'], $cart['product']);
                break;
            case 'VISA':
                $nl_result = $nlcheckout->VisaCheckout(
                    $transaction_id, $cart['total_price_order'], 1,
                    $description, 0, 0, 0,
                    $URL_CALLBACK, $URL_CANCEL, $data_address['or_name'], $data_address['or_email'],
                    $data_address['or_phone'], $data_address['or_address'] . "|" . $data_address['zd_id'] . "|" . $data_address['zp_id'], $cart['product'], '');
                break;
            default:
                if ($orderModel->create($data_address)) {

                    if (isset($cart['coupon_code'])) {
                        //log coupon
                        $data_log_coupon = array(
                            'co_code' => $cart['coupon_code'],
                            'us_id' => $this->user->us_id,
                            'or_public_id' => $transaction_id,
                            'cl_amout' => $cart['coupon'],
                        );
                        $couponLogModel->create($data_log_coupon);
                    }
                    $this->updateOderDetailAndQuantityProduct($orderModel->or_id);

                    $respon['status'] = 1;
                    $respon['url'] = $this->config['rootUrl'] . 'checkout/payment-success';
                    $respon['message'] = 'Thành Công';
                    $this->memsession->remove('CART');
                } else {
                    $respon['message'] = 'Lỗi giao dịch';
                }
                return $respon;
                break;
        }
        if ($nl_result->error_code == '00') {
            $respon['status'] = 1;
            $respon['url'] = (string)$nl_result->checkout_url;
            $respon['message'] = 'Thành Công';
        } else {
            // Log transaction fail
            $log = '===== ' . date("Y-m-d H:i:s") . " ===== \n ";
            $log .= 'UserID: ' . $this->user->us_id . " \n ";
            $log .= 'Email: ' . $this->user->us_email . " \n ";
            $log .= 'Order ID: ' . $transaction_id . " \n ";
            $log .= 'Total: ' . $cart['total_price_order'] . " \n ";
            $log .= 'Error: ' . $nl_result->error_message . " \n";
            $log .= "===== End ===== \n ";
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/ninomax/apps/data/logs/log_transaction_fail.txt', $log, FILE_APPEND);
            $respon['message'] = end($nl_result->error_message);

        }
        return $respon;
    }

    public function paymentSuccessAction()
    {
        $this->view->header_title = "Success";
    }

    public function paymentCallbackAction()
    {
        $orderModel = new OrderModel();
        $orderLogModel = new OrderLogModel();
        $couponLogModel = new CouponLogModel();
        $cart = $this->memsession->get('CART', null);
        $nlcheckout = new \NL_CheckOutV3($this->MERCHANT_ID, $this->MERCHANT_PASS, $this->RECEIVER, $this->URL_API);
        $nl_result = $nlcheckout->GetTransactionDetail($this->request->getQuery('token'));
        if ($nl_result) {
            $nl_errorcode = (string)$nl_result->error_code;
            $nl_transaction_status = (string)$nl_result->transaction_status;
            if ($nl_errorcode == '00') {
                if ($nl_transaction_status == '00') {
                    //Insert Order
                    $string = (string)$nl_result->buyer_address;
                    $address = substr($string, 0, stripos($string, "|"));
                    $temp = substr($string, stripos($string, "|"));
                    $zd_id = substr($temp, 1, strrpos($temp, "|") - 1);
                    $zp_id = substr($temp, strrpos($temp, "|") + 1);
                    $data_order = array(
                        'or_public_id' => (string)$nl_result->order_code,
                        'us_id' => $this->user->us_id,
                        'or_name' => (string)$nl_result->buyer_fullname,
                        'or_email' => (string)$nl_result->buyer_email,
                        'or_phone' => (string)$nl_result->buyer_mobile,
                        'or_address' => $address,
                        'zd_id' => $zd_id,
                        'zp_id' => $zp_id,
                        'or_fee_drive' => $cart['fee_drive'],
                        'or_discount' => $cart['coupon'],
                        'or_total' => (string)$nl_result->total_amount,
                        'or_description' => (string)$nl_result->order_description,
                        'or_payment_type' => (string)$nl_result->payment_method,
                        'os_id' => 7,
                        'or_create_date' => date("Y-m-d H:i:s")
                    );
                    $orderModel->create($data_order);
                    //save log order
                    $data_log = array(
                        'transaction_id' => (string)$nl_result->transaction_id,
                        'or_public_id' => (string)$nl_result->order_code,
                        'us_id' => $this->user->us_id,
                        'us_email' => (string)$nl_result->buyer_email,
                        'transaction_status' => (string)$nl_result->transaction_status,
                        'total' => (string)$nl_result->total_amount,
                        'error_code' => (string)$nl_result->error_code,
                        'message' => '',
                    );
                    $orderLogModel->save($data_log);
                    $this->updateOderDetailAndQuantityProduct($orderModel->or_id);
                    //log coupon

                    if (isset($cart['coupon_code'])) {
                        $data_log_coupon = array(
                            'co_code' => $cart['coupon_code'],
                            'us_id' => $this->user->us_id,
                            'or_public_id' => (string)$nl_result->order_code,
                            'cl_amout' => $cart['coupon'],
                        );
                        $couponLogModel->create($data_log_coupon);
                    }
                    $this->memsession->remove('CART');
                    return $this->response->redirect($this->config['rootUrl'] . 'checkout/payment-success');

                }
            } else {
                //save log
                $data_log = array(
                    'transaction_id' => (string)$nl_result->transaction_id,
                    'or_public_id' => (string)$nl_result->order_code,
                    'us_id' => $this->user->us_id,
                    'us_email' => (string)$nl_result->buyer_email,
                    'transaction_status' => (string)$nl_result->transaction_status,
                    'total' => (string)$nl_result->total_amount,
                    'error_code' => (string)$nl_result->error_code,
                    'message' => $nlcheckout->GetErrorMessage($nl_errorcode),
                );
                $orderLogModel->save($data_log);
                // Log transaction fail
                $log = '===== ' . date("Y-m-d H:i:s") . " ===== \n ";
                $log .= 'UserID: ' . $this->user->us_id . " \n ";
                $log .= 'Email: ' . $this->user->us_email . " \n ";
                $log .= 'Error: ' . $nlcheckout->GetErrorMessage($nl_errorcode) . " \n";
                $log .= "===== End ===== \n ";
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/ninomax/apps/data/logs/log_transaction_fail.txt', $log, FILE_APPEND);
                return $this->response->redirect($this->config['rootUrl'] . 'checkout');
            }
        }

    }

    public function paymentCancelAction()
    {
        return $this->response->redirect('checkout');
    }

    public function updateOderDetailAndQuantityProduct($or_id)
    {
        //update order detail and number product
        $cart = $this->memsession->get('CART', null);
        foreach ($cart['product'] as $product) {
            $orderDetailModel = new  OrderDetailModel();
            $productModel = new ProductModel();
            $data_order_detail = array(
                'or_id' => $or_id,
                'pr_id' => $product['pr_id'],
                'od_quantity' => $product['cart_quantity'],
                'od_size' => isset($product['size']) ? $product['size'] : '',
                'od_color' => isset($product['color']) ? $product['color'] : '',
                'od_price' => ($product['pr_price_promotion'] > 0) ? $product['pr_price_promotion'] : $product['pr_price'],
                'od_total_price' => $product['total_price'],
            );
            $orderDetailModel->create($data_order_detail);
            $updateProduct = $productModel::findFirst($product['pr_id']);
            $updateProduct->pr_quantity = $updateProduct->pr_quantity - $product['cart_quantity'];
            $updateProduct->update();
        }
    }
}

