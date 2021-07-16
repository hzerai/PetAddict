<?php

namespace App\Controller;


use App\Repository\AdoptionRepository;

use App\Repository\FoundRepository;
use App\Repository\LostRepository;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractFOSRestController
{

    private $adoptionRepository;
    private $found;
    private $lost;



    public function __construct(
        AdoptionRepository $repository,
        FoundRepository $found,
        LostRepository $lost

    ) {

        $this->adoptionRepository = $repository;
        $this->found = $found;
        $this->lost = $lost;

    }


    /**
     * @Route("/api/home/soulssaved", name="soulssaved", methods = "GET")
     */
    public function soulssaved(): Response
    {
        $adopted = $this->adoptionRepository->countByStatus('ADOPTED');
        $found1 = $this->found->count('FOUND');
        $found2 = $this->lost->count('FOUND');

        return new Response($adopted + $found1 + $found2, Response::HTTP_OK);
    }

      /**
     * @Route("/api/home/lost", name="lost_friends", methods = "GET")
     */
    public function lost(): Response
    {
        $found1 = $this->found->count('CREATED');
        $found2 = $this->lost->count('CREATED');

        return new Response($found1 + $found2, Response::HTTP_OK);
    }

      /**
     * @Route("/api/home/waiting", name="waiting", methods = "GET")
     */
    public function waiting(): Response
    {
        $adopted = $this->adoptionRepository->countByStatus('CREATED');
        $found1 = $this->found->count('CREATED');
        $found2 = $this->lost->count('CREATED');

        return new Response($adopted + $found1 + $found2, Response::HTTP_OK);
    }
}
