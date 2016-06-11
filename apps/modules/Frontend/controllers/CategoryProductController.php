<?php

namespace Frontend\Controllers;

class CategoryProductController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $arrQuery = $this->request->getQuery();
        $manufacturerModel = new \Backend\Models\ManufacturerModel();
        $productModel = new \Backend\Models\ProductModel();
        $optionModel = new \Backend\Models\ProductListOptionModel();
        $arrFilter = $this->getData($arrQuery);
        $where = $arrFilter[0];
        $order = $arrFilter[1];
        $manufacture = $arrFilter[2];
        $listCategory = $arrFilter[3];
        $product = $productModel::find(array($where, "limit" => 12, "order" => $order));
        if (isset($arrQuery['promotion'])) {
            $where .= ' and pr_price_promotion > 0  ';
            $product = $productModel::find(array($where, "limit" => 12, "order" => "pr_price asc"));
        }
        $this->setScript();
        unset($arrQuery['_url']);
        $this->view->query = $arrQuery;
        $this->view->product = $product;
        $this->view->choseManufacture = $manufacture;
        $this->view->listCategory = $listCategory;
        $this->view->listOption = $optionModel::find();
        $this->view->manufacturer = $manufacturerModel::find(array("order" => "ma_id desc"));
        $this->view->setLayout("index");
        $this->view->header_title = "Ninomaxx - N&M";
    }


    protected function setScript()
    {
        $this->assets->collection("inline")
            ->addJs('public/FrontendCore/js/loadcontent.js');
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
            if(!isset($arrQuery['munafacturer'])){
                $listCategory = $categoryProductModel::find(array(" ct_status =1 and ct_parent_id in {$IdOfGender}  ", "order" => "ct_sort asc"));
            }else{
                $listCategory = $categoryProductModel::find(array("ma_id = '{$manufacture}' AND ct_status =1 and ct_parent_id in {$IdOfGender}  ", "order" => "ct_sort asc"));
            }

        } else {
            if(!isset($arrQuery['munafacturer'])){
                $listCategory = $categoryProductModel::find(array(" ct_status =1 and ct_parent_id != 0  ", "order" => "ct_sort asc"));
            }else{
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
