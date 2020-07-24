<?php

namespace App\Entity;

use App\Utils\TimeUtils;
use App\Utils\Utils;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManager extends \Sonata\UserBundle\Entity\UserManager
{
  private ProgramManager $program_manager;

  private TransformedFinder $user_finder;

  private UrlHelper $url_helper;

  public function __construct(PasswordUpdaterInterface $passwordUpdater,
                              CanonicalFieldsUpdater $canonicalFieldsUpdater,
                              EntityManagerInterface $em,
                              TransformedFinder $user_finder,
                              ProgramManager $program_manager,
                              UrlHelper $url_helper)
  {
    $this->user_finder = $user_finder;
    $this->url_helper = $url_helper;
    $this->program_manager = $program_manager;

    /** @var ObjectManager $om */
    $om = $em;
    parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, User::class);
  }

  public function decodeToken(string $token): array
  {
    $tokenParts = explode('.', $token);
    $tokenPayload = base64_decode($tokenParts[1], true);

    return json_decode($tokenPayload, true);
  }

  public function isPasswordValid(UserInterface $user, string $password, PasswordEncoderInterface $encoder): bool
  {
    return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
  }

  public function getMappedUserData(array $raw_user_data): array
  {
    $response_data = [];

    foreach ($raw_user_data as $user)
    {
      try
      {
        $country = Countries::getName(strtoupper($user->getCountry()));
      }
      catch (MissingResourceException $e)
      {
        $country = '';
      }
      array_push($response_data, [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
        'project_count' => count($this->program_manager->getPublicUserPrograms($user->getId())),
        'country' => $country,
        'profile' => $user,
      ]);
    }

    return $response_data;
  }

  public function createUserFromScratch(array $userdata): ?User
  {
    $scratch_user_id = intval($userdata['id']);
    /** @var User|null $user */
    $user = $this->findUserBy(['scratch_user_id' => $scratch_user_id]);

    if (null === $user)
    {
      $username = $userdata['username'];
      $user = new User();
      $user->setScratchUserId($scratch_user_id);
      $user->setScratchUsername($username);
      $user->setEmail($username.'@localhost');
      $user->setPlainPassword(Utils::randomPassword());
      if ($avatar = $userdata['profile']['images']['90x90'] ?? null)
      {
        $user->setAvatar($avatar);
      }
      $joined = TimeUtils::dateTimeFromScratch($userdata['history']['joined']);
      if ($joined)
      {
        $user->changeCreatedAt($joined);
      }
      $this->objectManager->persist($user);
      $this->objectManager->flush();
      $this->objectManager->refresh($user);
    }

    return $user;
  }

  public function search(string $query, ?int $limit = 10, int $offset = 0): array
  {
    $program_query = $this->userSearchQuery($query);

    return $this->user_finder->find($program_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query): int
  {
    $program_query = $this->userSearchQuery($query);

    $paginator = $this->user_finder->findPaginated($program_query);

    return $paginator->getNbResults();
  }

  private function userSearchQuery(string $query): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word)
    {
      $word = $word.'*';
    }
    unset($word);
    $query = implode(' ', $words);

    $query_string = new QueryString();
    $query_string->setQuery($query);
    $query_string->setFields(['id', 'username']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $bool_query = new BoolQuery();
    $bool_query->addMust($query_string);

    return $bool_query;
  }
}
