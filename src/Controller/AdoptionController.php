<?php

namespace App\Controller;

use App\Entity\Adoption;
use App\Entity\AdoptionRequest;
use App\Entity\Animal;
use App\Repository\AddressRepository;
use App\Repository\AdoptionRepository;
use App\Repository\AdoptionRequestRepository;
use App\Repository\AnimalRepository;
use App\Repository\UserRepository;
use DateTime;
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

class AdoptionController extends AbstractFOSRestController
{

    private $adoptionRepository;
    private $entityManager;
    private $serializer;
    private $animalRepo;
    private $adoptionRequestRepository;
    private $userRepo;
    private $addressRepository;


    public function __construct(
        AdoptionRequestRepository $adoptionRequestRepository,
        AdoptionRepository $repository,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        AnimalRepository $animalRepo,
        UserRepository $userRepo,
        AddressRepository $addressRepository
    ) {

        $this->adoptionRepository = $repository;
        $this->entityManager = $em;
        $this->serializer = $serializer;
        $this->animalRepo = $animalRepo;
        $this->userRepo = $userRepo;
        $this->addressRepository = $addressRepository;
        $this->adoptionRequestRepository = $adoptionRequestRepository;
    }

    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }

    /**
     * @Route("/api/adoption", name="adoption_list", methods = "GET")
     */
    public function findAll(Request $requst): Response
    {

        $page = $requst->query->get('page');
        $size = $requst->query->get('size');

        $espece = $requst->query->get('espece');
        $type = $requst->query->get('type');
        $age = $requst->query->get('age');
        $couleur = $requst->query->get('couleur');
        $ville = $requst->query->get('ville');
        $municipality = $requst->query->get('municipality');
        $taille = $requst->query->get('taille');
        $sexe = $requst->query->get('sexe');
        $user_id = $requst->query->get('user_id');
        $key = $requst->query->get('key');

        if (
            isset($espece) && strlen($espece) > 0 || isset($type) && strlen($type) > 0 ||
            isset($age) && strlen($age) > 0 || isset($couleur) && strlen($couleur) > 0 ||
            isset($taille) && strlen($taille) > 0 || isset($sexe) && strlen($sexe) > 0 ||
            isset($ville) && strlen($ville) > 0 || isset($municipality) && strlen($municipality) > 0 || isset($user_id) && strlen($user_id) > 0
        ) {
            $criteria = $this->createCriteria($espece, $type, $taille, $sexe, $ville, $municipality, $user_id, $age, $couleur);
            $page = isset($page) && $page > 0 ? $page : 1;
            $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
            $criteria['page'] = $page;
            $criteria['size'] = isset($size) ? $size : 6;
            $adoptions = $this->adoptionRepository->findWithCriteria($criteria, null, isset($size) ? $size :  8,  $offset);

            if (isset($key)) {
                $keys =  explode(",", $key);
                foreach ($adoptions as $adoption) {
                    foreach ($keys as $k) {
                        if ($k == 'user') {
                            $user =  $this->userRepo->find($adoption->getUserId());
                            if ($user->getAddressId() != null) {
                                $address = $this->addressRepository->find($user->getAddressId());
                                $user->setAddress($address);
                            }
                            $adoption->setUser($user);
                        } else if ($k == 'requests') {
                            $requests = $this->adoptionRequestRepository->findByAdoptionId($adoption->getId());
                            foreach ($requests as $ar) {
                                $adoption->addAdoptionRequest($ar);
                            }
                        }
                    }
                }
            }
            return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
        }

        // if not paginated
        if (!isset($page) && !isset($size)) {
            $adoptions = $this->adoptionRepository->findAll();
            if (isset($key)) {
                $keys =  explode(",", $key);
                foreach ($adoptions as $adoption) {
                    foreach ($keys as $k) {
                        if ($k == 'user') {
                            $user =  $this->userRepo->find($adoption->getUserId());
                            if ($user->getAddressId() != null) {
                                $address = $this->addressRepository->find($user->getAddressId());
                                $user->setAddress($address);
                            }
                            $adoption->setUser($user);
                        } else if ($k == 'requests') {
                            $requests = $this->adoptionRequestRepository->findByAdoptionId($adoption->getId());
                            foreach ($requests as $ar) {
                                $adoption->addAdoptionRequest($ar);
                            }
                        }
                    }
                }
            }
            return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
        }
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $adoptions = $this->adoptionRepository->findPaged($offset, isset($size) ? $size :  8);
        if (isset($key)) {
            $keys =  explode(",", $key);
            foreach ($adoptions as $adoption) {
                foreach ($keys as $k) {
                    if ($k == 'user') {
                        $user =  $this->userRepo->find($adoption->getUserId());
                        if ($user->getAddressId() != null) {
                            $address = $this->addressRepository->find($user->getAddressId());
                            $user->setAddress($address);
                        }
                        $adoption->setUser($user);
                    } else if ($k == 'requests') {
                        $requests = $this->adoptionRequestRepository->findByAdoptionId($adoption->getId());
                        foreach ($requests as $ar) {
                            $adoption->addAdoptionRequest($ar);
                        }
                    }
                }
            }
        }
        return new Response($this->handleCircularReference($adoptions), Response::HTTP_OK);
    }


    /**
     * @Route("/api/adoptions/count", name="count_adoption" , methods = "GET")
     */
    public function count(Request $requst): Response
    {


        $espece = $requst->query->get('espece');
        $type = $requst->query->get('type');
        $ville = $requst->query->get('ville');
        $age = $requst->query->get('age');
        $couleur = $requst->query->get('couleur');
        $municipality = $requst->query->get('municipality');
        $taille = $requst->query->get('taille');
        $sexe = $requst->query->get('sexe');
        $user_id = $requst->query->get('user_id');

        if (
            isset($espece) && strlen($espece) > 0 || isset($type) && strlen($type) > 0 ||
            isset($age) && strlen($age) > 0 || isset($couleur) && strlen($couleur) > 0 ||
            isset($taille) && strlen($taille) > 0 || isset($sexe) && strlen($sexe) > 0 ||
            isset($ville) && strlen($ville) > 0 || isset($municipality) && strlen($municipality) > 0 || isset($user_id) && strlen($user_id) > 0
        ) {
            $criteria = $this->createCriteria($espece, $type, $taille, $sexe, $ville, $municipality, $user_id, $age, $couleur);
            $size = $this->adoptionRepository->countFiltered($criteria);
            return $this->json($size, Response::HTTP_OK);
        }

        $size = $this->adoptionRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="get_adoption" , methods = "GET")
     */
    public function findOne($id, Request $requst): Response
    {
        $adoption = $this->adoptionRepository->find($id);
        if ($adoption == null) {
            return new Response('Adoption not found', Response::HTTP_NOT_FOUND);
        }
        $key = $requst->query->get('key');
        if (isset($key)) {
            $keys =  explode(",", $key);
            foreach ($keys as $k) {
                if ($k == 'user') {
                    $user =  $this->userRepo->find($adoption->getUserId());
                    if ($user->getAddressId() != null) {
                        $address = $this->addressRepository->find($user->getAddressId());
                        $user->setAddress($address);
                    }
                    $adoption->setUser($user);
                } else if ($k == 'requests') {
                    $requests = $this->adoptionRequestRepository->findByAdoptionId($adoption->getId());
                    foreach ($requests as $ar) {
                        $adoption->addAdoptionRequest($ar);
                    }
                }
            }
        }
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }


    /**
     * @Route("/api/adoptioncoupdecoeur/", name="coupdecoeur" , methods = "GET")
     */
    public function coupdecoeur(): Response
    {
        $adoption = $this->adoptionRepository->coupdecoeur();
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoptions/elasticsearch", name="elastic_search_adoption" , methods = "GET")
     */
    public function elasticSearch(Request $requst): Response
    {
        $key = $requst->query->get('keyword');
        $adoption = $this->adoptionRepository->elasticSearch($key);
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="delete_adoption" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        $adoption = $this->adoptionRepository->find($id);
        if ($adoption == null) {
            return new Response('Adoption not found', Response::HTTP_NOT_FOUND);
        }
        $arequests = $this->adoptionRequestRepository->findByAdoptionId($id);
        foreach ($arequests as $ar) {
            $this->entityManager->remove($ar);
        }
        $this->entityManager->remove($adoption->getAnimal());
        $this->entityManager->remove($adoption);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption/{id}", name="update_adoption" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $adoption = $this->adoptionRepository->find($id);
        if ($adoption == null) {
            return new Response('Adoption not found', Response::HTTP_NOT_FOUND);
        }
        $adoption = $this->adoptionDto($adoption, $data);
        $animal = $this->animalDto($adoption->getAnimal(), $data);
        $user = $this->getUser();
        $adoption->setUpdatedBy($user->getEmail());
        $animal->setUpdatedBy($user->getEmail());
        $adoption->setAnimal($animal);
        $this->entityManager->persist($adoption);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoption), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoption", name="create_adoption" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $adoption = $this->adoptionDto(new Adoption(), $data);
        $animal = $this->animalDto(new Animal(), $data);
        $user = $this->getUser();
        $adoption->setCreatedBy($user->getEmail());
        $adoption->setUserId($user->getId());
        $animal->setCreatedBy($user->getEmail());
        $adoption->setAnimal($animal);
        $this->entityManager->persist($adoption);
        $this->entityManager->flush();
        $adoption->setUser($user);
        return new Response($this->handleCircularReference($adoption), Response::HTTP_CREATED);
    }

    private function adoptionDto(Adoption $adoption, $data)
    {
        if (isset($data['title'])) {
            $adoption->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $adoption->setDescription($data['description']);
        }
        return $adoption;
    }

    private function animalDto(Animal $animal, $data)
    {
        if (isset($data['animal'])) {
            if (isset($data['animal']['type'])) {
                $animal->setType($data['animal']['type']);
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
        return $animal;
    }

    private function createCriteria($espece = null, $type = null, $taille = null, $sexe = null, $ville = null, $municipality = null, $user_id = null, $age, $couleur): array
    {

        $criteria = [];
        if (isset($espece) && strlen($espece) > 0) {
            $criteria['espece'] = $espece;
        }
        if (isset($type) && strlen($type) > 0) {
            $criteria['type'] = $type;
        }
        if (isset($age) && strlen($age) > 0) {
            $criteria['age'] = $age;
        }
        if (isset($couleur) && strlen($couleur) > 0) {
            $criteria['couleur'] = $couleur;
        }
        if (isset($taille) && strlen($taille) > 0) {
            $criteria['taille'] = $taille;
        }
        if (isset($sexe) && strlen($sexe) > 0) {
            $criteria['sexe'] = $sexe;
        }
        if (isset($ville) && strlen($ville) > 0) {
            $criteria['ville'] = $ville;
        }
        if (isset($municipality) && strlen($municipality) > 0) {
            $criteria['municipality'] = $municipality;
        }
        if (isset($user_id) && strlen($user_id) > 0) {
            $criteria['user_id'] = $user_id;
        }
        return $criteria;
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
     * @Route("/api/adoption/{id}/adopt", name="create_adoption_requests" , methods = "POST")
     */
    public function createAdoptionRequest($id): Response
    {
        $user = $this->getUser();
        $adoptionRequest = new AdoptionRequest();
        $adoptionRequest->setAdoptionId((int) $id);
        $adoptionRequest->setUserId($user->getId());
        $adoptionRequest->setCreatedBy($user->getEmail());
        $this->entityManager->persist($adoptionRequest);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($adoptionRequest), Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/adoptionRequest/{id}/accept", name="accept_adoption_request" , methods = "POST")
     */
    public function acceptAdoptionRequest($id): Response
    {
        $sql = 'update adoption_request set status = :status , updated_by = :usr , updated_at = :d where id = :id';
        $params['id'] = $id;
        $params['status'] = "ACCEPTED";
        $params['usr'] = $this->getUser()->getEmail();
        $params['d'] = date_format(new DateTime(), 'Y-m-d H:i:s');
        $this->entityManager->getConnection()->executeQuery($sql, $params);
        return new Response(Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/api/adoptionRequest/{id}/reject", name="reject_adoption_request" , methods = "POST")
     */
    public function rejectAdoptionRequest($id): Response
    {
        $sql = 'update adoption_request set status = :status , updated_by = :usr , updated_at = :d where id = :id';
        $params['id'] = $id;
        $params['status'] = "REJECTED";
        $params['usr'] = $this->getUser()->getEmail();
        $params['d'] = date_format(new DateTime(), 'Y-m-d H:i:s');
        $this->entityManager->getConnection()->executeQuery($sql, $params);
        return new Response(Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/api/adoptionRequest/{id}/cancel", name="cancel_adoption_request" , methods = "POST")
     */
    public function cancelAdoptionRequest($id): Response
    {
        $sql = 'update adoption_request set status = :status , updated_by = :usr , updated_at = :d where id = :id';
        $params['id'] = $id;
        $params['status'] = "CANCELED";
        $params['usr'] = $this->getUser()->getEmail();
        $params['d'] = date_format(new DateTime(), 'Y-m-d H:i:s');
        $this->entityManager->getConnection()->executeQuery($sql, $params);
        return new Response(Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/api/adoptionRequest/{id}/reopen", name="reopen_adoption_request" , methods = "POST")
     */
    public function reopenAdoptionRequest($id): Response
    {
        $sql = 'update adoption_request set status = :status , updated_by = :usr , updated_at = :d where id = :id';
        $params['id'] = $id;
        $params['status'] = "CREATED";
        $params['usr'] = $this->getUser()->getEmail();
        $params['d'] = date_format(new DateTime(), 'Y-m-d H:i:s');
        $this->entityManager->getConnection()->executeQuery($sql, $params);
        return new Response(Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/api/adoptionRequest/{id}", name="get_adoption_request" , methods = "GET")
     */
    public function getAdoptionRequest($id): Response
    {
        $adoptionRequest = $this->adoptionRequestRepository->find((int) $id);
        return new Response($this->handleCircularReference($adoptionRequest), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoptionRequest/user/{email}", name="get_user_adoption_requests" , methods = "GET")
     */
    public function getUserAdoptionRequest($email): Response
    {
        $adoptionRequests = $this->adoptionRequestRepository->findByCreatedBy($email);
        return new Response($this->handleCircularReference($adoptionRequests), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoptionRequest/user/{id}", name="get_adoption_adoption_requests" , methods = "GET")
     */
    public function getAdoptionAdoptionRequest($id): Response
    {
        $adoptionRequests = $this->adoptionRequestRepository->findByAdoptionId($id);
        return new Response($this->handleCircularReference($adoptionRequests), Response::HTTP_OK);
    }

    /**
     * @Route("/api/adoptionRequest/{email}/canadopot/{id}", name="can_user_adopt" , methods = "GET")
     */
    public function canAdopt($email, $id): Response
    {
        $adoptions = $this->adoptionRepository->findByCreatedBy($email);
        foreach ($adoptions as $a) {
            if ($a->getId() == $id) {
                return new Response($this->serializer->serialize(false, 'json'), Response::HTTP_OK);
            }
        }

        $adoptionRequests = $this->adoptionRequestRepository->findByAdoptionId($id);
        foreach ($adoptionRequests as $ar) {
            if ($ar->getCreatedBy() == $email) {
                return new Response($this->serializer->serialize(false, 'json'), Response::HTTP_OK);
            }
        }

        return new Response($this->serializer->serialize(true, 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("/api/animal/{id}", name="get_animal" , methods = "GET")
     */
    public function getAnimal($id): Response
    {
        $animal = $this->animalRepo->find((int) $id);
        return new Response($this->handleCircularReference($animal), Response::HTTP_OK);
    }
}
