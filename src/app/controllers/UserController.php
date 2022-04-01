<?php

use Phalcon\Mvc\Controller;
class UserController extends Controller
{
    public function indexAction()
    {
        
    }

    /**
     * Add role to database
     *
     * @return void
     */
    public function addAction()
    {
        $this->view->roles=Roles::find();
        if ($this->request->isPost()) {
            $user=new Users();
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $data=$escaper->sanitize($postData);
            /**
             * add user to database
             */
            $user->assign($data, ['name', 'email', 'role']);
            $userData['name']=$data['name'];
            $userData['role']=Roles::findFirst($data['role'])->name;
            if ($user->save()) {
                $this->view->message = "Registered!";
                //creating an object of event manager
                $eventsManagers=$this->di->get('EventsManager');
                //firing event to create a token
                $this->view->token=$eventsManagers->fire('notification:createToken', $this, $userData);
            } else {
                $this->view->message = "Not registered: <br>" . implode("<br>", $user->getMessages());
            }
        }
    }
}
