<?php


namespace Catrobat\AppBundle\Controller\Admin;


use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailUserMessageController extends CRUDController
{
    public function listAction(Request $request = null) {
        return $this->render('Admin/mail.html.twig');
    }

    public function sendAction(Request $request = null) {
        /**
         * @var $userManager UserManager
         * @var $user User
         */

        $userManager = $this->get('usermanager');
        $user = $userManager->findUserByUsername($_GET['Username']);

        if (!$user)
            return new Response("User does not exist");

        $mailaddress = $user->getEmail();

        $msg = wordwrap($_GET['Message'], 70);
        //mail("someone@example.com","My subject",$msg);
        $headers = "From: webmaster@catrob.at" . "\r\n";
        mail($mailaddress, "Admin Message", $msg, $headers);

        return new Response("OK");
    }
}