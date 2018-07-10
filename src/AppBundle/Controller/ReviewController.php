<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Review;
use AppBundle\Entity\Hotel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController extends Controller
{
    /**
     * @param $id
     * @return Response
     * @Route("{id}/today/review", name="daily_review")
     */
    public function reviewAction($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $hotel = $entityManager->getRepository(Hotel::class)->findOneBy(['id' => $id]);

        if(!$hotel) {

            throw $this->createNotFoundException('The hotel id was not found');

        }else{

            $review = $entityManager->getRepository(Review::class)->findRandomReviewFromToday($id);
            if($review === null)
            {
                $message = "Sorry, today You dont got any reviews";
                $response =  $this -> render('Review/sorry.html.twig' ,
                    [
                        'message' => $message
                    ]);
            }else{
                $response =  $this -> render('Review/review.html.twig' ,
                    [
                        'review' => $review
                    ]);
            }
            $response->setSharedMaxAge(300);
            return $response;
        }
    }
}