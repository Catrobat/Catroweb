<?php

namespace App\Catrobat\Security;

use App\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class FOSUBUserProvider extends BaseClass
{
  public function connect(UserInterface $user, UserResponseInterface $response): void
  {
    if (!$user instanceof User)
    {
      throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', \get_class($user)));
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
    if (null !== $previousUser = $this->userManager->findUserBy([$property => $username]))
    {
      $previousUser->{$setter_id}(null);
      $previousUser->{$setter_access_token}(null);
      $this->userManager->updateUser($user);
    }
    //username is a unique integer
    $user->{$setter_id}($username);
    $user->{$setter_access_token}($access_token);
    $this->userManager->updateUser($user);
  }

  public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
  {
    $username = $response->getUsername();

    $user = $this->userManager->findUserBy([$this->getProperty($response) => $username]);
    $service = $response->getResourceOwner()->getName();
    //make setter and getter names dynamic so we can use it for more services (Facebook, Google, Github)
    $setter_name = 'set'.ucfirst($service);
    $setter_id = $setter_name.'Id';
    $setter_access_token = $setter_name.'AccessToken';
    $access_token = $response->getAccessToken();
    //register new user
    if (null === $user)
    {
      $user = $this->userManager->findUserByEmail($response->getEmail());
      //if user with the given email doesnt exists create a new user
      if (null === $user)
      {
        /** @var User $user */
        $user = $this->userManager->createUser();
        //generate random username for example user12345678, needs to be discussed
        $user->setUsername($this->createRandomUsername($response));
        $user->setEmail($response->getEmail());
        $user->setEnabled(true);
        $user->setPassword($this->generateRandomPassword());
        $user->setOauthUser(true);
      }
      $user->{$setter_id}($username);
      $user->{$setter_access_token}($access_token);
      $this->userManager->updateUser($user);

      return $user;
    }

    //if user exists just proceed with hwioauthentication
    $user = parent::loadUserByOAuthUserResponse($response);

    //update access token
    $user->{$setter_access_token}($access_token);

    return $user;
  }

  private function generateRandomPassword(int $length = 32): string
  {
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'.
      '0123456789-=~!@#$%&*()_+,.<>?;:[]{}|';

    $pass = '';
    $max = strlen($chars) - 1;

    for ($i = 0; $i < $length; ++$i)
    {
      $pass .= $chars[random_int(0, $max)];
    }

    return $pass;
  }

  private function createRandomUsername(UserResponseInterface $response): string
  {
    $first_name = $response->getFirstName();
    $last_name = $response->getLastName();
    $username_base = $first_name.$last_name;
    if (empty($username_base))
    {
      $username_base = 'user';
    }
    $username = $username_base;
    $user_number = 0;
    while (null !== $this->userManager->findUserByUsername($username))
    {
      ++$user_number;
      $username = $username_base.$user_number;
    }

    return $username;
  }
}
