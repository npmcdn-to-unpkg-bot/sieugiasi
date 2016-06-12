<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryModel;
use Backend\Models\ManufacturerModel;
use Backend\Models\ProductModel;

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
        $productModel = new ProductModel();
        $date = date("Y-m-d");
        $this->setScript();
        $this->view->productSaleRandom = $productModel::find(array("pr_price_promotion !=0 and pr_status=1 and pr_date_sale_from <= '{$date}' and '{$date}'<=pr_date_sale_to", "order" => "RAND()", "limit" => 10));
        $this->view->countTodaySale= count($productModel::find(array("pr_price_promotion !=0 and pr_status=1 and pr_date_sale_from <= '{$date}' and '{$date}'<=pr_date_sale_to")));
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
    private function setScript()
    {
        $this->assets->collection("inline")
            ->addJs('public/FrontendCore/plugins/jquery.countdown-2.1.0/jquery.countdown.min.js');
    }

}
