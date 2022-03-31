<?php

use Phalcon\Mvc\Controller;

class OrderController extends Controller
{
    /**
     * Orders listing
     *
     * @return void
     */
    public function indexAction()
    {
        $orders=$this->modelsManager
                     ->createQuery(
                         'SELECT Products.name as name, Orders.* FROM Orders Join Products on Orders.product_id=Products.id'
                     );
        $result=$orders->execute();
        $this->view->orders=$result;
    }

    /**
     * Add order to database with event
     *
     * @return void
     */
    public function addAction()
    {
        $products=Products::find();
        $this->view->products=$products;

        $order=new Orders();
        if ($this->request->isPost()) {
            //code to sanitize data using escaper
            $postData = $this->request->getPost();
            $escaper=new \App\Components\MyEscaper();
            $sanitizedData=$escaper->sanitize($postData);

            //creating an object of event manager
            $eventsManagers=$this->di->get('EventsManager');
            //firing event
            $data=$eventsManagers->fire('notification:defaultOrderData', $this, $sanitizedData);
            //die(print_r($data));
            $order->assign(
                $data,
                [
                    'customer_name',
                    'address',
                    'zipcode',
                    'product_id',
                    'quantity'
                ]
            );
            if ($order->save()) {
                $this->view->message = "Order placed";
            } else {
                $this->view->message = "Order not placed: <br>" . implode("<br>", $order->getMessages());
            }
        }
        
    }
}
