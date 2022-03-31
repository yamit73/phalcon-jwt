<?php

use Phalcon\Mvc\Model;

class Permissions extends Model
{
    public $id;
    public $role;
    public $components;
    public $actions;
}
