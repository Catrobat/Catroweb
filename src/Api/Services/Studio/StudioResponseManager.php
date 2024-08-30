<?php

declare(strict_types=1);

namespace App\Api\Services\Studio;

use App\Api\Services\Base\AbstractResponseManager;
use App\Api\Services\ResponseCache\ResponseCacheManager;
use App\DB\Entity\Studio\Studio;
use OpenAPI\Server\Model\StudioResponse;
use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudioResponseManager extends AbstractResponseManager
{
  public function __construct(
    TranslatorInterface $translator,
    SerializerInterface $serializer,
    ResponseCacheManager $response_cache_manager,
    private readonly UrlGeneratorInterface $url_generator,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly RequestStack $request_stack,
  ) {
    parent::__construct($translator, $serializer, $response_cache_manager);
  }

  public function createStudioResponse(Studio $studio): StudioResponse
  {
    return (new StudioResponse())
      ->setId($studio->getId())
      ->setName($studio->getName())
      ->setDescription($studio->getDescription())
      ->setIsPublic($studio->isIsPublic())
      ->setEnableComments($studio->isAllowComments())
      ->setImagePath($this->generateImagePath($studio))
    ;
  }

  public function addStudioLocationToHeaders(array &$responseHeaders, Studio $studio): void
  {
    $responseHeaders['Location'] = $this->createStudioLocation($studio);
  }

  protected function createStudioLocation(Studio $studio): string
  {
    return $this->url_generator->generate(
      'studio_details',
      [
        'theme' => $this->parameter_bag->get('umbrellaTheme'),
        'id' => $studio->getId(),
      ],
      UrlGeneratorInterface::ABSOLUTE_URL
    );
  }

  protected function generateImagePath(Studio $studio): string
  {
    $assetPath = $studio->getCoverAssetPath();
    if (empty($assetPath)) {
      return '';
    }

    $baseUrl = $this->request_stack->getCurrentRequest()->getSchemeAndHttpHost();

    return $baseUrl.'/'.$assetPath;
  }
}
