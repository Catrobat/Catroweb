<?php
namespace Catrobat\AppBundle\Controller\Resetting;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ResettingController extends Controller
{
    /**
     * @Route("/reset/invalid", name="reset_invalid")
     */
    public function handleInvalidUsernameOrEmail() {
        return $this->render('@FOSUser/Resetting/request.html.twig', array(
                'invalid_username' => ""
            ));
    }


    /**
     * @Route("/reset/already_requested", name="reset_already_requested")
     */
    public function handlePasswordAlreadyRequested() {
        return $this->render('@FOSUser/Resetting/check_email.html.twig', array(
            'already_reset' => ""
        ));
    }
}
