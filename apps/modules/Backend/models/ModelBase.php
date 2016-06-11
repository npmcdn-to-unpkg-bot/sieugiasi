<?php

namespace Backend\Models;

use Phalcon\Mvc\Model;
use Phalcon\Validation;
use Phalcon\Mvc\Model\Behavior\Timestampable;

//use Phalcon\Mvc\Model\Behavior\SoftDelete;

class ModelBase extends Model {

    public $scenario;
    public $metaData;

//    const DELETED = 'D';
//    const NOT_DELETED = 'N';

    public function initialize() {
        $this->addBehavior(
                new Timestampable(
                array(
            'beforeCreate' => array(
                'field' => 'created_at',
                'format' => 'Y-m-d H:i:s'
            ),
            'beforeUpdate' => array(
                'field' => 'update_at',
                'format' => 'Y-m-d H:i:s'
            )
                )
                )
        );
//        $this->addBehavior(
//                new SoftDelete(
//                    array(
//                        'field' => 'deleted_at',
//                        'value' => self::DELETED
//                    )
//                )
//        );
    }


    public function setCreateDate() {
        if (!empty($this->created_at)) {
            return date("d-m-Y H:i:s", strtotime($this->created_at));
        } else {
            return '';
        }
    }

    public function setUpdateDate() {
        if (!empty($this->update_at)) {
            return date("d-m-Y H:i:s", strtotime($this->update_at));
        } else {
            return '';
        }
    }

}
