<?php

namespace Frontend\Controllers;

use Backend\Models\CategoryModel;
use Backend\Models\ProductModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class TodaySaleController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $productModel = new ProductModel();
        $date = date("Y-m-d");
        $this->view->categorySale = $productModel::find(array("pr_date_sale_from <= '{$date}' and '{$date}'<= pr_date_sale_to and pr_price_promotion !=0 and pr_status=1 and pr_quantity !=0", "columns", "Distinct(ct_id)"));
        $this->view->todaySale = $productModel::find("pr_date_sale_from <= '{$date}' and '{$date}'<= pr_date_sale_to and pr_price_promotion !=0 and pr_status=1 and pr_quantity !=0");
        $this->view->header_title = "Today Sale";
        $this->setScript();
    }

    public function searchAction()
    {
        if ($this->request->isPost()) {
            $cate = $this->request->getPost("cate");
            $value = $this->request->getPost("value");
            $productModel = new ProductModel();

            if ($cate == 'cate') {
                $date = date("Y-m-d");
                $this->view->todaySale = $productModel::find("pr_date_sale_from <= '{$date}' and '{$date}'<= pr_date_sale_to and pr_price_promotion !=0 and pr_status=1 and pr_quantity !=0 and ct_id='{$value}'");
            } else {
                $date = date("Y-m-d", strtotime('+1 day', time()));
                $this->view->todaySale = $productModel::find("pr_date_sale_from <= '{$date}' and '{$date}'<= pr_date_sale_to and pr_price_promotion !=0 and pr_status=1 and pr_quantity !=0 ");
            }
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    protected function setScript()
    {
//        $this->assets->addCss('https://fonts.googleapis.com/css?family=Open+Sans:400,400italic&subset=latin,vietnamese', false);
//        $this->assets->addCss('public/FrontendCore/css/category-product.css', true);
//        $this->assets->collection("inline")
//            ->addJs('public/FrontendCore/js/filter.js');
    }


}
