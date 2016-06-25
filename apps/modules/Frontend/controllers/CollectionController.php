<?php

namespace Frontend\Controllers;

use Backend\Models\BannerModel;
use Backend\Models\CategoryCollectionModel;
use Backend\Models\CollectionModel;
use Backend\Models\OrderDetailModel;
use Backend\Models\ProductImageModel;
use Backend\Models\ProductListOptionModel;
use Backend\Models\ProductModel;
use Backend\Models\ProductPriceModel;
use Phalcon\Paginator\Adapter\Model as PaginatorModel;

class CollectionController extends ControllerBase
{
    protected $buy_roduct = array();

    public function initialize()
    {
        parent::initialize();
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
        $bannerModel = new BannerModel();
        $categoryCollectionModel = new CategoryCollectionModel();
        $currentPage = $this->request->getQuery('page', 'int');
        $cate = $this->request->getQuery('cate');
        $collectionModel = new CollectionModel();
        if (isset($cate)) {
            $collection = $categoryCollectionModel::find(array("col_status=1 and col_parent_id!=0 and col_parent_id='{$cate}'"));
            $this->view->cateQuery = $cate;
        } else {
            $collection = $categoryCollectionModel::find(array("col_status=1 and col_parent_id!=0"));
        }
        $paginator = new PaginatorModel(
            array(
                "data" => $collection,
                "limit" => 40,
                "page" => $currentPage
            )
        );
        $page = $paginator->getPaginate();
        $this->view->category_data = $categoryCollectionModel::find("col_status=1 and col_parent_id=0");
        $this->view->collection = $page;
        $this->view->slider = $bannerModel::find(array("bc_id = '{$bannerModel::$SliderCollection}'", "order" => "ba_sort asc"));
        $this->view->header_title = "Collection";
    }

    public function showAction($seo)
    {
        $categoryCollectionModel = new CategoryCollectionModel();
        $collectionModel = new CollectionModel();
        $collection = $categoryCollectionModel::findFirst(array("col_seo_link ='{$seo}'"));
        if (count($collection) == 0) {
            return $this->response->redirect("collection");
        }
        $this->view->ortherCollection = $categoryCollectionModel::find(array("col_status=1 and col_id!='{$collection->col_id}' and col_parent_id='{$collection->col_parent_id}'"));
        $this->view->collection = $collection;
        $this->view->product = $collectionModel::find(array("col_id = '{$collection->col_id}'"));
        $this->view->header_title = $collection->col_name;
    }


    protected function setScript()
    {
        $this->assets->addCss('public/FrontendCore/css/product.css', true);
    }
}
