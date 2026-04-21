<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Translation\TranslationApiFacade;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use OpenAPI\Server\Api\TranslationApiInterface;
use OpenAPI\Server\Model\ProjectCustomTranslationResponse;
use OpenAPI\Server\Model\ProjectCustomTranslationSaveRequest;
use OpenAPI\Server\Model\ProjectTranslationResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class TranslationApi extends AbstractApiController implements TranslationApiInterface
{
  public function __construct(
    private readonly TranslationApiFacade $facade,
    private readonly ProjectManager $project_manager,
    private readonly RequestStack $request_stack,
  ) {
  }

  #[\Override]
  public function projectsIdTranslationGet(
    string $id,
    string $target_language,
    string $accept_language,
    ?string $source_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?ProjectTranslationResponse {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    if ($source_language === $target_language) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return null;
    }

    $etag_value = md5($project->getName().$project->getDescription().$project->getCredits()).$target_language;
    $etag_header = '"'.$etag_value.'"';
    $responseHeaders['ETag'] = $etag_header;

    $request = $this->request_stack->getCurrentRequest();
    $if_none_match = $request?->headers->get('If-None-Match');
    if (null !== $if_none_match) {
      $candidates = array_map(trim(...), explode(',', $if_none_match));
      foreach ($candidates as $candidate) {
        if (trim($candidate, '"') === $etag_value) {
          $responseCode = Response::HTTP_NOT_MODIFIED;

          return null;
        }
      }
    }

    $translation_result = $this->facade->getLoader()->translateProject($project, $source_language, $target_language);
    if (null === $translation_result) {
      $responseCode = Response::HTTP_SERVICE_UNAVAILABLE;

      return null;
    }

    $title_translation = $translation_result[0] ?? null;
    if (null === $title_translation) {
      $responseCode = Response::HTTP_SERVICE_UNAVAILABLE;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createProjectTranslationResponse(
      $project->getId() ?? '',
      $source_language,
      $target_language,
      $translation_result,
    );
  }

  #[\Override]
  public function projectsIdTranslationFieldLanguageGet(
    string $id,
    string $field,
    string $language,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?ProjectCustomTranslationResponse {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $result = $this->facade->getLoader()->getCustomTranslation($project, $field, $language);
    if (null === $result) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->facade->getResponseManager()->createCustomTranslationResponse($result);
  }

  #[\Override]
  public function projectsIdTranslationFieldLanguagePut(
    string $id,
    string $field,
    string $language,
    ProjectCustomTranslationSaveRequest $project_custom_translation_save_request,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $project = $this->project_manager->find($id);
    if (!$project instanceof Project || $project->getUser() !== $user) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    $text = $project_custom_translation_save_request->getText() ?? '';
    if ('' === trim($text)) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    try {
      $result = $this->facade->getProcessor()->saveCustomTranslation($project, $field, $language, $text);
    } catch (\InvalidArgumentException) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $responseCode = $result ? Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;
  }

  #[\Override]
  public function projectsIdTranslationFieldLanguageDelete(
    string $id,
    string $field,
    string $language,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): void {
    $user = $this->facade->getAuthenticationManager()->getAuthenticatedUser();
    if (!$user instanceof User) {
      $responseCode = Response::HTTP_UNAUTHORIZED;

      return;
    }

    $project = $this->project_manager->find($id);
    if (!$project instanceof Project || $project->getUser() !== $user) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return;
    }

    try {
      $result = $this->facade->getProcessor()->deleteCustomTranslation($project, $field, $language);
    } catch (\InvalidArgumentException) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return;
    }

    $responseCode = $result ? Response::HTTP_NO_CONTENT : Response::HTTP_INTERNAL_SERVER_ERROR;
  }

  #[\Override]
  public function projectsIdTranslationLanguagesGet(
    string $id,
    string $accept_language,
    int &$responseCode,
    array &$responseHeaders,
  ): ?array {
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return $this->facade->getLoader()->listDefinedLanguages($project);
  }
}
