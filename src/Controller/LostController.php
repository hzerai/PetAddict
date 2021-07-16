<?php

namespace App\Controller;

use App\Entity\Lost;
use App\Entity\Animal;
use App\Repository\LostRepository;
use App\Repository\AnimalRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\CommentRepository;
use App\Entity\Comment;

//Caching
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;


use Symfony\Component\Serializer\SerializerInterface;

class LostController extends AbstractFOSRestController
{

    private $commentRepository;
    private $LostRepository;
    private $entityManager;
    private $serializer;
    private $animalRepo;
    private $userRepo;


    public function __construct(LostRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer, AnimalRepository $animalRepo, UserRepository $userRepo , CommentRepository $commentRepository)
    {
        $this->commentRepository=$commentRepository;
        $this->LostRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        $this->animalRepo = $animalRepo;
        $this->userRepo = $userRepo;
    }
    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }
    /**
     * @Route("/api/lost", name="lost_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {

        $page = $requst->query->get('page');
        $size = $requst->query->get('size');  
        $status = $requst->query->get('status');  
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $losts = $this->LostRepository->findPaged($offset, isset($size) ? $size :  8, $status);
        return new Response($this->handleCircularReference($losts), Response::HTTP_OK);
    }
    
    /**
     * @Route("/api/userlost", name="lost_list_user", methods = "GET")
     */
    public function findAllUser(): Response
    {

        $email=$this->getUser()->getEmail();
        $losts = $this->LostRepository->findByCreatedBy($email);
        return new Response($this->handleCircularReference($losts), Response::HTTP_OK);
    }


    /**
     * @Route("/api/lost/count", name="count_lost" , methods = "GET")
     */
    public function count(Request $request): Response
    {
        $status = $request->query->get('status');  
        $size = $this->LostRepository->count($status);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="get_lost" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $lost = $this->LostRepository->find($id);
        if ($lost == null) {
            return new Response('Post lost not found', Response::HTTP_NOT_FOUND);
        }
        $user =  $this->userRepo->findOneByEmail($lost->getCreatedBy());
        $lost->setUser($user);
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="delete_lost" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $lost = $this->LostRepository->find($id);
        if ($lost == null) {
            return new Response('Post lost not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($lost);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost/{id}", name="update_lost" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $lost = $this->LostRepository->find($id);
        if ($lost == null) {
            return new Response('Lost not found', Response::HTTP_NOT_FOUND);
        }
        $lost = $this->foundDto($lost, $data);
        $lost->setUpdatedBy($lost->getCreatedBy());
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

    /**
     * @Route("/api/lost", name="create_lost" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $lost = $this->foundDto(new Lost(), $data);
        $user = $this->getUser();
        $lost->setCreatedBy($user->getEmail());
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        $lost->setUser($user);
        return new Response($this->handleCircularReference($lost), Response::HTTP_CREATED);
    }

    private function foundDto(Lost $lost, $data)
    {
        $lost->setUser($this->getUser());
        if (isset($data['title'])) {
            $lost->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $lost->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $lost->setStatus($data['status']);
        }
        if (isset($data['animal'])) {
            $animal = $lost->getAnimal();
            if ($animal == null) {
                $animal = new Animal();
                $lost->setAnimal($animal);
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
        return $lost;
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
    /**
     * @Route("/api/lost/{id}/addcommentlost", name="addcommentlost" , methods = "POST")
     */
    public function addComment($id,Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $body=$data["body"];
        $comment=new Comment(); 
        $comment->setBody($body);
        $user=$this->getUser();
        $comment->setCreatedBy($user->getEmail());
        $comment->setUserFullName($user->getFirstName()." ".$user->getLastName());
        $lost= $this->LostRepository->find($id);
        $lost->addComment($comment);
        if ($lost == null) {
            return new Response('This post was not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->persist($lost);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }
      /**
     * @Route("/api/lost/{id}/addcommentlost/{commentid}/replylost", name="replylost" , methods = "POST")
     */
    public function addReply($id,$commentid ,Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $body=$data["body"];
        $commentreply=new Comment(); 
        $commentreply->setBody($body);
        $user=$this->getUser();
        $commentreply->setCreatedBy($user->getEmail());
        $commentreply->setUserFullName($user->getFirstName()." ".$user->getLastName());

        $comment= $this->commentRepository->find($commentid);
        $comment->addComment($commentreply);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
        $lost= $this->LostRepository->find($id);
        return new Response($this->handleCircularReference($lost), Response::HTTP_OK);
    }

}