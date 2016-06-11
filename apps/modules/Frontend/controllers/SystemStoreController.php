<?php

namespace Frontend\Controllers;

use Backend\Models\AgencyModel;
use Backend\Models\CategoryModel;
use Backend\Models\ZoneProvinceModel;

class SystemStoreController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {
        $categoryModel = new CategoryModel();
        $bannerModel = new \Backend\Models\BannerModel();
        $provinceModel = new ZoneProvinceModel();
        $agencyModel = new AgencyModel();
        $agency = $agencyModel::find();
        $this->view->system = $provinceModel::find();
        $this->view->lastStore = $agency->getLast();
        $this->view->slider = $bannerModel::find(array(
            "bc_id=6",
            "order" => "ba_id DESC",
            "limit" => 3
        ));
        if ($this->request->isPost()) {
            $keywork = $this->request->getPost('keyword');
            if (!empty($keywork)) {
                $store = $agencyModel::find(array("ag_address like '%{$keywork}%' "));
            }
            $this->view->store = $store;
        }
        $this->view->category = $categoryModel::find(array("ct_status =1 AND ct_parent_id != 0", 'columns' => 'distinct(ct_name) as ct_name,ct_id'));
        $this->view->header_title = "System Store";
    }

    public function searchStoreAction()
    {
        if ($this->request->isPost()) {
            $province_id = $this->request->getPost('id');
            $agencyModel = new AgencyModel();
            if (!empty($province_id)) {
                $store = $agencyModel::find(array("zp_id = '{$province_id}'"));
            }
            $this->view->store = $store;
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }


}
