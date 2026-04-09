<?php

namespace OpenAPI\Server\Service;

interface SerializerInterface
{
  /**
   * Serializes the given data to the specified output format.
   *
   * @param object|array|scalar $data
   */
  public function serialize($data, string $format): string;

  /**
   * Deserializes the given data to the specified type.
   *
   * @return object|array|scalar
   */
  public function deserialize($data, string $type, string $format);
}
