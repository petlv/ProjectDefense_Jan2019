<?php

namespace SoftUniBlogBundle\Controller;

use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\Role;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @Route("/register", name="user_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $emailForm = $form->getData()->getEmail();

            $userForm = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->findOneBy(['email' => $emailForm]);

            if (null !== $userForm) {
                $this->addFlash('info', "Username with email " . $emailForm . " is already taken!");
                return $this->render('user/register.html.twig', ['form' => $form->createView()]);
            }

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());

            /** @var Role $role */
            $role = $this
                ->getDoctrine()
                ->getRepository(Role::class)
                ->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($role);

            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('security_login');
        }


        return $this->render('user/register.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/profile", name="user_profile")
     */
    public function profile(){
        $userId = $this->getUser()->getId();
        $user = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);

        $unreadMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['recipient' => $user, 'isReaded' => false]);

        $countMsg = count($unreadMessages);

        return $this->render("user/profile.html.twig", ['user' => $user, 'countMsg' => $countMsg]);
    }
}
