<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
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
        CacheManager::setDefaultConfig(new ConfigurationOption([
            'path' => '/var/www/phpfastcache.com/dev/tmp', // or in windows "C:/tmp/"
        ]));

        // In your class, function, you can call the Cache
        $this->InstanceCache = CacheManager::getInstance('files');
    }




    /**
     * @Route("", name="get_user_notification" , methods = "GET")
     */
    public function getUserNotifications(): Response
    {
        $user_id = $this->getuser()->getEmail();
        $allUserNotifications = $this->notificationRepo->findByToUser($user_id);
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
        $this->em->persist($notification);
        $this->em->flush();
        $CachedString = $this->InstanceCache->getItem('NOTIF' . $this->clean($data['toUser']));
        if (!$CachedString->isHit() || $CachedString->isNull()) {
            $CachedString->set([$notification])->expiresAfter(3600);
            $this->InstanceCache->save($CachedString);
        } else {
            $array = $CachedString->get();
            array_push($array, $notification);
            $CachedString->set($array);
            $this->InstanceCache->save($CachedString);
        }
        return new Response($this->serializer->serialize($notification, 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/read", name="read_notification" , methods = "POST")
     */
    public function readNotification($id): Response
    {
        $notification = $this->notificationRepo->find($id);
        $notification->setVu(true);
        $this->em->persist($notification);
        $this->em->flush();
        return new Response($this->serializer->serialize($notification, 'json'), Response::HTTP_OK);
    }


    /**
     * @Route("/{key}/new", name="notifications_stream" , methods = "GET")
     */
    public function notificationsStream($key)
    {

        $CachedString = $this->InstanceCache->getItem('NOTIF' . $this->clean($key));
        if (!$CachedString->isHit() || $CachedString->isNull()) {
            return null;
        } else {
            $result = $this->serializer->serialize($CachedString->get(), 'json');
            $CachedString->set(null);
            $this->InstanceCache->save($CachedString);
            return new Response($result, Response::HTTP_OK);
        }
    }

    function clean($string)
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', json_encode($string)); // Removes special chars.
    }
}
