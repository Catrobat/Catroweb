<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use App\DB\Entity\User\User;
use App\Security\PasswordGenerator;
use App\User\UserManager;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class HwiOauthUserProvider implements OAuthAwareUserProviderInterface
{
  protected array $properties = ['identifier' => 'id'];

  public function __construct(protected UserManager $user_manager, array $properties)
  {
    $this->properties = array_merge($this->properties, $properties);
  }

  #[\Override]
  public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
  {
    $username = $response->getUsername();

    $user = $this->user_manager->findOneBy([$this->getProperty($response) => $username]);
    $service = $response->getResourceOwner()->getName();
    // make setter and getter names dynamic so we can use it for more services (Facebook, Google, Github)
    $setter_name = 'set'.ucfirst($service);
    $setter_id = $setter_name.'Id';
    $setter_access_token = $setter_name.'AccessToken';
    $access_token = $response->getAccessToken();
    // register new user
    if (!$user instanceof User) {
      $user = $this->user_manager->findUserByEmail($response->getEmail());
      // if user with the given email doesnt exists create a new user
      if (!$user instanceof \Sonata\UserBundle\Model\UserInterface) {
        /** @var User $user */
        $user = $this->user_manager->create();
        // generate random username for example user12345678, needs to be discussed
        $user->setUsername($this->createRandomUsername($response));
        $user->setEmail($response->getEmail());
        $user->setEnabled(true);
        $user->setPassword(PasswordGenerator::generateRandomPassword());
        $user->setOauthUser(true);
      }

      $user->{$setter_id}($username);
      $user->{$setter_access_token}($access_token);
      $this->user_manager->updateUser($user);

      return $user;
    }

    // update access token
    $user->{$setter_access_token}($access_token);

    return $user;
  }

  private function createRandomUsername(UserResponseInterface $response): string
  {
    $first_name = $response->getFirstName();
    $last_name = $response->getLastName();
    $username_base = $first_name.$last_name;
    if ('' === $username_base || '0' === $username_base) {
      $username_base = 'user';
    }

    $username = $username_base;
    $user_number = 0;
    while ($this->user_manager->findUserByUsername($username) instanceof \Sonata\UserBundle\Model\UserInterface) {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }

  /**
   * @throws \RuntimeException
   */
  protected function getProperty(UserResponseInterface $response): string
  {
    $resourceOwnerName = $response->getResourceOwner()->getName();

    if (!isset($this->properties[$resourceOwnerName])) {
      throw new \RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
    }

    return $this->properties[$resourceOwnerName];
  }
}
