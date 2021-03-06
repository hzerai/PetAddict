<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

/**
 *@Route("/api/notification")
 */
class NotificationController extends AbstractController
{

    private $notificationRepo;
    private $serializer;
    private $em;
    private $InstanceCache;

    public function __construct(NotificationRepository $notificationRepo, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        $this->notificationRepo = $notificationRepo;
        $this->serializer = $serializer;
        $this->em = $em;
        // CacheManager::setDefaultConfig(new ConfigurationOption([
        //     'path' => 'App\Cache', 
        // ]));
        // $this->InstanceCache = CacheManager::getInstance('files');
    }

    /**
     * @Route("", name="get_user_notification" , methods = "GET")
     */
    public function getUserNotifications(): Response
    {
        $user_id = $this->getuser()->getEmail();
        $allUserNotifications = $this->notificationRepo->findNew($user_id);
        return new Response($this->serializer->serialize(array_reverse($allUserNotifications), 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("", name="send_notification" , methods = "POST")
     */
    public function sendNotification(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $fromUser = $this->getuser()->getEmail();
        $notification = new Notification();
        $notification->setBody($data['body']);
        $notification->setRoute($data['route']);
        $notification->setToUser($data['toUser']);
        $notification->setFromUser($fromUser);
        $notification->setCreatedBy($fromUser);
        $this->em->persist($notification);
        $this->em->flush();
        return new Response($this->serializer->serialize($notification, 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/read", name="read_notification" , methods = "POST")
     */
    public function readNotification($id): Response
    {
        $sql = 'update notification set vu = true where id = :id';
        $params['id'] = $id;
        $this->em->getConnection()->executeQuery($sql, $params);
        return new Response(Response::HTTP_OK);
    }
}
