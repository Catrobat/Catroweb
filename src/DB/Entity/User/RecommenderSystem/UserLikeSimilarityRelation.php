<?php

declare(strict_types=1);

namespace App\DB\Entity\User\RecommenderSystem;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\RecommenderSystem\UserLikeSimilarityRelationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_like_similarity_relation')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: UserLikeSimilarityRelationRepository::class)]
class UserLikeSimilarityRelation extends AbstractSimilarityRelation
{
  #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY', inversedBy: 'relations_of_similar_users_based_on_likes')]
  protected User $first_user;

  #[ORM\ManyToOne(targetEntity: User::class, fetch: 'LAZY', inversedBy: 'reverse_relations_of_similar_users_based_on_likes')]
  protected User $second_user;
}
