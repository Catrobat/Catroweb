<?php

declare(strict_types=1);

namespace App\Security\Captcha;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CaptchaVerifier
{
  public function __construct(
    private readonly HttpClientInterface $httpClient,
    private readonly string $captchaVerifyUrl,
    private readonly string $captchaSecret,
    private readonly bool $captchaEnabled,
    private readonly string $appEnv,
  ) {
  }

  public function isEnabled(): bool
  {
    return $this->captchaEnabled && 'test' !== $this->appEnv;
  }

  /**
   * Verifies the CAPTCHA token via the Cap server's siteverify endpoint.
   * Disabled in dev (CAPTCHA_ENABLED=false) and test env: auto-passes.
   * In test env, token 'fail' forces a failure (for Behat testing).
   *
   * @return array{success: bool, result: string}
   */
  public function verify(?string $token, ?string $remoteIp = null): array
  {
    if ('test' === $this->appEnv) {
      if ('fail' === $token) {
        return ['success' => false, 'result' => 'test-forced-failure'];
      }

      return ['success' => true, 'result' => 'test-auto-pass'];
    }

    if (!$this->captchaEnabled) {
      return ['success' => true, 'result' => 'disabled'];
    }

    if (null === $token || '' === trim($token)) {
      return ['success' => false, 'result' => 'missing-token'];
    }

    $params = [
      'secret' => $this->captchaSecret,
      'response' => $token,
    ];

    $response = $this->httpClient->request('POST', $this->captchaVerifyUrl, [
      'json' => $params,
    ]);

    $data = $response->toArray(false);

    return [
      'success' => $data['success'] ?? false,
      'result' => ($data['success'] ?? false) ? 'verified' : 'verification-failed',
    ];
  }
}
