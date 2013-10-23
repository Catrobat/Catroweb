<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\CatrowebBundle\Entity\User;

class ApiController extends Controller
{
    public function checkTokenAction()
    {
        return $this->render('CatrobatApiBundle:Api:checkToken.json.twig');
    }

    public function uploadAction()
    {
    	return $this->render('CatrobatApiBundle:Api:index.json.twig');
    }
    
    public function loginOrRegisterAction()
    {
      $userManager = $this->container->get('fos_user.user_manager');
      $request = $this->getRequest();
      $retArray = array();
      $username = $request->request->get('registrationUsername');
      $userpassword = $request->request->get('registrationPassword');
      $usercountry = $request->request->get('registrationCountry');
      $user = $userManager->findUserByUsername($username);
      
      if ($user == null) 
      {
        if ($userpassword == "") 
        {
          $retArray['statusCode'] = 602;
          $retArray['answer'] = "The password is missing.";
        } 
        else if (strlen($userpassword) < 6) 
        {
          $retArray['statusCode'] = 602;
          $retArray['answer'] = "Your password must have at least 6 characters.";
        } 
        else if (strlen($usercountry) == 0)
        {
          $retArray['statusCode'] = 602;
          $retArray['answer'] = "The country is missing.";
        }
        else 
        {
          $user = $userManager->createUser();
          $user->setUsername($username);
          $user->setEmail($request->request->get('registrationEmail'));
          $user->setPlainPassword($userpassword);
          $user->setEnabled(true);
          $user->setToken("RandomToken");
          
          $userManager->updateUser($user);
          $retArray['statusCode'] = 201;
          $retArray['answer'] = "Registration successful!";
          $retArray['token'] = $user->getToken();
        }
      } 
      else 
      {
        $retArray['statusCode'] = 200;
        $encoder_service = $this->get('security.encoder_factory');
        $encoder = $encoder_service->getEncoder($user);
        $encoded_pass = $encoder->encodePassword($request->request->get('registrationPassword'), $user->getSalt());
        
        if ($user->getPassword() == $encoded_pass) 
        {
          $retArray['statusCode'] = 200;
          $retArray['token'] = $user->getToken();
        } 
        else 
        {
          $retArray['statusCode'] = 601;
          $retArray['answer'] = "The password or username was incorrect.";
          
        }
      }
      
      
//       $this->getDoctrine()->getManager()->persist($user);
//       $this->getDoctrine()->getManager()->flush();
      
      return $this->render('CatrobatApiBundle:Api:loginOrRegister.json.twig', $retArray);
    }
    
}
