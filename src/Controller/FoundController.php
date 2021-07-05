<?php

namespace App\Controller;

use App\Entity\Found;
use App\Entity\Animal;
use App\Repository\FoundRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


//Caching
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;


use Symfony\Component\Serializer\SerializerInterface;

class FoundController extends AbstractFOSRestController
{

    private $FoundRepository;
    private $entityManager;
    private $serializer;
    private $animalRepo;


    public function __construct(FoundRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer, AnimalRepository $animalRepo)
    {
        $this->FoundRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        $this->animalRepo = $animalRepo;
    }
    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }
    /**
     * @Route("/api/found", name="found_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {

        $page = $requst->query->get('page');
        $size = $requst->query->get('size');  
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $founds = $this->FoundRepository->findPaged($offset, isset($size) ? $size :  8);
        return new Response($this->handleCircularReference($founds), Response::HTTP_OK);
    }


    /**
     * @Route("/api/found/count", name="count_found" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->FoundRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="get_found" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $found = $this->FoundRepository->find($id);
        if ($found == null) {
            return new Response('Post found not found', Response::HTTP_NOT_FOUND);
        }
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="delete_found" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $found = $this->FoundRepository->find($id);
        if ($found == null) {
            return new Response('Post found not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

    /**
     * @Route("/api/found/{id}", name="update_found" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $found = $this->FoundRepository->find($id);
        if ($found == null) {
            return new Response('Found not found', Response::HTTP_NOT_FOUND);
        }
        $found = $this->foundDto($found, $data);
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

    /**
     * @Route("/api/found", name="create_found" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $found = $this->foundDto(new Found(), $data);
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_CREATED);
    }

    private function foundDto(Found $found, $data)
    {
        $found->setUser($this->getUser());
        if (isset($data['title'])) {
            $found->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $found->setDescription($data['description']);
        }
        if (isset($data['animal'])) {
            $animal = $found->getAnimal();
            if ($animal == null) {
                $animal = new Animal();
                $animal->setFound($found);
            }
            if (isset($data['animal']['age'])) {
                $animal->setAge($data['animal']['age']);
            }
            if (isset($data['animal']['couleur'])) {
                $animal->setCouleur($data['animal']['couleur']);
            }
            if (isset($data['animal']['espece'])) {
                $animal->setEspece($data['animal']['espece']);
            }
            if (isset($data['animal']['taille'])) {
                $animal->setTaille($data['animal']['taille']);
            }
            if (isset($data['animal']['sexe'])) {
                $animal->setSexe($data['animal']['sexe']);
            }
            if (isset($data['animal']['nom'])) {
                $animal->setNom($data['animal']['nom']);
            }
            
        }
        return $found;
    }

    function handleCircularReference($objectToSerialize)
    {
        $jsonObject = $this->serializer->serialize($objectToSerialize, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return $jsonObject;
    }

}