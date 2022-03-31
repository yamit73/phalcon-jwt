<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    /**
     * Product listing
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->products=Products::find();
    }

    /**
     * Add product to database with event
     *
     * @return void
     */
    public function addAction()
    {
        $product=new Products();
        if ($this->request->isPost()) {
            $setting=Settings::findFirst();
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $sanitizedData=$escaper->sanitize($postData);

            //creating an object of event manager
            $eventsManagers=$this->di->get('EventsManager');
            //firing event
            $data=$eventsManagers->fire('notification:titleOptimize', $this, $sanitizedData);
            $product->assign(
                $data,
                [
                    'name',
                    'description',
                    'tags',
                    'price',
                    'stock'
                ]
            );
            if ($product->save()) {
                $this->view->message = "Product added";
            } else {
                $this->view->message = "Product not added: <br>" . implode("<br>", $product->getMessages());
            }
        }
    }
}
