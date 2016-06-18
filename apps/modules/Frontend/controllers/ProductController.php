<?php

namespace Frontend\Controllers;

use Backend\Models\OrderDetailModel;
use Backend\Models\ProductImageModel;
use Backend\Models\ProductListOptionModel;
use Backend\Models\ProductModel;
use Backend\Models\ProductPriceModel;

class ProductController extends ControllerBase
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

    public function showAction($seo)
    {
        $this->memsession->remove('BUYPRODUCT');
        $productModel = new ProductModel();
        $product = $productModel::findFirst(array("pr_seo_link = '{$seo}'"));
        if (!$product) {
            return $this->response->redirect('');
        }

        $this->view->ortherProduct = $productModel->getProductTopSale(6);
        $this->view->product = $product;
        $this->view->header_title = $product->pr_name;
        $this->setScript();
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

    public function changeColorAction()
    {
        if ($this->request->isPost()) {
            $idProduct = $this->request->getPost('id_product');
            $idColor = $this->request->getPost('id_color');
            $productModel = new ProductModel();
            $cart = $this->memsession->get('BUYPRODUCT', null);
            $this->view->product = $productModel::findFirst($idProduct);
            $this->view->buyProduct = $cart;
            $this->view->idColor = $idColor;
            $this->view->pick("product/buyproduct");
            $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        }
    }

    public function buyProductAction()
    {
        if ($this->request->isPost()) {
            $idProduct = $this->request->getPost('id_product');
            $idSize = $this->request->getPost('id_size');
            $idColor = $this->request->getPost('id_color');
            $quantity = $this->request->getPost('quantity');
            $cart = $this->memsession->get('BUYPRODUCT', null);
            $productModel = new ProductModel();
            $productListOptionModel = new ProductListOptionModel();
            $productPriceModel = new ProductPriceModel();
            $product = $productModel::findFirst($idProduct);
            if ($product) {
                $cart[$idProduct]['color'][$idColor][$idSize] = $quantity;
                $total_quantity = 0;
                foreach ($cart[$idProduct]['color'] as $color) {
                    foreach ($color as $quantity) {
                        $total_quantity += $quantity;
                    }
                }
                $cart[$idProduct]['total_quantity'] = $total_quantity;

                //get price
                $date = date("Y-m-d");
                if ($product->pr_price_promotion != 0 && $product->pr_date_sale_from <= $date && $date <= $product->pr_date_sale_to) {
                    $cart[$idProduct]['price'] = $product->pr_price_promotion;
                } else {
                    $prices = $productPriceModel::find(array("pr_id='{$idProduct}'", "order" => "hqr_id desc"));
                    foreach ($prices as $price) {
                        if (empty($price->hqr_quantity_to) && $cart[$idProduct]['total_quantity'] >= $price->hqr_quantity_from) {
                            $cart[$idProduct]['price'] = $price->hqr_price;
                            break;
                        } else if ($cart[$idProduct]['total_quantity'] >= $price->hqr_quantity_from && $cart[$idProduct]['total_quantity'] < $price->hqr_quantity_to) {
                            $cart[$idProduct]['price'] = $price->hqr_price;
                        }
                    }
                }
                $cart[$idProduct]['total_price'] = $cart[$idProduct]['total_quantity'] * $cart[$idProduct]['price'];

                $this->memsession->set('BUYPRODUCT', $cart);
                $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
                $this->view->product = $product;
                $this->view->buyProduct = $cart;
                $this->view->idColor = $idColor;
            }

        }
    }

    protected function setScript()
    {
        $this->assets->addCss('public/FrontendCore/css/product.css', true);
    }
}
