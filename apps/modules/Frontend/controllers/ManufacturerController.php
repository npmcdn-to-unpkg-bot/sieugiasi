<?php

namespace Frontend\Controllers;

class ManufacturerController extends ControllerBase {

    public function initialize() {
        parent::initialize();
    }

    public function ninomaxxAction() {
        $bannerModel = new \Backend\Models\BannerModel();
        $productModel = new \Backend\Models\ProductModel();
        $categoryProductModel = new \Backend\Models\CategoryModel();
        $manufactureModel = new \Backend\Models\ManufacturerModel;
        $newModel = new \Backend\Models\NewsModel();

        // 1. Lấy category là Nữ
        // 2. Lấy parent category của Nữ 
        // 3. Lấy product của parent

        $womanCategory = $categoryProductModel::findFirst(array("ct_name = 'Nữ' AND ma_id='{$manufactureModel::$Ninomaxx}'"));
        $parentWomanCategory = $categoryProductModel::find(array("ct_parent_id = '{$womanCategory->ct_id}'", "columns" => "ct_id as id"));
        $list_ct_id = array();
        foreach ($parentWomanCategory as $list) {
            $list_ct_id[] = $list->id;
        }
        $list_ct_id = implode(",", $list_ct_id);
        $womanProduct = $productModel::find(array("ct_id in ({$list_ct_id}) and pr_status=1", "order" => "pr_create_date asc","limit"=>13));

        // Tương tự với Nam

        $manCategory = $categoryProductModel::findFirst(array("ct_name = 'Nam' AND ma_id='{$manufactureModel::$Ninomaxx}'"));
        $parentManCategory = $categoryProductModel::find(array("ct_parent_id = '{$manCategory->ct_id}'", "columns" => "ct_id as id"));
        $list_ct_man_id = array();
        foreach ($parentManCategory as $list) {
            $list_ct_man_id[] = $list->id;
        }
        $list_ct_man_id = implode(",", $list_ct_man_id);
        $manProduct = $productModel::find(array("ct_id in ({$list_ct_man_id}) and pr_status=1", "order" => "pr_create_date asc","limit"=>13));

        //News
        $news = $newModel::find(array("order" => "created_at desc", "limit" => 3));
        $this->view->womanProduct = $womanProduct;
        $this->view->manProduct = $manProduct;
        $this->view->news = $news;
        $this->view->sliders = $bannerModel::find(array("bc_id='{$bannerModel::$SliderNinomaxxPage}'", "order" => "ba_sort asc"));
        $this->view->setLayout("index");
        $this->view->header_title = "Ninomaxx";
    }

    public function nAndMAction() {
        $bannerModel = new \Backend\Models\BannerModel();
        $this->view->featureBanner = $bannerModel::find(array("bc_id='{$bannerModel::$FeatureNMPage}'", "order" => "ba_sort asc", "limit" => 3));
        $this->view->setLayout("index");
        $this->view->header_title = "N&M";
        $this->view->page_NAndM = true;
    }

}
