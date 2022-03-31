<?php

use Phalcon\Mvc\Controller;

class ComponentController extends Controller
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
        if ($this->request->isPost()) {
            $component=new Components();
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $data=$escaper->sanitize($postData);
            $component->assign($data, ['name', 'actions']);
            if ($component->save()) {
                $this->view->message = "Component added";
            } else {
                $this->view->message = "Component not added: <br>" . implode("<br>", $component->getMessages());
            }
        }
    }
}
