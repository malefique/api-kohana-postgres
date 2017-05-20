<?php defined('SYSPATH') or die('No direct script access.');

class Model_Products extends ORM {
    protected $_table_name = 'products';
    protected $_primary_key = 'id';
    /*protected $_db_group = 'api';*/

    public function set_table($id){
        $this->_table_name = 'products_'.$id;
    }
}