<?php

namespace Backend\Models;

class CategoryModel extends ModelBase {

    public function initialize() {
        parent::initialize();
        $this->belongsTo("ma_id", "\Backend\Models\ManufacturerModel", "ma_id", array('alias' => 'ManufacturerModel'));
        $this->hasMany("ct_id", "\Backend\Models\ProductModel", "ct_id", array('alias' => 'ProductModel'));
    }

    public function getSource() {
        return "hq_category";
    }

    public function updateByID($data, $id) {
        $fields = array_keys($data);
        $values = array_values($data);
        $where = "ct_id='" . $id . "'";
        return $this->getWriteConnection()->update($this->getSource(), $fields, $values, $where);
    }

}
