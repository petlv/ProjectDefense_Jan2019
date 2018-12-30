<?php
/**
 * Created by PhpStorm.
 * User: pivanov
 * Date: 29.12.2018 г.
 * Time: 19:15 ч.
 */

namespace SoftUniBlogBundle\Service;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use SoftUniBlogBundle\Entity\Role;
use SoftUniBlogBundle\Entity\User;

class UserService
{

    /* @var EntityManager $em */
    protected $em;

    /* @var Connection $queryBuilder */
    protected $qBuilder;

    /**
     * OneLevel Constructor
     *
     * @param EntityManager $em
     * @param Connection $conn
     */
    public function __construct(EntityManager $em, Connection $conn)
    {
        $this->em = $em;
        $this->qBuilder = $conn->createQueryBuilder();
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


    public function checkForDuplicateUser($emailForm) {
        $userForm = $this
            ->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $emailForm]);

        return $userForm;
    }

    public function findAndSetRole() {

        /** @var Role[]|null $roles */
        $roles = $this
            ->em
            ->getRepository(Role::class)
            ->findAll();

        if (empty($roles)) {
            $this->qBuilder
                ->insert('roles')
                ->setValue('name', '?')
                ->setParameter(0, 'ROLE_ADMIN')
                ->setValue('name', '?')
                ->setParameter(0, 'ROLE_USER')
                ->setValue('name', '?')
                ->setParameter(0, 'ROLE_OWNER')
                ->setValue('name', '?')
                ->setParameter(0, 'ROLE_TOURIST');
        }

        /** @var Role $role */
        $role = $this
            ->em
            ->getRepository(Role::class)
            ->findOneBy(['name' => 'ROLE_USER']);
    }

}