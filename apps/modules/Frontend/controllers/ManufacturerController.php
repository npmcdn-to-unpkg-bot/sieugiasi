<?php

namespace Frontend\Controllers;

class ManufacturerController extends ControllerBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction($id)
    {
        $manufactureModel = new \Backend\Models\ManufacturerModel;
        $manufacture=$manufactureModel::findFirst($id);
        if(!$manufacture){
            return $this->response->redirect("");
        }
        $this->view->manufacture=$manufacture;
        $this->view->header_title = "Siêu Giá Sĩ";
    }

    

}
