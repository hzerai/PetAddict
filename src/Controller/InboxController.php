<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;

/**
 *@Route("/api/inbox")
 */
class InboxController extends AbstractController
{

    private $messageRepo;
    private $serializer;
    private $em;
    private $InstanceCache;
    public function __construct(MessageRepository $messageRepo, SerializerInterface $serializer, EntityManagerInterface $em)
    {
        CacheManager::setDefaultConfig(new ConfigurationOption([
            'path' => 'App\Cache', 
        ]));

        // In your class, function, you can call the Cache
        $this->InstanceCache = CacheManager::getInstance('files');
        $this->messageRepo = $messageRepo;
        $this->serializer = $serializer;
        $this->em = $em;
    }




    /**
     * @Route("", name="get_user_inbox" , methods = "GET")
     */
    public function getUserInbox(): Response
    {
        $user_id = $this->getuser()->getEmail();
        $allUserMessages = $this->messageRepo->findByUser($user_id);
        $inbox = [];
        $inbox['messagesByUser'] = [];
        foreach ($allUserMessages as $message) {
            $theOtherUser = $message->getFromUser() != $user_id ? $message->getFromUser() : $message->getToUser();
            if (!isset($inbox['messagesByUser'][$theOtherUser])) {
                $inbox['messagesByUser'][$theOtherUser] = [$message];
            } else {
                array_push($inbox['messagesByUser'][$theOtherUser], $message);
            }
        }
        return new Response($this->serializer->serialize($inbox, 'json'), Response::HTTP_OK);
    }


    /**
     * @Route("", name="get_user_unread_messages" , methods = "PUT")
     */
    public function getUserNewMessages(): Response
    {
        $user_id = $this->getuser()->getEmail();
        $inbox = [];
        $allUserMessages = $this->messageRepo->findUserNewMessages($user_id);
        foreach ($allUserMessages as $message) {
            $theOtherUser = $message->getFromUser();
            if ($inbox[$theOtherUser] == null) {
                $inbox[$theOtherUser] = [$message];
            } else {
                array_push($inbox[$theOtherUser], $message);
            }
        }
        return new Response($this->serializer->serialize($inbox, 'json'), Response::HTTP_OK);
    }
    /**
     * @Route("", name="send_message" , methods = "POST")
     */
    public function sendMessage(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $fromUser = $this->getuser()->getEmail();
        $message = new Message();
        $message->setBody($data['body']);
        $message->setToUser($data['toUser']);
        $message->setFromUser($fromUser);
        $this->em->persist($message);
        $this->em->flush();
        $CachedString = $this->InstanceCache->getItem($this->clean($data['toUser']));
        if (!$CachedString->isHit() || $CachedString->isNull()) {
            $CachedString->set([$message])->expiresAfter(3600);
            $this->InstanceCache->save($CachedString);
        } else {
            $array = $CachedString->get();
            array_push($array, $message);
            $CachedString->set($array);
            $this->InstanceCache->save($CachedString);
        }
        return new Response($this->serializer->serialize($message, 'json'), Response::HTTP_OK);
    }

    /**
     * @Route("/{key}/new", name="messages_stream" , methods = "GET")
     */
    public function messagesStream($key)
    {

        $CachedString = $this->InstanceCache->getItem($this->clean($key));
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

    /**
     * @Route("/{id}/read", name="read_message" , methods = "POST")
     */
    public function readMessage($id): Response
    {
        $message = $this->messageRepo->find($id);
        $message->setVu(true);
        $this->em->persist($message);
        $this->em->flush();
        return new Response($this->serializer->serialize($message, 'json'), Response::HTTP_OK);
    }
}
