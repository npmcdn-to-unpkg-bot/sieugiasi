<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryCollectionModel;
use Backend\Models\CategoryModel;
use Backend\Models\ManufacturerModel;
use Backend\Models\ProductModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

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
        $categoryCollectionModel = new CategoryCollectionModel();
        $date = date("Y-m-d");
        $this->view->collection = $categoryCollectionModel::findFirst(array("col_parent_id!=0 ", "order" => "RAND()"));
        $this->view->productSaleRandom = $productModel::find(array("pr_price_promotion !=0 and pr_status=1 and pr_date_sale_from <= '{$date}' and '{$date}'<=pr_date_sale_to", "order" => "RAND()", "limit" => 10));
        $this->view->countTodaySale = count($productModel::find(array("pr_price_promotion !=0 and pr_status=1 and pr_date_sale_from <= '{$date}' and '{$date}'<=pr_date_sale_to")));
        $this->view->category = $categoryModel::find(array("ct_status = 1 and ct_parent_id=0"));
        $this->view->slider = $bannerModel::find(array("bc_id = '{$bannerModel::$SliderHome}'", "order" => "ba_sort asc"));
        $this->view->manufacturer = $manufacturerModel::find(array());
        $this->view->header_title = "Siêu Giá Sĩ";
    }


    public function searchAction()
    {
        $arrQuery = $this->request->getQuery();
        $productModel = new \Backend\Models\ProductModel();
        $currentPage = $this->request->getQuery('page', 'int');
        if (!empty($arrQuery['key'])) {
            $where = ' pr_name like "%' . $arrQuery['key'] . '%"';
            $product = $productModel::find(array($where, "order" => "pr_price asc"));
        } else {
            $product = false;
        }
        $paginator = new PaginatorModel(
            array(
                "data" => $product,
                "limit" => 40,
                "page" => $currentPage
            )
        );
        $this->assets->addCss('public/FrontendCore/css/category-product.css', true);
        $this->view->keyword = isset($arrQuery['key']) ? $arrQuery['key'] : '';
        $this->view->products = $paginator->getPaginate();
        $this->view->header_title = "Search";
    }

}
