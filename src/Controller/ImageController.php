<?php

namespace App\Controller;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 *@Route("/api/image/")
 */
class ImageController extends AbstractController
{

    private $imageRepo;
    private $serializer;
    private $em;

    public function __construct(ImageRepository $imageRepo, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->imageRepo = $imageRepo;
        $this->serializer = $serializer;
        $this->em = $em;
    }

    /**
     * @Route("{id}", name="get_user_image" , methods = "GET")
     */
    public function getImage($id): Response
    {
        $image = $this->imageRepo->findOneByName($id);
        if ($image == null) {
            return new Response('Image not found', Response::HTTP_NOT_FOUND);
        }
        $image->setBytes(stream_get_contents($image->getBytes()));
        return new Response($this->serializer->serialize($image, 'json'), Response::HTTP_OK);
    }

     /**
     * @Route("", name="get_all_image" , methods = "GET")
     */
    public function getAllImages(): Response
    {
        $images = $this->imageRepo->findAll();       
        foreach($images as $image){
            $image->setBytes(stream_get_contents($image->getBytes()));
        }
        return new Response($this->serializer->serialize($images, 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("{id}", name="update_image" , methods = "PUT")
     */
    public function updateImage($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getuser()->getEmail();
        $image = $this->imageRepo->find($id);
        $image->setBytes($data['bytes']);
        $image->setUpdatedBy($user);
        $this->em->persist($image);
        $this->em->flush();
        return new Response($this->serializer->serialize($image, 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("", name="upload_image" , methods = "POST")
     */
    public function uploadImage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->getuser()->getEmail();
        $image = new Image();
        $image->setBytes($data['bytes']);
        $image->setName($data['name']);
        if (isset($data['cover'])) {
            $image->setCover(boolval($data['cover']));
        }
        $image->setCreatedBy($user);
        $this->em->persist($image);
        $this->em->flush();
        return new Response($this->serializer->serialize($image, 'json'), Response::HTTP_CREATED);
    }


    /**
     * @Route("{id}", name="delete_image" , methods = "POST")
     */
    public function deleteImage($id): Response
    {
        $image = $this->imageRepo->find($id);
        $image->setName('__DELETED__' . $image->getName());
        $this->em->persist($image);
        $this->em->flush();
        $image->setBytes(stream_get_contents($image->getBytes()));
        return new Response($this->serializer->serialize($image, 'json'), Response::HTTP_OK);
    }
}
