<?php

use Phalcon\Mvc\Model;

class Orders extends Model
{
    public $id;
    public $customer_name;
    public $address;
    public $zipcode;
    public $product_id;
    public $quantity;
}
