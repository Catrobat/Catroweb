<?php
namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

class GoogleCloudMessagingController extends CRUDController
{
    public function listAction(Request $request = null)
    {
        return $this->render(':Admin:gcm.html.twig');
    }

    public function sendAction(Request $request = null)
    {
        if(!isset($_GET['a']) || !isset($_GET['m']))
            return new Response("Error: Invalid parameters");

        $apikey = htmlentities($_GET['a']);
        $message = htmlentities($_GET['m']);

        $url = 'https://gcm-http.googleapis.com/gcm/send';
        $data = '{"to" : "/topics/catroweb", "data" : {"message" : "'.$message.'"}}';

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json\r\nAuthorization:key=".$apikey."\r\n",
                'method'  => 'POST',
                'content' => $data,
            ),
        );

        $context  = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if($result === FALSE) {
            return new Response("Error: Invalid response or API key");
        }

        if(strpos($result, "\"message_id\":") > 0) {
            return new Response("OK");
        }

        return new Response($result);
    }

}