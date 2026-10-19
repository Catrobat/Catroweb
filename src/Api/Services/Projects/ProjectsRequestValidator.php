<?php

declare(strict_types=1);

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractRequestValidator;
use App\Api\Services\GeneralValidator;
use App\Api\Services\ValidationWrapper;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\User\UserManager;
use OpenAPI\Server\Model\UpdateProjectRequest;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsRequestValidator extends AbstractRequestValidator
{
  final public const int MIN_NAME_LENGTH = 1;

  final public const int MAX_NAME_LENGTH = 255;

  final public const int MAX_DESCRIPTION_LENGTH = 10_000;

  final public const int MAX_CREDITS_LENGTH = 3_000;

  final public const string UPLOAD_EXTENSION = 'catrobat';

  final public const int MAX_UPLOAD_SIZE_BYTES = 100 * 1_024 * 1_024; // 100 MB

  final public const int MIN_SLUG_LENGTH = 3;

  final public const int MAX_SLUG_LENGTH = 50;

  /**
   * Slugs live at /project/{slug}; block siblings of that route and words that would confuse users.
   */
  final public const array RESERVED_SLUGS = [
    'about', 'admin', 'api', 'app', 'catrobat', 'comment', 'comments', 'delete', 'details', 'edit', 'explore',
    'help', 'login', 'logout', 'new', 'null', 'pocketcode', 'program', 'project', 'projects', 'register',
    'search', 'settings', 'studio', 'studios', 'undefined', 'upload', 'user', 'users',
  ];

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, private readonly UserManager $user_manager, private readonly ProjectRepository $project_repository)
  {
    parent::__construct($validator, $translator);
  }

  public function validateUserExists(string $user_id): bool
  {
    return '' !== trim($user_id)
        && !ctype_space($user_id)
        && $this->user_manager->findOneBy(['id' => $user_id]) instanceof User;
  }

  public function validateUploadFile(string $checksum, UploadedFile $file, string $locale): ValidationWrapper
  {
    $KEY = 'error';

    if (!$file->isValid()) {
      return $this->getValidationWrapper()->addError(
        $this->__('api.projectsPost.upload_error', [], $locale), $KEY
      );
    }

    $extension = $file->getClientOriginalExtension();
    if ('' !== $extension && self::UPLOAD_EXTENSION !== strtolower($extension)) {
      return $this->getValidationWrapper()->addError(
        $this->__('api.projectsPost.invalid_file_extension', [], $locale), $KEY
      );
    }

    if ($file->getSize() > self::MAX_UPLOAD_SIZE_BYTES) {
      return $this->getValidationWrapper()->addError(
        $this->__('api.projectsPost.file_too_large', [], $locale), $KEY
      );
    }

    $calculated_checksum = md5_file($file->getPathname());
    if (strtolower((string) $calculated_checksum) !== strtolower($checksum)) {
      return $this->getValidationWrapper()->addError(
        $this->__('api.projectsPost.invalid_checksum', [], $locale), $KEY
      );
    }

    return $this->getValidationWrapper();
  }

  public function validateUpdateRequest(UpdateProjectRequest $request, string $locale, ?Project $project = null): ValidationWrapper
  {
    if (!is_null($request->getName())) {
      $this->validateName($request->getName(), $locale);
    }

    if (!is_null($request->getDescription())) {
      $this->validateDescription($request->getDescription(), $locale);
    }

    if (!is_null($request->getCredits())) {
      $this->validateCredits($request->getCredits(), $locale);
    }

    if (!is_null($request->getScreenshot())) {
      $this->validateScreenshot($request->getScreenshot(), $locale);
    }

    $slug = $request->getSlug();
    if (!is_null($slug)) {
      $this->validateSlug($slug, $project, $locale);
    }

    return $this->getValidationWrapper();
  }

  private function validateName(string $name, string $locale): void
  {
    $KEY = 'name';

    if (strlen($name) < self::MIN_NAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.nameEmpty', [], $locale), $KEY);
    } elseif (strlen($name) > self::MAX_NAME_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.nameTooLong', [], $locale), $KEY);
    }
  }

  private function validateDescription(string $description, string $locale): void
  {
    $KEY = 'description';

    if (strlen($description) > self::MAX_DESCRIPTION_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.descriptionTooLong', [], $locale), $KEY);
    }
  }

  private function validateCredits(string $credits, string $locale): void
  {
    $KEY = 'credits';

    if (strlen($credits) > self::MAX_CREDITS_LENGTH) {
      $this->getValidationWrapper()->addError($this->__('api.project.creditsTooLong', [], $locale), $KEY);
    }
  }

  private function validateScreenshot(string $screenshot, string $locale): void
  {
    $KEY = 'screenshot';
    if (false === GeneralValidator::validateImageDataUrl($screenshot)) {
      $this->getValidationWrapper()->addError($this->__('api.project.screenshotInvalid', [], $locale), $KEY);
    }
  }

  /**
   * Lowercases and trims; an empty slug removes the custom URL.
   */
  public static function normalizeSlug(string $slug): ?string
  {
    $slug = strtolower(trim($slug));

    return '' === $slug ? null : $slug;
  }

  private function validateSlug(string $slug, ?Project $project, string $locale): void
  {
    $KEY = 'slug';
    $slug = self::normalizeSlug($slug);
    if (null === $slug) {
      return;
    }

    // Letters, digits and single inner hyphens; at least one letter so a slug never looks like a numeric
    // legacy id, and never UUID-shaped so it can't shadow a project id.
    if (strlen($slug) < self::MIN_SLUG_LENGTH || strlen($slug) > self::MAX_SLUG_LENGTH
      || 1 !== preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)
      || 1 !== preg_match('/[a-z]/', $slug)
      || 1 === preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $slug)) {
      $this->getValidationWrapper()->addError($this->__('api.project.slugInvalid', [], $locale), $KEY);
    } elseif (in_array($slug, self::RESERVED_SLUGS, true)) {
      $this->getValidationWrapper()->addError($this->__('api.project.slugReserved', [], $locale), $KEY);
    } else {
      $owner = $this->project_repository->findOneBy(['slug' => $slug]);
      if ($owner instanceof Project && $owner !== $project) {
        $this->getValidationWrapper()->addError($this->__('api.project.slugTaken', [], $locale), $KEY);
      }
    }
  }
}
