<?php

namespace App\Controller;
use App\Entity\Found;
use App\Repository\FoundRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;


class FoundController extends AbstractFOSRestController
{
    private $foundRepository;
    private $entityManager;

    public function __construct(FoundRepository $repository, EntityManagerInterface $em)
    {
        $this->foundRepository = $repository;
        $this->entityManager = $em;
    }

    /**
     * @Route("/api/found", name="found_list", methods = "GET")
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
                $founds =  $this->foundRepository->findBy($criteria);
                return $this->json($founds, Response::HTTP_OK);
            }
            $page = isset($page) && $page > 0 ? $page : 1;
            $offset = isset($size) ? ($page - 1) * $size : 0;
            $founds = $this->foundRepository->findBy($criteria, null, isset($size) ? $size :  8,  $offset);
            return $this->json($founds, Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $founds = $this->foundRepository->findAll();
            return $this->json($founds, Response::HTTP_OK);
        }
        $founds = $this->foundRepository->findPaged($page, $size);
        return $this->json($founds, Response::HTTP_OK);
    }


    /**
     * @Route("/api/found/count", name="count_found" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->foundRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="get_found" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $found = $this->foundRepository->find($id);
        return $this->json($found, Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="delete_found" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $found = $this->foundRepository->find($id);
        $this->entityManager->remove($lost);
        $this->entityManager->flush();
        return $this->json($found, Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="update_found" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $found = $this->foundRepository->find($id);
        $found = $this->adoptionDto($found, $data);
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        return $this->json($found);
    }

    /**
     * @Route("/api/found", name="create_found" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $found = $this->List(new Found(), $data);
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return $this->json($found, Response::HTTP_CREATED);
    }

    private function List(Found $found, $data)
    {
        $found->setTitle($data['title']);
        $found->setDescription($data['description']);
        $found->setAnimal($data['animal']);
        return $found;
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
