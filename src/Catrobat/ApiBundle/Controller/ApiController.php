<?php

namespace Catrobat\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\CatrowebBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\CatrowebBundle\Model\UserManager;
use Catrobat\CatrowebBundle\Model\Requests\AddProjectRequest;
use Catrobat\CatrowebBundle\Model\ProjectManager;
use Catrobat\CatrowebBundle\Services\TokenGenerator;
use Catrobat\CatrowebBundle\Exceptions\InvalidCatrobatFileException;

class ApiController
{
    protected $templating;
    protected $user_manager;
    protected $validator;
    protected $context;
    protected $project_manager;
    protected $tokenGenerator;
    
    public function __construct(EngineInterface $templating, UserManager $user_manager, Validator $validator, SecurityContext $context, ProjectManager $project_manager, TokenGenerator $tokenGenerator)
    {
      $this->templating = $templating;
      $this->user_manager = $user_manager;
      $this->validator = $validator;
      $this->context = $context;
      $this->project_manager = $project_manager;
      $this->tokenGenerator = $tokenGenerator;
    }
  
    public function checkTokenAction()
    {
      return $this->templating->renderResponse('CatrobatApiBundle:Api:checkToken.json.twig');
    }

    public function uploadAction(Request $request)
    {
      if ($request->files->count() != 1)
      {
        $response["statusCode"] = 501;
        $response["answer"] = "POST-Data not correct or missing!";
      }
      else 
      {
        try 
        {
          $add_project_request = new AddProjectRequest($this->context->getToken()->getUser(), $request->files->get(0));
          
          $id = $this->project_manager->addProject($add_project_request);
          $user = $this->context->getToken()->getUser();
          $user->setToken($this->tokenGenerator->generateToken());
          $this->user_manager->updateUser($user);
          
          $response["projectId"] = $id;
          $response["statusCode"] = 200;
          $response["answer"] = "Your project was uploaded successfully!";
          $response["token"] = $user->getToken();
        }
        catch (InvalidCatrobatFileException $exception)
        {
          $response["statusCode"] = 501;
          $response["answer"] = $exception->getMessage();
        }
      }
        
//      $num_files = $this->context->getToken()->getUser()->getUsername(); //$request->request->get('fileChecksum'); //$request->files->count();
      $response["preHeaderMessages"] = "";
      return $this->templating->renderResponse('CatrobatApiBundle:Api:upload.json.twig', array("response" => $response));
    }
    
    public function loginOrRegisterAction(Request $request)
    {
      $userManager = $this->user_manager;
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
          $user->setToken($this->tokenGenerator->generateToken());
          
          $userManager->updateUser($user);
          $retArray['statusCode'] = 201;
          $retArray['answer'] = "Registration successful!";
          $retArray['token'] = $user->getToken();
        }
      } 
      else 
      {
        $retArray['statusCode'] = 200;
        $correct_pass = $userManager->isPasswordValid($user, $request->request->get('registrationPassword'));
        
        if ($correct_pass) 
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
      
      return $this->templating->renderResponse('CatrobatApiBundle:Api:loginOrRegister.json.twig', $retArray);
    }
    
    public function searchProjectsAction(Request $request) 
    {
      $retArray = array();
      $projectName = $request->request->get('projectName');
      $limit = intval($request->request->get('limit'));
      $offset = intval($request->request->get('offset'));
       
      //$retArray['projectName'] = $projectName;
      $retArray['limit'] = $limit;
      $retArray['offset'] = $offset;
      
      $entities = $this->project_manager->findAll();
      $retArray['numOfProjects'] = count($entities);
      
      $entities = $this->project_manager->findOneByName($projectName);
      $retArray['id'] = $entities->getId();
      $retArray['projectName'] = $entities->getName();
      $retArray['description'] = $entities->getDescription();
      $retArray['downloads'] = $entities->getDownloads();
      $retArray['views'] = $entities->getViews();
      $retArray['author'] = $entities->getUser()->getUsername();
      $retArray['uploaded_time'] = $entities->getUploadedAt()->getTimestamp();

      return $this->templating->renderResponse('CatrobatApiBundle:Api:searchProjects.json.twig', $retArray);
    }
}
