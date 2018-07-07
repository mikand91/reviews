<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Review;
use AppBundle\Entity\Hotel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends Controller
{
    /**
     * @Route("{id}/today/review", name="daily_review")
     */
    public function reviewAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $hotel = $entityManager->getRepository(Hotel::class)->findOneBy(['id' => $id]);

        if(!$hotel) {

            throw $this->createNotFoundException('The hotel id was not found');

        }else{
            $queryBuilder = $entityManager->getRepository(Review::class)->createQueryBuilder('reviews');
            $queryBuilder
                ->where("reviews.hotel_id == $id")
                ->andwhere('reviews.crearedAt > :morning')
                ->setParameter('morning', new \DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
                ->addSelect('RAND() as HIDDEN rand')->orderBy('rand')
                ->setMaxResults(1);
            $review = $queryBuilder->getQuery()->getOneOrNullResult();

            if($review === null)
            {
                $message = "Sorry today You dont got any reviews";
                return $this -> render('Review/sorry.html.twig' ,
                    [
                        'message' => $message
                    ]);
            }else{
                return $this -> render('Review/review.html.twig' ,
                    [
                        'review' => $review
                    ]);
            }
        }
    }
}