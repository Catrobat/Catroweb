<?php

namespace App\Api\Services\Search;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use App\Catrobat\Services\ImageRepository;
use App\Entity\ProgramManager;
use App\Entity\UserManager;
use App\Utils\ElapsedTimeStringFormatter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class SearchApiFacade extends AbstractApiFacade
{
  private SearchResponseManager $response_manager;
  private SearchApiLoader $loader;
  private SearchApiProcessor $processor;
  private SearchRequestValidator $request_validator;

  private UserManager $user_manager;
  private ProgramManager $program_manager;
  private TranslatorInterface $translator;
  private TokenStorageInterface $token_storage;
  private JWTTokenManagerInterface $jwt_manager;
  private ImageRepository $image_repository;
  private ElapsedTimeStringFormatter $time_formatter;
  private ParameterBagInterface $parameter_bag;

  public function __construct(
    AuthenticationManager $authentication_manager,
    SearchResponseManager $response_manager,
    SearchApiLoader $loader,
    SearchApiProcessor $processor,
    SearchRequestValidator $request_validator,
    ProgramManager $program_manager,
    UserManager $user_manager,
    TranslatorInterface $translator,
    ElapsedTimeStringFormatter $time_formatter,
    TokenStorageInterface $token_storage,
    JWTTokenManagerInterface $jwt_manager,
    ParameterBagInterface $parameter_bag,
    ImageRepository $image_repository
  ) {
    parent::__construct($authentication_manager);
    $this->response_manager = $response_manager;
    $this->loader = $loader;
    $this->processor = $processor;
    $this->request_validator = $request_validator;

    $this->program_manager = $program_manager;
    $this->user_manager = $user_manager;
    $this->translator = $translator;
    $this->time_formatter = $time_formatter;
    $this->token_storage = $token_storage;
    $this->jwt_manager = $jwt_manager;
    $this->parameter_bag = $parameter_bag;
    $this->image_repository = $image_repository;
  }

  public function getResponseManager(): SearchResponseManager
  {
    return $this->response_manager;
  }

  public function getLoader(): SearchApiLoader
  {
    return $this->loader;
  }

  public function getProcessor(): SearchApiProcessor
  {
    return $this->processor;
  }

  public function getRequestValidator(): SearchRequestValidator
  {
    return $this->request_validator;
  }

  public function getProgramManager(): ProgramManager
  {
    return $this->program_manager;
  }

  public function getUserManager(): UserManager
  {
    return $this->user_manager;
  }

  public function getTranslator(): TranslatorInterface
  {
    return $this->translator;
  }

  public function getTimeFormatter(): ElapsedTimeStringFormatter
  {
    return $this->time_formatter;
  }

  public function getTokenStorage(): TokenStorageInterface
  {
    return $this->token_storage;
  }

  public function getJWTManager(): JWTTokenManagerInterface
  {
    return $this->jwt_manager;
  }

  public function getParameterBag(): ParameterBagInterface
  {
    return $this->parameter_bag;
  }

    public function getImageRepository(): ImageRepository
    {
        return $this->image_repository;
    }
}
