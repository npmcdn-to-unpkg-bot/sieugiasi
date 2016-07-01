<?php

namespace Frontend\Controllers;

use Backend\Models\CategoryModel;
use Backend\Models\ProductModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class CategoryProductController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function showAction($seo_link='')
    {
        $categoryModel = new CategoryModel();
        $productModel = new ProductModel();
        $optionModel = new \Backend\Models\ProductListOptionModel();
        $optionDetailModel = new \Backend\Models\ProductOptionDetailModel();
        $currentPage = $this->request->getQuery('page', 'int');
        $category = $categoryModel::findFirst(array(" ct_seo_link = '{$seo_link}'"));
        if (!$category||!isset($seo_link)) {
            return $this->response->redirect("");
        }
        $where = 'pr_status=1 ';
        $order = 'pr_create_date desc';
        $arrQuery = $this->request->getQuery();
        if ($category->ct_parent_id == 0) {
            $listCateChild = $category->getCategoryChild();
            $string = array();
            foreach ($listCateChild as $val) {
                $string[] = $val->ct_id;
            }
            $string = implode(",", $string);
            $where = "ct_id in (" . $string . ")";

        } else {
            $where = "ct_id=" . $category->ct_id;
        }
        $date = date("Y-m-d");
        if (isset($arrQuery['promotion'])) {

            $where .= " and pr_price_promotion > 0 and pr_date_sale_from <= '{$date}' and '{$date}'<= pr_date_sale_to ";
        }
        if (isset($arrQuery['price'])) {
            $order = 'pr_price ' . $arrQuery['price'];
        }
        if (isset($arrQuery['size']) && $arrQuery['size'] != '-1') {
            $productOption = $optionDetailModel::find(array("plo_id = '{$arrQuery['size']}'"));
            $pr_id = '';
            foreach ($productOption as $pr) {
                $pr_id .= $pr->pr_id . ',';
            }
            $pr_id = rtrim($pr_id, ",");
            $where .= ' and pr_id in (' . $pr_id . ')  ';
        }
        if (isset($arrQuery['color']) && $arrQuery['color'] != '-1') {
            $productOption = $optionDetailModel::find(array("plo_id = '{$arrQuery['color']}'"));
            $pr_id = '';
            foreach ($productOption as $pr) {
                $pr_id .= $pr->pr_id . ',';
            }
            $pr_id = rtrim($pr_id, ",");
            $where .= ' and pr_id in (' . $pr_id . ')  ';
        }
        //filter price min - max
        $minPrice = $this->request->getQuery('minPrice');
        $maxPrice = $this->request->getQuery('maxPrice');
        if (isset($minPrice) && isset($maxPrice) && is_numeric($minPrice) && is_numeric($maxPrice)) {
            $where .= " and ".$minPrice." <= pr_price and pr_price <= ".$maxPrice;
        }

        $product = $productModel::find(array($where, "order" => $order));
        $paginator = new PaginatorModel(
            array(
                "data" => $product,
                "limit" => 40,
                "page" => $currentPage
            )
        );
        $this->view->productSaleRandom = $productModel::find(array("pr_price_promotion !=0 and pr_status=1 and pr_date_sale_from <= '{$date}' and '{$date}'<=pr_date_sale_to", "order" => "RAND()", "limit" => 4));
        $this->view->listOption = $optionModel::find();
        $this->view->products = $paginator->getPaginate();
        $this->view->query = $this->formatStringQuery($arrQuery);
        $this->view->category = $category;
        $this->view->header_title = $category->ct_name;
        $this->setScript();
    }


    protected function setScript()
    {
        $this->assets->addCss('https://fonts.googleapis.com/css?family=Open+Sans:400,400italic&subset=latin,vietnamese', false);
        $this->assets->addCss('public/FrontendCore/css/ae-mobile.css', true);
        $this->assets->addCss('public/FrontendCore/css/category-product.css', true);
        $this->assets->collection("inline")
            ->addJs('public/FrontendCore/js/filter.js');
    }

    public function formatStringQuery($query)
    {
        unset($query['_url']);
        $string = '';
        if (!empty($query)) {
            foreach ($query as $key => $val) {
                $string .= "'" . $key . "':'" . $val . "',";
            }
            $string = rtrim($string, ",");
        }
        return $string;
    }

    public function filterAction()
    {
        if ($this->request->isPost()) {
            $arrQuery = $this->request->getPost('data');
            $pagination = $this->request->getPost('page');
            $productModel = new \Backend\Models\ProductModel();
            $arrFilter = $this->getData($arrQuery);
            $where = $arrFilter[0];
            $order = $arrFilter[1];
            if (isset($pagination)) {
                $product = $productModel::find(array($where, "limit" => 12, "offset" => $pagination * 12, "order" => $order));
                if (isset($arrQuery['promotion'])) {
                    $where .= ' and pr_price_promotion > 0  ';
                    $product = $productModel::find(array($where, "limit" => 12, "offset" => $pagination * 12, "order" => "pr_price asc"));
                }
            } else {
                $product = $productModel::find(array($where, "limit" => 12, "order" => $order));
                if (isset($arrQuery['promotion'])) {
                    $where .= ' and pr_price_promotion > 0  ';
                    $product = $productModel::find(array($where, "limit" => 12, "order" => $order));
                }
            }
            $this->view->product = $product;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    public function getCategoryProductAction()
    {
        if ($this->request->isPost()) {
            $arrQuery = $this->request->getPost('data');
            $categoryProductModel = new \Backend\Models\CategoryModel();
            $manufacturerModel = new \Backend\Models\ManufacturerModel();
            //default
            if (!isset($arrQuery['munafacturer'])) {
                $manufacture = $manufacturerModel::$Ninomaxx;
            } else {
                $manufacture = $arrQuery['munafacturer'];
            }
            if (isset($arrQuery['gender'])) {
                $listIdOfGender = $categoryProductModel::find(array("ct_seo_link = '{$arrQuery['gender']}'"));
                $IdOfGender = '';
                foreach ($listIdOfGender as $list) {
                    $IdOfGender .= $list->ct_id . ",";
                }
                $IdOfGender = '(' . rtrim($IdOfGender, ",") . ')';
                $listCategory = $categoryProductModel::find(array("ma_id = '{$manufacture}' AND ct_status =1 and ct_parent_id in {$IdOfGender}  ", "order" => "ct_sort asc"));
            } else {
                $listCategory = $categoryProductModel::find(array("ma_id = '{$manufacture}' AND ct_status =1 and ct_parent_id != 0  ", "order" => "ct_sort asc"));
            }
            $this->view->listCategory = $listCategory;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    public function getData($arrQuery, $gender = false)
    {
        $categoryProductModel = new \Backend\Models\CategoryModel();
        $manufacturerModel = new \Backend\Models\ManufacturerModel();
        $optionDetailModel = new \Backend\Models\ProductOptionDetailModel();
        //default
        if (!isset($arrQuery['munafacturer'])) {
            $manufacture = '';
        } else {
            $manufacture = $arrQuery['munafacturer'];
        }
        if (isset($arrQuery['gender']) || $gender) {
            $listIdOfGender = $categoryProductModel::find(array("ct_seo_link = '{$arrQuery['gender']}'"));
            $IdOfGender = '';
            foreach ($listIdOfGender as $list) {
                $IdOfGender .= $list->ct_id . ",";
            }
            $IdOfGender = '(' . rtrim($IdOfGender, ",") . ')';
            if (!isset($arrQuery['munafacturer'])) {
                $listCategory = $categoryProductModel::find(array(" ct_status =1 and ct_parent_id in {$IdOfGender}  ", "order" => "ct_sort asc"));
            } else {
                $listCategory = $categoryProductModel::find(array("ma_id = '{$manufacture}' AND ct_status =1 and ct_parent_id in {$IdOfGender}  ", "order" => "ct_sort asc"));
            }

        } else {
            if (!isset($arrQuery['munafacturer'])) {
                $listCategory = $categoryProductModel::find(array(" ct_status =1 and ct_parent_id != 0  ", "order" => "ct_sort asc"));
            } else {
                $listCategory = $categoryProductModel::find(array("ma_id = '{$manufacture}' AND ct_status =1 and ct_parent_id != 0  ", "order" => "ct_sort asc"));
            }

        }
        unset($arrQuery['munafacturer'], $arrQuery['_url']);
        $listID = '';
        foreach ($listCategory as $list) {
            $listID .= $list->ct_id . ",";
        }
        $listID = '(' . rtrim($listID, ",") . ')';
        // filter
        $where = 'ct_id in ' . $listID . ' and ';
        $order = 'pr_create_date desc';
        if (isset($arrQuery['category']) && $arrQuery['category'] != '-1') {
            $where = ' ct_id = ' . $arrQuery['category'] . " and ";
        }
        if (isset($arrQuery['price'])) {
            $order = 'pr_price ' . $arrQuery['price'];
        }
        if (isset($arrQuery['size']) && $arrQuery['size'] != '-1') {
            $productOption = $optionDetailModel::find(array("plo_id = '{$arrQuery['size']}'"));
            $pr_id = '';
            foreach ($productOption as $pr) {
                $pr_id .= $pr->pr_id . ',';
            }
            $pr_id = rtrim($pr_id, ",");
            $where .= ' pr_id in (' . $pr_id . ') and ';
        }
        if (isset($arrQuery['color']) && $arrQuery['color'] != '-1') {
            $productOption = $optionDetailModel::find(array("plo_id = '{$arrQuery['color']}'"));
            $pr_id = '';
            foreach ($productOption as $pr) {
                $pr_id .= $pr->pr_id . ',';
            }
            $pr_id = rtrim($pr_id, ",");
            $where .= ' pr_id in (' . $pr_id . ') and ';
        }
        $where = rtrim($where, "and ");
        return array($where, $order, $manufacture, $listCategory);

    }
}
