<?php

declare(strict_types=1);

namespace App\Security\OAuth;

use App\DB\Entity\User\User;
use App\User\UserManager;
use HWI\Bundle\OAuthBundle\Connect\AccountConnectorInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class HwiOauthAccountConnector implements AccountConnectorInterface
{
  protected array $properties = ['identifier' => 'id'];

  public function __construct(protected UserManager $user_manager, array $properties)
  {
    $this->properties = array_merge($this->properties, $properties);
  }

  /**
   * Overwrite the provider!
   */
  #[\Override]
  public function connect(UserInterface $user, UserResponseInterface $response): void
  {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', $user::class));
    }

    // retrieve access token and the ID
    $property = $this->getProperty($response);
    $username = $response->getUsername();
    $service = $response->getResourceOwner()->getName();

    $setter_name = 'set'.ucfirst($service);
    $setter_id = $setter_name.'Id';
    $setter_access_token = $setter_name.'AccessToken';
    $access_token = $response->getAccessToken();

    // Disconnect previous user
    if (($previousUser = $this->user_manager->findOneBy([$property => $username])) instanceof User) {
      $previousUser->{$setter_id}(null);
      $previousUser->{$setter_access_token}(null);
      $this->user_manager->updateUser($user);
    }

    // username is a unique integer
    $user->{$setter_id}($username);
    $user->{$setter_access_token}($access_token);

    $this->user_manager->updateUser($user);
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
