<?php
/**
 * Created by PhpStorm.
 * User: pivanov
 * Date: 29.12.2018 г.
 * Time: 17:02 ч.
 */

namespace SoftUniBlogBundle\Service;


use Doctrine\ORM\EntityManager;
use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\User;
use Symfony\Component\Security\Core\Security;

class MessageService
{
    /* @var EntityManager $em */
    protected $em;

    /* @var Security $security */
    protected $security;

    /**
     * OneLevel Constructor
     *
     * @param EntityManager $em
     * @param Security $security
     */
    public function __construct(EntityManager $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * @return int
     */
    public function countUnreadMessages() {

        $currentUser = $this->security->getUser();

        /** @var Message[]|null $unreadMessages */
        $unreadMessages = $this
            ->em->getRepository(Message::class)
            ->findBy(['recipient' => $currentUser, 'isReaded' => false]);

        return count($unreadMessages);
    }
}