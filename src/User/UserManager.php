<?php

namespace App\User;

use App\DB\Entity\User\User;
use App\Project\ProgramManager;
use App\Security\PasswordGenerator;
use App\Utils\TimeUtils;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Util;
use Exception;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UserManager extends \Sonata\UserBundle\Entity\UserManager
{
  protected ProgramManager $program_manager;
  protected EntityManagerInterface $entity_manager;
  protected TransformedFinder $user_finder;
  protected UrlHelper $url_helper;

  public function __construct(PasswordUpdaterInterface $passwordUpdater,
                              CanonicalFieldsUpdater $canonicalFieldsUpdater,
                              EntityManagerInterface $entity_manager,
                              TransformedFinder $user_finder,
                              ProgramManager $program_manager,
                              UrlHelper $url_helper)
  {
    $this->user_finder = $user_finder;
    $this->url_helper = $url_helper;
    $this->program_manager = $program_manager;
    $this->entity_manager = $entity_manager;

    /** @var ObjectManager $om */
    $om = $entity_manager;
    parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, User::class);
  }

  public function decodeToken(string $token): array
  {
    try {
      $tokenParts = explode('.', $token);
      $tokenPayload = base64_decode($tokenParts[1], true);

      $payload = json_decode($tokenPayload, true);
      if (!is_array($payload)) {
        return [];
      }

      return json_decode($tokenPayload, true);
    } catch (Exception $e) {
      return [];
    }
  }

  public function isPasswordValid(UserInterface $user, string $password, PasswordEncoderInterface $encoder): bool
  {
    return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
  }

  public function getMappedUserData(array $raw_user_data): array
  {
    $response_data = [];

    foreach ($raw_user_data as $user) {
      $response_data[] = [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
        'project_count' => $this->program_manager->countPublicUserProjects($user->getId()),
        'profile' => $user,
      ];
    }

    return $response_data;
  }

  public function createUserFromScratch(array $userdata): ?User
  {
    $scratch_user_id = intval($userdata['id']);
    /** @var User|null $user */
    $user = $this->findUserBy(['scratch_user_id' => $scratch_user_id]);

    if (null === $user) {
      $username = $userdata['username'];
      $user = new User();
      $user->setScratchUserId($scratch_user_id);
      $user->setScratchUsername($username);
      $user->setEmail($username.'@localhost');
      $user->setPlainPassword(PasswordGenerator::generateRandomPassword());
      if ($avatar = $userdata['profile']['images']['90x90'] ?? null) {
        $user->setAvatar($avatar);
      }
      $joined = TimeUtils::dateTimeFromScratch($userdata['history']['joined']);
      if ($joined) {
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

  public function getUserIDList(): array
  {
    $associative_array = $this->entity_manager->createQueryBuilder()
      ->select('user.id as id')
      ->from('App\DB\Entity\User\User', 'user')
      ->getQuery()
      ->execute()
    ;

    return array_map(function ($value) { return $value['id']; }, $associative_array);
  }

  public function getActiveUserIDList(int $years): array
  {
    $result = $this->entity_manager->createQueryBuilder()
      ->select('user.id as id')
      ->from('App\DB\Entity\User\User', 'user')
      ->leftjoin('App\DB\Entity\Project\Program', 'project', Join::WITH, 'user.id = project.user')
      ->where('user.createdAt <= :date')
      ->setParameter('date', new DateTime("-{$years} years"))
      ->groupBy('user.id')
      ->having("COUNT(user.id) >= {$years}")
      ->getQuery()
      ->execute()
      ;

    return array_map(function ($value) { return $value['id']; }, $result);
  }

  protected function userSearchQuery(string $query): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word) {
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
