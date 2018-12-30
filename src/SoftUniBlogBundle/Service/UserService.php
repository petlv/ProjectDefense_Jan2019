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


    public function checkForDuplicateUser($emailForm) {
        $userForm = $this
            ->em
            ->getRepository(User::class)
            ->findOneBy(['email' => $emailForm]);

        return $userForm;
    }


    /**
     * @param string $typeUser
     * @return Role
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findAndSetRole($typeUser) {

        /** @var Role[]|null $roles */
        $roles = $this
            ->em
            ->getRepository(Role::class)
            ->findAll();

        if (empty($roles)) {
            $conn = $this->em->getConnection();
            $dbal = "INSERT INTO roles (name) values ('ROLE_ADMIN'), ('ROLE_OWNER'), ('ROLE_TOURIST')";
            $stmt = $conn->prepare($dbal);
            $stmt->execute();
        }

        $newRole = new Role();

        if ('type_owner' === $typeUser) {

            /** @var Role $newRole */
            $newRole = $this
                ->em
                ->getRepository(Role::class)
                ->findOneBy(['name' => 'ROLE_OWNER']);

        } elseif ('type_tourist' === $typeUser) {

            /** @var Role $newRole */
            $newRole = $this
                ->em
                ->getRepository(Role::class)
                ->findOneBy(['name' => 'ROLE_TOURIST']);
        }

        return $newRole;
    }

}