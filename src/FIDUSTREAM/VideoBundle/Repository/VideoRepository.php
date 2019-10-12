<?php

namespace FIDUSTREAM\VideoBundle\Repository;

use Doctrine\ORM\EntityRepository;
/**
 * VideoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VideoRepository extends EntityRepository
{
    public function contains($text, $level)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $queryBuilder->where('v.title LIKE :text')
                     ->orWhere('v.tags LIKE :text')
                     ->orWhere('v.description LIKE :text')
                     ->andWhere('v.currentPlace = :published')
                     ->andWhere('v.authenticationLevel >= :level')
                     ->setParameter('published', "published")
                     ->setParameter('level', $level )
                     ->setParameter('text', '%'.$text.'%');
        return $queryBuilder->getQuery()->getResult();
    }

    public function latestVideo($userLevel)
    {
        $queryBuilder = $this->createQueryBuilder('v');
        $queryBuilder->where('v.authenticationLevel >= :userLevel')
                     ->andWhere('v.currentPlace = :published')
                     ->orderBy('v.publicationDate', 'DESC')
                     ->setFirstResult(0)
                     ->setMaxResults(10)
                     ->setParameter('published', "published")
                     ->setParameter('userLevel', $userLevel );
        return $queryBuilder->getQuery()->getResult();
    }

    public function findVideoToValidate($moderatorId)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $queryBuilder->where('v.validator = :moderator')
                     ->andWhere('v.currentPlace = :validation')
                     ->setParameter('moderator', $moderatorId)
                     ->setParameter('validation', "validation");
        return $queryBuilder->getQuery()->getResult();
    }
    public function findUploadedVideo($contributorId)
    {
        $queryBuilder = $this->createQueryBuilder('v');

        $queryBuilder->where('v.contributor = :id')
                     ->setParameter('id', $contributorId);
        return $queryBuilder->getQuery()->getResult();
    }
}