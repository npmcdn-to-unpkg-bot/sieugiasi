<?php

namespace Backend\Controllers;

class IndexController extends ControllerBase {

    public function indexAction() {
        $orderModel = new \Backend\Models\OrderModel();
        $this->view->year = $orderModel::find(array("columns" => array("time" => "DISTINCT(YEAR(or_create_date))")));
        $this->view->header_title = "Backend Admin";
    }


}
