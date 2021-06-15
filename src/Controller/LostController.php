<?php

namespace App\Controller;
use App\Entity\Lost;
use App\Repository\LostRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class LostController extends AbstractFOSRestController
{
    private $lostRepository;
    private $entityManager;

    public function __construct(LostRepository $repository, EntityManagerInterface $em)
    {
        $this->lostRepository = $repository;
        $this->entityManager = $em;
    }

    /**
     * @Route("/api/lost", name="lost_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $page = $requst->query->get('page');
        $size = $requst->query->get('size');

        $title = $requst->query->get('title');
        $animal = $requst->query->get('animal');
        $createdAt = $requst->query->get('createdAt');
        $description = $requst->query->get('description');

        if (isset($title) || isset($animal) || isset($description) || isset($createdAt)) {
            $criteria = $this->createCriteria($title, $description, $createdAt, $animal);
            if (!isset($page) && !isset($size)) {
                $losts =  $this->lostRepository->findBy($criteria);
                return $this->json($losts, Response::HTTP_OK);
            }
            $page = isset($page) && $page > 0 ? $page : 1;
            $offset = isset($size) ? ($page - 1) * $size : 0;
            $losts = $this->lostRepository->findBy($criteria, null, isset($size) ? $size :  8,  $offset);
            return $this->json($losts, Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $losts = $this->lostRepository->findAll();
            return $this->json($losts, Response::HTTP_OK);
        }
        $losts = $this->lostRepository->findPaged($page, $size);
        return $this->json($losts, Response::HTTP_OK);
    }


    /**
     * @Route("/api/losts/count", name="count_lost" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->lostRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="get_lost" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $lost = $this->lostRepository->find($id);
        return $this->json($lost, Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="delete_lost" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $lost = $this->lostRepository->find($id);
        $this->entityManager->remove($lost);
        $this->entityManager->flush();
        return $this->json($lost, Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="update_lost" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $lost = $this->lostRepository->find($id);
        $lost = $this->adoptionDto($lost, $data);
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return $this->json($lost);
    }

    /**
     * @Route("/api/lost", name="create_lost" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $lost = $this->List(new Lost(), $data);
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return $this->json($lost, Response::HTTP_CREATED);
    }

    private function List(Lost $lost, $data)
    {
        $lost->setTitle($data['title']);
        $lost->setDescription($data['description']);
        $lost->setAnimal($data['animal']);
        return $lost;
    }

    private function createCriteria($title, $description, $creationAt, $animal): array
    {
        $criteria = [];
        if (isset($title)) {
            $criteria['title'] = $title;
        }
        if (isset($description)) {
            $criteria['description'] = $description;
        }
        if (isset($creationAt)) {
            $criteria['creationAt'] = $creationAt;
        }
        if (isset($animal)) {
            $criteria['animal'] = $animal;
        }
        return $criteria;
    }
}
