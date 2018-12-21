<?php

namespace SoftUniBlogBundle\Controller;

use SoftUniBlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("admin")
 * Class AdminController
 * @package SoftUniBlogBundle\Controller
 */
class AdminController extends Controller
{
    /**
     * @Route("/", name = "all_users")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $allUsers = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('admin/index.html.twig', ['allUsers' => $allUsers]);
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

        return $this->render('admin/user_profile.html.twig', ['user' => $user]);
    }
}
