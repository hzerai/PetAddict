<?php


namespace App\Controller;

use App\Entity\Veto;
use App\Repository\VetoRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
class VetoController  extends AbstractFOSRestController
{
    private $vetoRepository;
    private $entityManager;
    private $serializer;
    
    public function __construct(VetoRepository $repository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->vetoRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/veto", name="veto_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $page = $requst->query->get('page');
        $size = $requst->query->get('size');
        $phone = $requst->query->get('phone');
        $image = $requst->query->get('image');
        $adresse = $requst->query->get('adresse');
        $docteur = $requst->query->get('docteur');
        $description = $requst->query->get('description');
        $createdAt = $requst->query->get('createdAt');
        

        if (isset($adresse) || isset($phone) || isset($image) || isset($docteur) || isset($description) || isset($createdAt)) {
            $criteria = $this->createCriteria($adresse, $phone, $image, $docteur, $description, $createdAt);
            if (!isset($page) && !isset($size)) {
                $veto =  $this->vetoRepository->findBy($criteria);
                return $this->json($veto, Response::HTTP_OK);
            }
            $page = isset($page) ? ($page - 1) * $size : 1;
            $size = isset($size) ? $size : 8;
            $veto = $this->vetoRepository->findBy($criteria, null, $size, $size);
            return $this->json($veto, Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $veto = $this->vetoRepository->findAll();
            return $this->json($veto, Response::HTTP_OK);
        }
        $veto = $this->vetoRepository->findPaged($page, $size);
        return $this->json($veto, Response::HTTP_OK);
    }

     /**
     * @Route("/api/veto/count", name="count_veto" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->vetoRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }
     /**
     * @Route("/api/veto/{id}", name="get_veto" , methods = "GET")
     */
    public function findOne($id): Response
    {
        $veto = $this->vetoRepository->find($id);
        return $this->json($veto, Response::HTTP_OK);
    }


     /**
     * @Route("/api/veto/{id}", name="delete_veto" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $veto = $this->vetoRepository->find($id);
        $this->entityManager->remove($veto);
        $this->entityManager->flush();
        return $this->json($veto, Response::HTTP_OK);
    }

        /**
     * @Route("/api/veto/{id}", name="update_veto" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $veto = $this->vetoRepository->find($id);
        $veto = $this->vetoDto($veto, $data);
        $this->entityManager->persist($veto);
        $this->entityManager->flush();
        return $this->json($veto);
    }

    /**
     * @Route("/api/veto", name="create_veto" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $veto = $this->vetoDto(new Veto(), $data);
        $this->entityManager->persist($veto);
        $this->entityManager->flush();
        return $this->json($veto, Response::HTTP_CREATED);
    }

    private function vetoDto(Veto $veto, $data)
    {
        $veto->setDocteur($data['docteur']);
        $veto->setDescription($data['description']);
        $veto->setImage($data['image']);
        $veto->setAdresse($data['adresse']);
        $veto->setPhone($data['phone']);
        return $veto;
    }

    private function createCriteria($image, $docteur, $description, $creationAt, $phone, $adresse): array
    {
        $criteria = [];
        if (isset($image)) {
            $criteria['image'] = $image;
        }
        if (isset($docteur)) {
            $criteria['docteur'] = $docteur;
        }
        if (isset($description)) {
            $criteria['description'] = $description;
        }
        if (isset($phone)) {
            $criteria['phone'] = $phone;
        }
        if (isset($adresse)) {
            $criteria['adresse'] = $adresse;
        }
        if (isset($creationAt)) {
            $criteria['creationAt'] = $creationAt;
        }
        return $criteria;
    }
    /**
     * @Route("/api/vetos/elasticsearch", name="elastic_search_veto" , methods = "GET")
     */
    public function elasticSearch(Request $requst): Response
    {
        $key = $requst->query->get('keyword');
        $veto = $this->vetoRepository->elasticSearch($key);
        return new Response($this->handleCircularReference($veto), Response::HTTP_OK);
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
