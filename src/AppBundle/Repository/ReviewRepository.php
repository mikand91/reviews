<?php
namespace AppBundle\Repository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class ReviewRepository
 * @package AppBundle\Repository
 */
class ReviewRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param $hotelId
     * @param null $date
     * @return bool|string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findRandomReviewIdFromDate($hotelId , $date = null)
    {
        if(!$date)
        {
            $date = date('Y-m-d');
        }

        $sql = "SELECT review.id , review.createdAt , review.hotelId  
                FROM `review` AS review 
                JOIN 
                  (SELECT (RAND() * (SELECT MAX(review.id) 
                   FROM `review` 
                   WHERE review.createdAt >= DATE '{$date}' 
                   AND review.hotelId = $hotelId)) AS id) AS random 
                WHERE ( review.id >= random.id)
                AND review.hotelId = $hotelId
                AND review.createdAt >= DATE '{$date}'
                LIMIT 1
                ";
        $entityManager =$this->getEntityManager();
        $reviewId = $entityManager->getConnection()->query($sql)->fetchColumn();
        return $reviewId;
    }

    /**
     * @param $hotelId
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findRandomReviewFromToday($hotelId)
    {
        $cache = new FilesystemAdapter('reviews_cache');
        $cachedReview = $cache->getItem("$hotelId-review");
        if (!$cachedReview->isHit()) {
            $reviewId = $this->findRandomReviewIdFromDate($hotelId);
            $queryBuilder = $this->createQueryBuilder('reviews');
            $queryBuilder
                ->where("reviews.hotel = '{$hotelId}'")
                ->andwhere("reviews.id = '{$reviewId}'")
                ->setMaxResults(1);
            $review = $queryBuilder->getQuery()->getOneOrNullResult();
            $cachedReview->set($review);
            $cache->save($cachedReview);
        } else {
            $review = $cachedReview->get();
        }
        return $review;
    }
}