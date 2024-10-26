<?php

declare(strict_types=1);

namespace App\Api\Services\Search;

use App\Api\Services\AuthenticationManager;
use App\Api\Services\Base\AbstractApiFacade;
use App\Project\ProjectManager;
use App\Storage\ImageRepository;
use App\User\UserManager;
use App\Utils\ElapsedTimeStringFormatter;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchApiFacade extends AbstractApiFacade
{
  public function __construct(
    AuthenticationManager $authentication_manager,
    private readonly SearchResponseManager $response_manager,
    private readonly SearchApiLoader $loader,
    private readonly SearchApiProcessor $processor,
    private readonly SearchRequestValidator $request_validator,
    private readonly ProjectManager $project_manager,
    private readonly UserManager $user_manager,
    private readonly TranslatorInterface $translator,
    private readonly ElapsedTimeStringFormatter $time_formatter,
    private readonly TokenStorageInterface $token_storage,
    private readonly JWTTokenManagerInterface $jwt_manager,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly ImageRepository $image_repository,
  ) {
    parent::__construct($authentication_manager);
  }

  #[\Override]
  public function getResponseManager(): SearchResponseManager
  {
    return $this->response_manager;
  }

  #[\Override]
  public function getLoader(): SearchApiLoader
  {
    return $this->loader;
  }

  #[\Override]
  public function getProcessor(): SearchApiProcessor
  {
    return $this->processor;
  }

  #[\Override]
  public function getRequestValidator(): SearchRequestValidator
  {
    return $this->request_validator;
  }

  public function getProjectManager(): ProjectManager
  {
    return $this->project_manager;
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
