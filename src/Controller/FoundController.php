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
use App\Repository\UserRepository;


//Caching
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Cache\ItemInterface;
use App\Repository\CommentRepository;
use App\Entity\Comment;

use Symfony\Component\Serializer\SerializerInterface;

class FoundController extends AbstractFOSRestController
{

    private $commentRepository;
    private $FoundRepository;
    private $entityManager;
    private $serializer;
    private $animalRepo;
    private $userRepo;


    public function __construct(FoundRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer, AnimalRepository $animalRepo,  UserRepository $userRepo , CommentRepository $commentRepository)
    {
        $this->commentRepository=$commentRepository;
        $this->FoundRepository = $repository;
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
        $user =  $this->userRepo->findOneByEmail($found->getCreatedBy());
        $found->setUser($user);
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
        $found->setUpdatedBy($found->getCreatedBy());
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
        $user = $this->getUser();
        $found->setCreatedBy($user->getEmail());
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        $found->setUser($user);
        return new Response($this->handleCircularReference($found), Response::HTTP_CREATED);
    }

    private function foundDto(Found $found, $data)
    {
        
        if (isset($data['title'])) {
            $found->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $found->setDescription($data['description']);
        }
        if (isset($data['body'])){
            $found->setBody($data['body']);
            }
        if (isset($data['animal'])) {
            $animal = $found->getAnimal();
            if ($animal == null) {
                $animal = new Animal();
                $found->setAnimal($animal);
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

    /**
     * @Route("/api/found/{id}/addcommentfound", name="addcommentfound" , methods = "POST")
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
        $found= $this->FoundRepository->find($id);
        $found->addComment($comment);
        if ($found == null) {
            return new Response('This post was not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->persist($found);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }
      /**
     * @Route("/api/found/{id}/addcommentfound/{commentid}/replyfound", name="replyfound" , methods = "POST")
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
        $found= $this->FoundRepository->find($id);
        return new Response($this->handleCircularReference($found), Response::HTTP_OK);
    }

}