<?php

declare(strict_types=1);

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Project\CodeView\CodeExportUnavailableException;
use App\Project\CodeView\CodeViewExportStore;
use App\Project\CodeView\CodeViewTranslation;
use App\Translation\TranslationDelegate;
use OpenAPI\Server\Api\CodeViewTranslationApiInterface;
use OpenAPI\Server\Model\CodeViewTranslationResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;

final class CodeViewTranslationApi extends AbstractApiController implements CodeViewTranslationApiInterface
{
  use RateLimitTrait;

  public function __construct(
    private readonly ProjectsApiLoader $projects,
    private readonly CodeViewExportStore $exports,
    private readonly CodeViewTranslation $translation,
    private readonly TranslationDelegate $languages,
    private readonly RateLimiterFactoryInterface $translationBurstLimiter,
  ) {
  }

  #[\Override]
  public function projectsIdCodeTranslationGet(
    string $id,
    string $target_language,
    ?string $source_language,
    ?bool $poll,
    ?bool $resume,
    ?string $x_code_export_key,
    int &$responseCode,
    array &$responseHeaders,
  ): ?CodeViewTranslationResponse {
    $responseHeaders['Cache-Control'] = 'private, no-store';
    $project = $this->projects->findProjectByID($id, true);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }
    try {
      [$source, $target] = $this->languages->machineLanguages($source_language, $target_language);
      $snapshot = $this->exports->snapshot($project);
    } catch (\InvalidArgumentException) {
      $responseCode = Response::HTTP_BAD_REQUEST;

      return null;
    } catch (CodeExportUnavailableException) {
      $responseCode = Response::HTTP_SERVICE_UNAVAILABLE;

      return null;
    }
    $key = null === $snapshot ? null : CodeViewExportStore::translationKey($snapshot);
    if (null !== $key) {
      $responseHeaders['X-Code-Export-Key'] = $key;
    }
    if (null === $key || (null !== $x_code_export_key && $x_code_export_key !== $key)) {
      $responseCode = Response::HTTP_CONFLICT;

      return null;
    }

    $client_ip = $this->getCurrentRequest()?->getClientIp() ?? 'unknown';
    $state = $this->translation->request($id, $key, $source, $target, $poll ?? false, $resume ?? false,
      fn (): bool => null !== $this->checkIpRateLimit($client_ip, $this->translationBurstLimiter));
    $responseCode = match ($state['status']) {
      'complete', 'partial', 'source_required' => Response::HTTP_OK,
      'queued' => Response::HTTP_ACCEPTED,
      'unsupported' => Response::HTTP_UNPROCESSABLE_ENTITY,
      'refused' => Response::HTTP_TOO_MANY_REQUESTS,
      default => Response::HTTP_SERVICE_UNAVAILABLE,
    };
    if (Response::HTTP_ACCEPTED === $responseCode) {
      $responseHeaders['Retry-After'] = '2';
    }

    return Response::HTTP_OK === $responseCode ? new CodeViewTranslationResponse($state) : null;
  }
}
