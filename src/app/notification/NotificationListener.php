<?php
namespace App\Notification;

use DateTimeImmutable;
use Exception;
use OrderController;
use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use ProductController;
use Settings;

use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

class NotificationListener extends Injectable
{
    /**
     * event handler to optimize the title based on settings
     *  Adding default value to price, stock if they are empty
     * @param Event $event
     * @param ProductController $product
     * @param [type] $data
     * @return void
     */
    public function titleOptimize(Event $event, ProductController $product, $data)
    {
        $setting=Settings::findFirst();

        if (isset($setting->id)) {
            if ($setting->title_optimization=='on' && $data['tags']!='') {
                $data['name'].='+'.$data['tags'];
            }
            if ($data['price']=='') {
                $data['price']=$setting->price;
            }
            if ($data['stock']=='') {
                $data['stock']=$setting->stock;
            }
        }
        return $data;
    }

    /**
     * event handler to add zipcode if it is empty
     *
     * @param Event $event
     * @param OrderController $order
     * @param [type] $data
     * @return void
     */
    public function defaultOrderData(Event $event, OrderController $order, $data)
    {
        $setting=Settings::findFirst();
        if ($data['zipcode']=='' && isset($setting->zipcode)) {
            $data['zipcode']=$setting->zipcode;
        }
        return $data;
    }

    public function beforeHandleRequest(Event $event, \Phalcon\Mvc\Application $application)
    {
        $bearer=$this->request->getQuery('bearer');
        if ($bearer) {
            try {
                $parser=new Parser();
                $tokenObject=$parser->parse($bearer);
                $now=new DateTimeImmutable();
                $expires=$now->getTimestamp();
                $validator=new Validator($tokenObject, 100);
                $validator->ValidateExpiration($expires);
                // die;
                $claims=$tokenObject->getClaims()->getPayload();
                $role=$claims['sub'];

                $controller=$this->router->getControllerName() ?? 'index';
                $action=$this->router->getActionName() ?? 'index';
                $aclFile=APP_PATH.'/security/acl.cache';
                if (is_file($aclFile)==true) {
                    $acl=unserialize(
                        file_get_contents($aclFile)
                    );
                    if ($acl->isAllowed($role, $controller, $action)!==true) {
                        die('<h1 style="color:red;">Access denied!</h1>');
                    }
                } else {
                    $this->response->redirect('secure/buildACL');
                }
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }
        } else {
            die('<h1 style="color:red;">Bearer not found!!!!!</h1>');
        }
    }
    /**
     * Function to create JWT token
     *
     * @return void
     */
    public function createToken(Event $event, $user, $role)
    {
        $signer  = new Hmac();

        // Builder object
        $builder = new Builder($signer);

        $now        = new DateTimeImmutable();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('-1 minute')->getTimestamp();
        $expires    = $now->modify('+1 day')->getTimestamp();
        $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';

        // Setup
        $builder
            ->setAudience('https://target.phalcon.io')  // aud
            ->setContentType('application/json')        // cty - header
            ->setExpirationTime($expires)               // exp 
            ->setId('abcd123456789')                    // JTI id 
            ->setIssuedAt($issued)                      // iat 
            ->setIssuer('https://phalcon.io')           // iss 
            ->setNotBefore($notBefore)                  // nbf
            ->setSubject($role)   // sub
            ->setPassphrase($passphrase)                // password 
        ;

        // Phalcon\Security\JWT\Token\Token object
        $tokenObject = $builder->getToken();

        // The token
        return $tokenObject->getToken();
    }
}
