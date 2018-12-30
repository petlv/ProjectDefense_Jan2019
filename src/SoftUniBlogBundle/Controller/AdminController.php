<?php

namespace SoftUniBlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use SoftUniBlogBundle\Entity\City;
use SoftUniBlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin")
 * Class AdminController
 * @package SoftUniBlogBundle\Controller
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name = "dashboard")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var User $allUsers */
        $allUsers = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        /** @var City $cities */
        $allCities = $this->getDoctrine()
            ->getRepository(City::class)
            ->findAll();

        $userId = $this->getUser()->getId();
        /** @var User $user */
        $currentUser = $this->container->get('app.user_service')->getCurrentUserFromDb($userId);
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        return $this->render('admin/dashboard.html.twig', ['allUsers' => $allUsers, 'allCities' => $allCities,
            'user' => $currentUser,
                'countMsg' => $countMsg] );
    }

    /**
     * @Route("/user-profile/{id}", name = "admin_user_profile")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userProfile($id)
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($id);

        return $this->render('user/show.html.twig', ['user' => $user]);
    }

    /**
     * @Route("/city-profile/{id}", name = "admin_city_profile")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cityProfile($id)
    {
        $city = $this->getDoctrine()
            ->getRepository(City::class)
            ->find($id);

        return $this->render('city/show.html.twig', ['city' => $city]);
    }
}
