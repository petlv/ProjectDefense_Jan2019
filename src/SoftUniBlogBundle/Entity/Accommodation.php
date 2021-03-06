<?php

namespace SoftUniBlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Accommodation
 *
 * @ORM\Table(name="accommodations")
 * @ORM\Entity(repositoryClass="SoftUniBlogBundle\Repository\AccommodationRepository")
 */
class Accommodation
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
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_added", type="datetime")
     */
    private $dateAdded;

    /**
     * @var string
     */
    private $summary;

    /**
     * @var int
     * @ORM\Column(name="owner_id", type="integer")
     */
    private $ownerId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="SoftUniBlogBundle\Entity\User", inversedBy="accommodations")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", nullable=false)
     */
    private $image;

    /**
     * @var integer
     *
     * @ORM\Column(name="view_count", type="integer")
     */
    private $viewCount;

    /**
     * @var ArrayCollection|Comment[]
     *
     *@ORM\OneToMany(targetEntity="SoftUniBlogBundle\Entity\Comment", mappedBy="accommodation", cascade={"remove"})
     */
    private $comments;

    /**
     * @var integer
     * @ORM\Column(name="likes_number", type="integer")
     */
    private $likesNumber;

    /**
     * @var ArrayCollection|User[]
     *
     * @ORM\ManyToMany(targetEntity="SoftUniBlogBundle\Entity\User", mappedBy="given_likes")
     */
    private $likesUsers;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="SoftUniBlogBundle\Entity\City", inversedBy="accommodations")
     */
    private $city;

    /**
     * @var string
     *
     * @Assert\NotNull
     *@ORM\Column(name="city_name", type="string", length=255)
     */
    private $cityName;


    /**
     * Accommodation constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->dateAdded = new \DateTime('now');
        $this->comments = new ArrayCollection();
        $this->viewCount = 0;
        $this->likesNumber = 0;
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
     * Set name
     *
     * @param $name
     * @return Accommodation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param $description
     * @return Accommodation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set dateAdded
     *
     * @param \DateTime $dateAdded
     *
     * @return Accommodation
     */
    public function setDateAdded($dateAdded)
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    /**
     * Get dateAdded
     *
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * @return string
     */
    public function getSummary()
    {
        if (strlen($this->description) > 50) {
            $this->setSummary();
        }
        return $this->summary;
    }

    public function setSummary()
    {
        $this->summary = substr($this->getDescription(), 0,
            strlen($this->getDescription()) / 2
        ) . '...';
    }

    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param integer $ownerId
     *
     * @return Accommodation
     */
    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     * @return Accommodation
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param string $image
     * @return Accommodation
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return int
     */
    public function getViewCount()
    {
        return $this->viewCount;
    }

    /**
     * @return Accommodation
     */
    public function addViewCount()
    {
        ++$this->viewCount;
        return $this;
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
     * @return Accommodation
     */
    public function addComment(Comment $comment = null)
    {
        $this->comments[] = $comment;
        return $this;
    }

    /**
     * @return int
     */
    public function getLikesNumber()
    {
        return $this->likesNumber;
    }


    /**
     * @return $this
     */
    public function addLike()
    {
        ++$this->likesNumber;
        return $this;
    }

    /**
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param City $city
     * @return Accommodation
     */
    public function setCity(City $city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getLikesUsers()
    {
        return $this->likesUsers;
    }

    /**
     * @param ArrayCollection $likesUsers
     */
    public function setLikesUsers($likesUsers)
    {
        $this->likesUsers = $likesUsers;
    }

    /**
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * @param string $cityName
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;
    }


}

