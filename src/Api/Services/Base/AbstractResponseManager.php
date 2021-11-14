<?php

namespace App\Api\Services\Base;

use OpenAPI\Server\Service\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractResponseManager.
 */
abstract class AbstractResponseManager implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  protected SerializerInterface $serializer;

  public function __construct(TranslatorInterface $translator, SerializerInterface $serializer)
  {
    $this->initTranslator($translator);
    $this->serializer = $serializer;
  }

  /**
   * The Response hash is added to the header to allow clients to distinguish if the response body must be requested
   * and then loaded. For example, to update a project category shown on a landing page. If the hash between the new
   * and old requests did not change, there is no need to request/load the new response body. This workflow can lead
   * to a significant performance boost.
   *
   * @param mixed $response
   */
  public function addResponseHashToHeaders(array &$responseHeaders, $response): void
  {
    $responseHeaders['X-Response-Hash'] = md5($this->getSerializer()->serialize($response, 'application/json'));
  }

  public function addContentLanguageToHeaders(array &$responseHeaders): void
  {
    $responseHeaders['Content-Language'] = $this->getLocale();
  }

  protected function getSerializer(): SerializerInterface
  {
    return $this->serializer;
  }
}
