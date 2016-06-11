<?php

namespace Frontend\Controllers;


use Backend\Models\OrderModel;
use Backend\Models\ProductModel;

class MyPageController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        if (empty($this->user)) {
            return $this->response->redirect('');
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
        $orderModel = new OrderModel();
        $this->view->order=$orderModel::find(array("us_id = '{$this->user->us_id}'","order"=>"or_create_date desc"));
        $this->view->header_title = "My Page";
    }

    public function contactMessage($request)
    {

        $respon['status'] = 0;
        if (empty($request['data'])) {
            $respon['message'] = 'Không có dữ liệu';
            return $respon;
        }

        $data = json_decode($request['data'], true);
        $userModel = new ContactMessageModel();
        //Validation
        $validation = $userModel->validationMessage($data);
        if ($validation) {
            $respon['message'] = $validation;
            return $respon;
        }
        //End Validation

        if ($userModel->create($data)) {
            $respon['status'] = 1;
            $respon['message'] = 'Message của bạn đã được gửi tới Admin.Xin chân thành cám ơn.';
        } else {
            $respon['message'] = 'Không thể Thêm';
        }
        return $respon;
    }

}
