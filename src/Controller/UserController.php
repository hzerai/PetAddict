<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Adoption;
use App\Entity\AdoptionRequest;
use App\Entity\User;
use App\Repository\AddressRepository;
use App\Repository\AdoptionRepository;
use App\Repository\AdoptionRequestRepository;
use App\Repository\UserRepository;
use DateTime;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;

//cache
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\JsonResponse;



class UserController extends AbstractController
{
    private $userRepository;
    private $entityManager;
    private $passwordEncoder;
    private $serializer;
    private $addressRepository;
    private $adoptionRepo;
    private $adoptionRequestRepo;
    private $verifyEmailHelper;
    private $mailer;


    public function __construct(
        VerifyEmailHelperInterface $helper, 
        MailerInterface $mailer,
        AddressRepository $addressRepository,
        UserRepository $repository,
        AdoptionRepository $adoptionRepo,
        AdoptionRequestRepository $adoptionRequestRepo,
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $passwordEncoder,
        SerializerInterface $serializer
    ) {
        $this->addressRepository = $addressRepository;
        $this->userRepository = $repository;
        $this->entityManager = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
        $this->adoptionRepo = $adoptionRepo;
        $this->adoptionRequestRepo = $adoptionRequestRepo;
        $this->verifyEmailHelper = $helper;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/api/users/status/{id}",name="staus_user",methods="PUT")
     */
    public function status($id): Response
    {
        $user = $this->userRepository->find($id);
        if ($user->getStatus() == true) {
            $user->setStatus(false);
        }
        else{
            $user->setStatus(true);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }

    /**
     * @Route("/api/login", name="login_user" , methods = "POST")
     */
    public function getTokenUser(Request $request,JWTTokenManagerInterface $JWTManager,RefreshTokenManagerInterface $refreshTokenManager)
    {
        // ...
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json("email or password is missing", Response::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->findOneByEmail($data['email']);
        if(!isset($user) || !$this->passwordEncoder->isPasswordValid($user,$data['password'])){
            return $this->json("Invalid credentials", Response::HTTP_UNAUTHORIZED);
        }
        if(!$user->getStatus()){
            return $this->json("your account is deactivated", Response::HTTP_UNAUTHORIZED);
        }
        if(!$user->getEmailVerified()){
            return $this->json("please verify your email", Response::HTTP_UNAUTHORIZED);
        }
        $valid = new DateTime('now');
        $valid->add(new DateInterval('P3D'));
        $refreshToken = $refreshTokenManager->create();
        $refreshToken->setUsername($user->getEmail());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($valid);

        $refreshTokenManager->save($refreshToken);
            return new JsonResponse(['token' => $JWTManager->create($user),'refreshToken' =>$refreshToken->getRefreshToken()]);
        //return new JsonResponse(['token' => $JWTManager->create($user)]);
    }

    /**
     * @Route("/api/users/count", name="count_user" , methods = "GET")
     */
    public function count(): Response
    {
        $size = $this->userRepository->count([]);
        return $this->json($size, Response::HTTP_OK);
    }

    /**
     * @Route("/api/users/{id}", name="get_user" , methods = "GET")
     */
    public function findOne($id, Request $requst): Response
    {
        $user = $this->userRepository->find($id);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        if ($user->getAddressId() != null) {
            $address = $this->addressRepository->find($user->getAddressId());
            $user->setAddress($address);
        }
        $user->setPassword('********');
        $key = $requst->query->get('key');
        $user->setAdoptions(new ArrayCollection());
        $user->setAdoptionRequests(new ArrayCollection());
        $user->setRecievedAdoptionRequests(new ArrayCollection());
        if (isset($key)) {
            $keys =  explode(",", $key);
            foreach ($keys as $k) {
                if ($k == 'adoptions') {
                    $adoptions = $this->adoptionRepo->findByUserId($user->getId());
                    foreach ($adoptions as $adoption) {
                        $user->addAdoption($adoption);
                        $recievedRequests = $this->adoptionRequestRepo->findByAdoptionId($adoption->getId());
                        foreach ($recievedRequests as $r) {
                            $requester = $this->userRepository->findOneById($r->getUserId());
                            $r->setUser($requester);
                            $r->setAdoption($adoption);
                            $user->addRecievedAdoptionRequest($r);
                            $adoption->addAdoptionRequest($r);
                        }
                    }
                } else if ($k == 'requests') {
                    $sentRequests = $this->adoptionRequestRepo->findByUserId($user->getId());
                    foreach ($sentRequests as $r) {
                        $user->addAdoptionRequest($r);
                        $adoption = $this->adoptionRepo->find($r->getAdoptionId());
                        $r->setAdoption($adoption);
                    }
                }
            }
        }
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }


    /**
     * @Route("/api/users", name="get_all_users" , methods = "GET")
     */
    public function findAll(Request $requst): Response
    {
        $page = $requst->query->get('page');
        $size = $requst->query->get('size');
        if (!isset($page) && !isset($size)) {
            $users = $this->userRepository->findAll();
            return new Response($this->handleCircularReference($users), Response::HTTP_OK);
        }
        $page = isset($page) && $page > 0 ? $page : 1;
        $offset = isset($size) ? ($page - 1) * $size : ($page - 1) * 8;
        $user = $this->userRepository->findPaged($offset, isset($size) ? $size :  8);
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);

    }

    /**
     * @Route("/api/users/addresses/find/all", name="get_all_addresses" , methods = "GET")
     */
    public function findAllAdresses(): Response
    {
        $addresses = $this->addressRepository->findAll();
        return new Response($this->handleCircularReference($addresses), Response::HTTP_OK);
    }

    /**
     * @Route("/api/users/addresses/{id}", name="get_one_address" , methods = "GET")
     */
    public function findOneAdress($id): Response
    {
        $address = $this->addressRepository->find($id);
        return new Response($this->handleCircularReference($address), Response::HTTP_OK);
    }


    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }

    /**
     * @Route("/api/users/user_by_email/{email}", name="get_user_by_email" , methods = "GET")
     */
    public function findByEmail($email, Request $requst): Response
    {
        $user = $this->userRepository->findOneByEmail($email);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        if ($user->getAddressId() != null) {
            $address = $this->addressRepository->find($user->getAddressId());
            $user->setAddress($address);
        }
        $user->setPassword('********');
        $key = $requst->query->get('key');
        $user->setAdoptions(new ArrayCollection());
        $user->setAdoptionRequests(new ArrayCollection());
        $user->setRecievedAdoptionRequests(new ArrayCollection());
        if (isset($key)) {
            $keys =  explode(",", $key);
            foreach ($keys as $k) {
                if ($k == 'adoptions') {
                    $adoptions = $this->adoptionRepo->findByUserId($user->getId());
                    foreach ($adoptions as $adoption) {
                        $user->addAdoption($adoption);
                        $recievedRequests = $this->adoptionRequestRepo->findByAdoptionId($adoption->getId());
                        foreach ($recievedRequests as $r) {
                            $requester = $this->userRepository->findOneById($r->getUserId());
                            $r->setUser($requester);
                            $r->setAdoption($adoption);
                            $user->addRecievedAdoptionRequest($r);
                            $adoption->addAdoptionRequest($r);
                        }
                    }
                } else if ($k == 'requests') {
                    $sentRequests = $this->adoptionRequestRepo->findByUserId($user->getId());
                    foreach ($sentRequests as $r) {
                        $user->addAdoptionRequest($r);
                        $adoption = $this->adoptionRepo->find($r->getAdoptionId());
                        $r->setAdoption($adoption);
                    }
                }
            }
        }
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }


    /**
     * @Route("/api/users/{id}", name="delete_user" , methods = "DELETE")
     */
    public function delete($id): Response
    {
        //TODO : Acctually disable
        $user = $this->userRepository->find($id);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        $user->setPassword("********");
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }

    /**
     * @Route("/api/users/{id}", name="update_user" , methods = "PUT")
     */
    public function update($id, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json("some parameters are missing", Response::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->find($id);
        if ($user == null) {
            return new Response('User not found', Response::HTTP_NOT_FOUND);
        }
        if (isset($data['address'])) {
            $address = $this->addressRepository->find($id);
            if ($address == null) {
                $address = new Address();
            }
            $address = $this->addressDto($address, $data);
            $this->entityManager->persist($address);
            $this->entityManager->flush();
            $user->setAddressId($address->getId());
            $user->setAddress($address);
        }
        $user = $this->userDto($user, $data);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new Response($this->handleCircularReference($user), Response::HTTP_OK);
    }

    /**
     * @Route("/api/users", name="create_user" , methods = "POST")
     */
    public function create(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json("email or password is missing", Response::HTTP_FORBIDDEN);
        }
        $user = $this->UserDto(new User(), $data);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $data['password']
            )
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $user->setPassword("********");
        return new Response($this->handleCircularReference($user), Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/users/register", name="register-user", methods = "POST")
     */
    public function register(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json("email or password is missing", Response::HTTP_FORBIDDEN);
        }
        $user = $this->UserDto(new User(), $data);
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $data['password']
            )
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $user->setPassword("********");
    
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
                'registration_confirmation_route',
                $user->getId(),
                $user->getEmail(),
               ['id' => $user->getId()]
            );
        $email = new TemplatedEmail();
        $email->from("petaddictpi@gmail.com");
        $email->to($user->getEmail());
        $email->htmlTemplate('confirmation_email.html.twig');
        $email->context(['signedUrl' => $signatureComponents->getSignedUrl(),'userid'=>$user->getId(),'useremail'=>$user->getEmail()]);
        
        $this->mailer->send($email);
        return new Response($this->handleCircularReference($user), Response::HTTP_CREATED);

        // generate and return a response for the browser
    }

    
    /**
     * @Route("/api/users/verify", name="registration_confirmation_route")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $id = $request->get('id');
        // Verify the user id exists and is not null
       if (null === $id) {
            return $this->json("No id Found", Response::HTTP_FORBIDDEN);
        }
        $user = $this->userRepository->find($id);
        // Ensure the user exists in persistence
        if (null === $user) {
            return $this->json("No User Found ", Response::HTTP_FORBIDDEN);
        }

        // Do not get the User's Id or Email Address from the Request object
        try {
            $this->verifyEmailHelper->validateEmailConfirmation($request->getUri(), $user->getId(), $user->getEmail());
        }
        catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('verify_email_error', $e->getReason());

            return $this->json("verification failed", Response::HTTP_FORBIDDEN);
        }
        

        // Mark your user as verified. e.g. switch a User::verified property to true
        $user->setEmailVerified(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->addFlash('success', 'Your e-mail address has been verified.');

        return new Response("Your e-mail address has been verified", Response::HTTP_OK);
    }



    private function userDto(User $user, $data)
    {
        $user->setEmail($data['email']);
        $user->eraseCredentials();
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }
        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }
        if (isset($data['phoneNumber'])) {
            $user->setPhoneNumber($data['phoneNumber']);
        }
        if (isset($data['about'])) {
            $user->setAbout($data['about']);
        }
        if (isset($data['sexe'])) {
            $user->setSexe($data['sexe']);
        }
        if (isset($data['birthDate'])) {
            $user->setBirthDate(
                date_create_from_format('Y-m-d', $data['birthDate'])
            );
        }
        if (isset($data['isMailPublic'])) {
            $user->setIsMailPublic($data['isMailPublic']);
        }
        if (isset($data['isPhonePublic'])) {
            $user->setIsPhonePublic($data['isPhonePublic']);
        }
        if (isset($data['allowNotification'])) {
            $user->setAllowNotification($data['allowNotification']);
        }
        if (isset($data['favoriteAnimal'])) {
            $user->setFavoriteAnimal($data['favoriteAnimal']);
        }
        return $user;
    }

    private function addressDto(Address $address, $data)
    {
        if (isset($data['address'])) {
            if ($address == null) {
                $address = new Address();
            }
            if (isset($data['address']['ville'])) {
                $address->setVille($data['address']['ville']);
            }
            if (isset($data['address']['municipality'])) {
                $address->setMunicipality($data['address']['municipality']);
            }
            if (isset($data['address']['details'])) {
                $address->setDetails($data['address']['details']);
            }
        }
        return $address;
    }

    function handleCircularReference($objectToSerialize)
    {
        // Serialize your object in Json
        $jsonObject = $this->serializer->serialize($objectToSerialize, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        return $jsonObject;
    }

    

     




}
