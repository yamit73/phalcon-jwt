<?php

use Phalcon\Mvc\Controller;

class AccessController extends Controller
{
    public function indexAction()
    {
        
    }

    /**
     * Add role to database
     *
     * @return void
     */
    public function permissionAction()
    {
        $components=Components::find();
        $this->view->roles=Roles::find();
        $controllers=array();
        $actions=array();
        foreach ($components as $val) {
            array_push($controllers, $val->name);
            $actions[$val->name]=explode(',', $val->actions);
        }
        
        $this->view->controllers=$controllers;
        $this->view->actions=$actions;
        
        if ($this->request->isPost()) {
            $component=$this->request->getPost('components');
            $this->view->selComponent=$component;
            $role=$this->request->getPost('role');
            $this->view->selrole=$role;
            $action=$this->request->getPost('actions');

            $permission= new Permissions();
            if ($action !='') {
                $permission->assign(
                    $this->request->getPost(),
                    [
                        'role',
                        'components',
                        'actions'
                    ]
                );
                $permission->save();
            }
        }
    }
}
