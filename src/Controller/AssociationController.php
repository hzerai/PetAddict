<?php


namespace App\Controller;

use App\Entity\Association;
use App\Repository\AssociationRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;


class AssociationController  extends AbstractFOSRestController
{
    private $associationRepository;
    private $entityManager;
    private $serializer;
    
    public function __construct(AssociationRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->associationRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        
    }

    /**
     * @Route("/api/association", name="association_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $page = $requst->query->get('page');
        $size = $requst->query->get('size');
        $image = $requst->query->get('image');
        $title = $requst->query->get('title');
        $adresse = $requst->query->get('adresse');
        $phone = $requst->query->get('phone');
        $description = $requst->query->get('description');
        $createdAt = $requst->query->get('createdAt');
        

        if ( isset($adresse) || isset($phone) || isset($image) || isset($title) || isset($description) || isset($createdAt)) {
            $criteria = $this->createCriteria($image, $title, $description, $createdAt, $adresse ,$phone);
            if (!isset($page) && !isset($size)) {
                $association =  $this->associationRepository->findBy($criteria);
                return $this->json($association, Response::HTTP_OK);
            }
            $page = isset($page) ? ($page - 1) * $size : 1;
            $size = isset($size) ? $size : 8;
            $association = $this->associationRepository->findBy($criteria, null, $size, $size);
            return $this->json($association, Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $association = $this->associationRepository->findAll();
            return $this->json($association, Response::HTTP_OK);
        }
        $association = $this->associationRepository->findPaged($page, $size);
        return $this->json($association, Response::HTTP_OK);
    }

     /**
     * @Route("/api/association/count", name="count_association" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->associationRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }
     /**
     * @Route("/api/association/{id}", name="get_association" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $association = $this->associationRepository->find($id);
        return $this->json($association, Response::HTTP_OK);
    }


     /**
     * @Route("/api/association/{id}", name="delete_association" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $association = $this->associationRepository->find($id);
        $this->entityManager->remove($association);
        $this->entityManager->flush();
        return $this->json($association, Response::HTTP_OK);
    }

        /**
     * @Route("/api/association/{id}", name="update_association" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $association = $this->associationRepository->find($id);
        $association = $this->associationDto($association, $data);
        $this->entityManager->persist($association);
        $this->entityManager->flush();
        return $this->json($association);
    }

    /**
     * @Route("/api/association", name="create_association" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $association = $this->associationDto(new Association(), $data);
        $this->entityManager->persist($association);
        $this->entityManager->flush();
        return $this->json($association, Response::HTTP_CREATED);
    }

    private function associationDto(Association $association, $data)
    {
        $association->setTitle($data['title']);
        $association->setDescription($data['description']);
        $association->setImage($data['image']);
        $association->setAdresse($data['adresse']);
        $association->setPhone($data['phone']);
        return $association;
    }

    private function createCriteria($image, $title, $description, $creationAt, $phone, $adresse): array
    {
        $criteria = [];
        if (isset($image)) {
            $criteria['image'] = $image;
        }
        if (isset($title)) {
            $criteria['title'] = $title;
        }
        if (isset($phone)) {
            $criteria['phone'] = $phone;
        }
        if (isset($adresse)) {
            $criteria['adresse'] = $adresse;
        }
        
        
        if (isset($description)) {
            $criteria['description'] = $description;
        }
        if (isset($creationAt)) {
            $criteria['creationAt'] = $creationAt;
        }
        return $criteria;
    }
    /**
     * @Route("/api/associations/elasticsearch", name="elastic_search_association" , methods = "GET")
     */
    public function elasticSearch(Request $requst): Response
    {
        $key = $requst->query->get('keyword');
        $association = $this->associationRepository->elasticSearch($key);
        return new Response($this->handleCircularReference($association), Response::HTTP_OK);
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