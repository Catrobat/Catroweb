<?php
namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Event\GetResponseUserEvent;
use Symfony\Component\HttpFoundation\Request;

class RegisterController extends Controller
{

//    public function registerAction(Request $request)
//    {
//        /** @var $formFactory FactoryInterface */
//        $formFactory = $this->get('fos_user.registration.form.factory');
//        /** @var $userManager UserManagerInterface */
//        $userManager = $this->get('fos_user.user_manager');
//        /** @var $dispatcher EventDispatcherInterface */
//        $dispatcher = $this->get('event_dispatcher');
//
//        $user = $userManager->createUser();
//        $user->setEnabled(true);
//
//        $event = new GetResponseUserEvent($user, $request);
//        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);
//
//        if (null !== $event->getResponse()) {
//            return $event->getResponse();
//        }
//
//        $form = $formFactory->createForm();
//        $form->setData($user);
//
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted()) {
//            if ($form->isValid()) {
//                $event = new FormEvent($form, $request);
//                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
//
//                $userManager->updateUser($user);
//
//                /*****************************************************
//                 * Add new functionality (e.g. log the registration) *
//                 *****************************************************/
//                $this->container->get('logger')->info(
//                    sprintf("New user registration: %s", $user)
//                );
//
//                if (null === $response = $event->getResponse()) {
//                    $url = $this->generateUrl('fos_user_registration_confirmed');
//                    $response = new RedirectResponse($url);
//                }
//
//                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
//
//                return $response;
//            }
//
//            $event = new FormEvent($form, $request);
//            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);
//
//            if (null !== $response = $event->getResponse()) {
//                return $response;
//            }
//        }
//
//        return $this->render('@FOSUser/Registration/register.html.twig', array(
//            'form' => $form->createView(),
//        ));
//    }

///**
// * @Route("/register", name="register_check")
// * @Method({"POST"})
// */
//  public function checkRegistration(Request $request)
//  {
//    $route = 'register_form';
//    $error = false;
//
//    $username = $request->request->get('fos_user_registration_form')['username'];
//    if (strpos($username, "@") !== false)
//    {
//      $this->addFlash(
//        'catroweb_error_message',
//        "errors.username.not_email"
//      );
//      $error = true;
//    }
//
//    $password = $request->request->get('fos_user_registration_form')['plainPassword'];
//    if ($password['first'] !== $password['second'])
//    {
//      $this->addFlash(
//        'catroweb_error_message',
//        "passwordsNoMatch"
//      );
//      $error = true;
//    }
//
//    if ($error)
//    {
//      return $this->redirectToRoute('register_form');
//    }
//
//
//    $response = $this->forward($this->registerAction($request));
//    return $response;
//  }

//  /**
//   * @Route("/register", name="register_form")
//   * @Method({"GET"})
//   */
//  public function showRegistrationForm(Request $request)
//  {
//      $response = $this->forward('fos_user.registration.controller:registerAction', array());
//      return $response;
//  }
}

