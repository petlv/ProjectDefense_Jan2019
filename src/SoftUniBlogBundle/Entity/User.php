<?php

namespace SoftUniBlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="SoftUniBlogBundle\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotNull
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="full_name", type="string", length=255)
     */
    private $fullName;

    /**
     * @var string
     *
     * @Assert\NotNull
     * @ORM\Column(name="user_type", type="string", length=255)
     */
    private $userType;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SoftUniBlogBundle\Entity\Accommodation", mappedBy="owner", cascade={"remove"})
     */
    private $accommodations;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="SoftUniBlogBundle\Entity\Role", inversedBy="users")
     * @ORM\JoinTable(name="users_roles",
     *          joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *           )
     */
    private $roles;

    /**
     * @var ArrayCollection|Comment[]
     *
     * @ORM\OneToMany(targetEntity="SoftUniBlogBundle\Entity\Comment", mappedBy="author", cascade={"remove"})
     */
    private $comments;

    /**
     * @var ArrayCollection|Message[]
     *
     * @ORM\OneToMany(targetEntity="SoftUniBlogBundle\Entity\Message", mappedBy="sender")
     */
    private $send_messages;

    /**
     * @var ArrayCollection|Message[]
     *
     * @ORM\OneToMany(targetEntity="SoftUniBlogBundle\Entity\Message", mappedBy="recipient")
     */
    private $receive_messages;

    /**
     * @var ArrayCollection|Accommodation[]
     *
     * @ORM\ManyToMany(targetEntity="SoftUniBlogBundle\Entity\Accommodation", inversedBy="likesUsers")
     * @ORM\JoinTable(name="users_likes",
     *          joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="accommodation_id", referencedColumnName="id")}
     *           )
     */
    private $given_likes;


    /**
     * User constructor.
     */
    public function __construct()
    {
        $this->accommodations = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->send_messages = new ArrayCollection();
        $this->receive_messages = new ArrayCollection();
        $this->given_likes = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     *
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param string $userType
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
    }



    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return array('ROLE_USER');
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[]
     */
    public function getRoles()
    {
        $stringRoles = [];

        foreach ($this->roles as $role) {
            /** @var Role $role  */
            $stringRoles[] = $role->getRole();
        }

        return $stringRoles;
    }

    /**
     * @param Role $role
     *
     * @return User
     */
    public function addRole(Role $role)
    {
        $this->roles[] = $role;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return ArrayCollection
     */
    public function getAccommodations()
    {
        return $this->accommodations;
    }

    /**
     * @param ArrayCollection $accommodations
     */
    public function setAccommodations($accommodations)
    {
        $this->accommodations = $accommodations;
    }

    /**
     * @param Accommodation $accommodation
     * @return User
     */
    public function addAccommodation(Accommodation $accommodation)
    {
        $this->accommodations[] = $accommodation;

        return $this;
    }

    /**
     * @param Accommodation $accommodation
     * @return bool
     */
    public function isOwner(Accommodation $accommodation) {
        return $accommodation->getOwnerId() === $this->getId();
    }

    /**
     * @return bool
     */
    public function isAdmin() {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    /**
     * @return ArrayCollection|Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment|null $comment
     * @return User
     */
    public function addComment(Comment $comment = null)
    {
        $this->comments[] = $comment;
        return $this;
    }

    /**
     * @return ArrayCollection|Message[]
     */
    public function getSendMessages()
    {
        return $this->send_messages;
    }

    /**
     * @param ArrayCollection|Message[] $send_messages
     */
    public function setSendMessages($send_messages)
    {
        $this->send_messages = $send_messages;
    }

    /**
     * @return ArrayCollection|Message[]
     */
    public function getReceiveMessages()
    {
        return $this->receive_messages;
    }

    /**
     * @param ArrayCollection|Message[] $receive_messages
     */
    public function setReceiveMessages($receive_messages)
    {
        $this->receive_messages = $receive_messages;
    }

    /**
     * @return ArrayCollection|Accommodation[]
     */
    public function getGivenLikes()
    {
        return $this->given_likes;
    }

    /**
     * @param Accommodation $accommodation
     * @return User
     */
    public function addLike(Accommodation $accommodation)
    {
        $this->given_likes[] = $accommodation;
        return $this;
    }

}

