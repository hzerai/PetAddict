<?php

namespace App\Controller;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\RequestStack;

use App\Entity\Post;
use App\Entity\Comment;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;

use Symfony\Component\Serializer\SerializerInterface;

class PostController extends AbstractFOSRestController
{
    private $commentRepository;
    private $postRepository;
    private $entityManager;
    private $serializer;
    public function __construct(PostRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer,CommentRepository $commentRepository)
    {
        $this->commentRepository=$commentRepository;
        $this->postRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
    }

      /**
     * @Route("/api/post", name="create_post" , methods = "POST")
     */
    public function create(Request $request): Response
    {

        $entityManager = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);

        $post = $this->postDto(new Post(), $data);
        $user=$this->getUser();
        $post->setCreatedBy($user->getEmail());
        $post->setUserId($user->getId());
        $post->setUserFullName($user->getFirstName()." ".$user->getLastName());
        $entityManager->persist($post);
        $entityManager->flush();
        return new Response($this->handleCircularReference($post), Response::HTTP_CREATED);
    }
    private function postDto(Post $post, $data)
    { 
    if (isset($data['title'])){
            $post->setTitle($data['title']);
        }
        if (isset($data['body'])){
        $post->setBody($data['body']);
        }
        return $post;
    }
     
       /**
     * @Route("/api/post/{id}", name="update_post" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
       
        $data = json_decode($request->getContent(), true);
        $post = $this->postRepository->find($id);
        if ($post== null) {
            return new Response('Post not found', Response::HTTP_NOT_FOUND);
        }
        $post = $this->postDto($post, $data);
        $post->setUpdatedBy($this->getUser()->getEmail());
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($post), Response::HTTP_OK);
    }
      
       /**
     * @Route("/api/post/{id}", name="delete_post" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $post = $this->postRepository->find($id);
        if ($post == null) {
            return new Response('Post not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($post), Response::HTTP_OK);
    }
       /**
     * @Route("/api/posts/count", name="count_post" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->postRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }
     /**
     * @Route("/api/post", name="post_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $page = $requst->query->get('page');
        $size = $requst->query->get('size');
        if (!isset($page) && !isset($size)) {
            $posts = $this->postRepository->findAll();
            return new Response($this->handleCircularReference($posts), Response::HTTP_OK);
        }
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $posts = $this->postRepository->findPaged($offset,$size);
      return new Response($this->handleCircularReference($posts), Response::HTTP_OK);
    }

    /**
     * @Route("/api/post/{id}", name="get_post" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $post= $this->postRepository->find($id);
        if ($post == null) {
            return new Response('Post not found', Response::HTTP_NOT_FOUND);
        }
        return new Response($this->handleCircularReference($post), Response::HTTP_OK);
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
     * @Route("/api/post/{id}/addcomment", name="addcomment" , methods = "POST")
     */
    public function addComment($id,Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $body=$data["body"];
        $comment=new Comment(); 
        $comment->setBody($body);
        $user=$this->getUser();
        $comment->setCreatedBy($user->getEmail());
        $comment->setUserId($user->getId());
        $comment->setUserFullName($user->getFirstName()." ".$user->getLastName());
        $post= $this->postRepository->find($id);
        $post->addComment($comment);
        if ($post == null) {
            return new Response('Post not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->persist($post);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($post), Response::HTTP_OK);
    }
      /**
     * @Route("/api/post/{id}/addcomment/{commentid}/reply", name="reply" , methods = "POST")
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
        $post= $this->postRepository->find($id);
        return new Response($this->handleCircularReference($post), Response::HTTP_OK);
    }
}
