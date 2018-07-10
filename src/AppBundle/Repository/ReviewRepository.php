<?php

namespace AppBundle\Repository;
use Doctrine\ORM\Mapping;


class ReviewRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param $hotelId
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRandomReviewFromToday($hotelId)
    {
        $date = date('Y-m-d');
        $sql ="SELECT review.id
               FROM `review` AS review 
               JOIN (SELECT (RAND() * (SELECT MAX(id) FROM `review`)) AS id) AS random
               WHERE review.createdAt >= $date
               AND review.hotelId = $hotelId
               AND  review.id >= random.id
               ORDER BY review.id ASC
               LIMIT 1";
        $entityManager =$this->getEntityManager();
        $reviewId = $entityManager->getConnection()->query($sql)->fetchColumn();
        $queryBuilder = $this->createQueryBuilder('reviews');
        $queryBuilder
            ->where("reviews.hotel = '{$hotelId}'")
            ->andwhere("reviews.id = '{$reviewId}'")
            ->setMaxResults(1);
        $review = $queryBuilder->getQuery()->getOneOrNullResult();
        return $review;
    }
}
