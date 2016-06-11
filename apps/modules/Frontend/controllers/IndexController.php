<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryModel;
use Backend\Models\ManufacturerModel;

class IndexController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $manufacturerModel = new ManufacturerModel();
        $bannerModel = new BannerModel();
        $categoryModel = new CategoryModel();
        $this->view->category = $categoryModel::find(array("ct_status = 1 and ct_parent_id=0"));
        $this->view->slider = $bannerModel::find(array("bc_id = '{$bannerModel::$SliderHome}'", "order" => "ba_sort asc"));
        $this->view->manufacturer = $manufacturerModel::find(array());
        $this->view->header_title = "Siêu Giá Sĩ";
    }


    public function searchAction()
    {
        $arrQuery = $this->request->getQuery();
        $productModel = new \Backend\Models\ProductModel();
        if (!empty($arrQuery['search'])) {
            $where = ' pr_name like "%' . $arrQuery['search'] . '%"';
            $product = $productModel::find(array($where, "order" => "pr_price asc"));
        } else {
            $product = false;
        }
        $this->view->keyword = isset($arrQuery['search']) ? $arrQuery['search'] : '';
        $this->view->product = $product;
        $this->view->header_title = "Search";
    }

}
