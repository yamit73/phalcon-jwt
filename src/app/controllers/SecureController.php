<?php
use Phalcon\Mvc\Controller;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Role;
use Phalcon\Acl\Component;

class SecureController extends Controller
{
    /**
     * Function to build ACL file
     * adding roles, components
     * allow access to roles
     *
     * @return void
     */
    public function buildACLAction()
    {
        $aclFile=APP_PATH.'/security/acl.cache';
        //check if acl file already exist
        if (is_file($aclFile)==!true) {
            $acl=new Memory();
            $components=Components::find();
            $roles=Roles::find();
            $permissions=Permissions::find();
            //add roles to acl file
            foreach ($roles as $value) {
                $acl->addRole($value->name);
            }
            //add components to acl file
            foreach ($components as $val) {
                $acl->addComponent($val->name, explode(',', $val->actions));
            }
            //allow access to roles
            $acl->allow('admin', '*', '*');
            foreach ($permissions as $val) {
                $acl->allow($val->role, $val->components, $val->actions);
            }
            //put all the content to the acl file
            file_put_contents(
                $aclFile,
                serialize($acl)
            );
        } else {
            //if acl file already exist simply use it
            $acl=unserialize(
                file_get_contents($aclFile)
            );
        }
        $this->response->redirect('');
    }
}
