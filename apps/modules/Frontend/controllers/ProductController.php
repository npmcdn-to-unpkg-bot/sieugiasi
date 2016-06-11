<?php

namespace Frontend\Controllers;

use Backend\Models\ProductImageModel;
use Backend\Models\ProductModel;

class ProductController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function showAction($seo)
    {
        $productModel = new ProductModel();
        $product = $productModel::findFirst(array("pr_seo_link = '{$seo}'"));
        if (!$product) {
            return $this->response->redirect('category-product');
        }
        $this->view->ortherProduct = $productModel::find(array("ct_id='{$product->ct_id}' AND pr_status = 1", "order" => "pr_create_date desc", "limit" => 4));
        $this->view->product = $product;
        $this->view->header_title = $product->pr_name;
    }

    public function sizeGuideAction($seo)
    {
        $productModel = new ProductModel();
        $product = $productModel::findFirst(array("pr_seo_link = '{$seo}'"));
        if (!$product) {
            return $this->response->redirect('category-product');
        }
        $this->view->product = $product;
        $this->view->header_title = $product->pr_name;
    }

    public function changeColorGalleryAction()
    {
        if ($this->request->isPost()) {
            $galleryModel = new ProductImageModel();
            $idProduct = $this->request->getPost("id_product");
            $idColor = $this->request->getPost("id_color");
            $this->view->gallery = $galleryModel::find(array("pr_id = '{$idProduct}' AND plo_id = '{$idColor}' "));
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

}
