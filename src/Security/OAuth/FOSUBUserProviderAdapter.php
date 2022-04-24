<?php

namespace App\Security\OAuth;

use App\DB\Entity\User\User;
use App\User\UserManager;
use Exception;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use RuntimeException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProviderAdapter implements OAuthAwareUserProviderInterface
{
  protected UserManager $user_manager;
  protected array $properties = ['identifier' => 'id'];

  public function __construct(UserManager $userManager, array $properties)
  {
    $this->properties = array_merge($this->properties, $properties);
    $this->user_manager = $userManager;
  }

  /**
   * Overwrite the provider!
   */
  public function connect(UserInterface $user, UserResponseInterface $response): void
  {
    if (!$user instanceof User) {
      throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
    }
    //retrieve access token and the ID
    $property = $this->getProperty($response);
    $username = $response->getUsername();
    $service = $response->getResourceOwner()->getName();

    $setter_name = 'set'.ucfirst($service);
    $setter_id = $setter_name.'Id';
    $setter_access_token = $setter_name.'AccessToken';
    $access_token = $response->getAccessToken();

    //Disconnect previous user
    if (null !== $previousUser = $this->user_manager->findOneBy([$property => $username])) {
      $previousUser->{$setter_id}(null);
      $previousUser->{$setter_access_token}(null);
      $this->user_manager->updateUser($user);
    }
    //username is a unique integer
    $user->{$setter_id}($username);
    $user->{$setter_access_token}($access_token);
    $this->user_manager->updateUser($user);
  }

  /**
   * Overwrite the provider!
   */
  public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
  {
    $username = $response->getUsername();

    $user = $this->user_manager->findOneBy([$this->getProperty($response) => $username]);
    $service = $response->getResourceOwner()->getName();
    //make setter and getter names dynamic so we can use it for more services (Facebook, Google, Github)
    $setter_name = 'set'.ucfirst($service);
    $setter_id = $setter_name.'Id';
    $setter_access_token = $setter_name.'AccessToken';
    $access_token = $response->getAccessToken();
    //register new user
    if (null === $user) {
      $user = $this->user_manager->findUserByEmail($response->getEmail());
      //if user with the given email doesnt exists create a new user
      if (null === $user) {
        /** @var User $user */
        $user = $this->user_manager->create();
        //generate random username for example user12345678, needs to be discussed
        $user->setUsername($this->createRandomUsername($response));
        $user->setEmail($response->getEmail());
        $user->setEnabled(true);
        $user->setPassword($this->generateRandomPassword());
        $user->setOauthUser(true);
      }
      $user->{$setter_id}($username);
      $user->{$setter_access_token}($access_token);
      $this->user_manager->updateUser($user);

      return $user;
    }

    //update access token
    $user->{$setter_access_token}($access_token);

    return $user;
  }

  /**
   * @throws Exception
   */
  private function generateRandomPassword(int $length = 32): string
  {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
      '0123456789-=~!@#$%&*()_+,.<>?;:[]{}|';

    $pass = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; ++$i) {
      $pass .= $chars[random_int(0, $max)];
    }

    return $pass;
  }

  private function createRandomUsername(UserResponseInterface $response): string
  {
    $first_name = $response->getFirstName();
    $last_name = $response->getLastName();
    $username_base = $first_name.$last_name;
    if (empty($username_base)) {
      $username_base = 'user';
    }
    $username = $username_base;
    $user_number = 0;
    while (null !== $this->user_manager->findUserByUsername($username)) {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }

  /**
   * @throws RuntimeException
   */
  protected function getProperty(UserResponseInterface $response): string
  {
    $resourceOwnerName = $response->getResourceOwner()->getName();

    if (!isset($this->properties[$resourceOwnerName])) {
      throw new RuntimeException(sprintf("No property defined for entity for resource owner '%s'.", $resourceOwnerName));
    }

    return $this->properties[$resourceOwnerName];
  }
}
