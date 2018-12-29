<?php
/**
 * Created by PhpStorm.
 * User: pivanov
 * Date: 29.12.2018 г.
 * Time: 19:15 ч.
 */

namespace SoftUniBlogBundle\Service;


use Doctrine\ORM\EntityManager;
use SoftUniBlogBundle\Entity\User;

class UserService
{

    /* @var EntityManager $em */
    protected $em;

    /**
     * OneLevel Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param $userId
     * @return User
     */
    public function getCurrentUserFromDb($userId) {

        /** @var User $foundUser */
        $foundUser = $this
            ->em->getRepository(User::class)
            ->find($userId);

        return $foundUser;
    }

}