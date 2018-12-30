<?php

namespace SoftUniBlogBundle\Controller;

use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\Role;
use SoftUniBlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * User controller.
 *
 * @Route("user")
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="user_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('SoftUniBlogBundle:User')->findAll();

        return $this->render('user/index.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/register", name="user_register")
     * @throws \Doctrine\DBAL\DBALException
     */
    public function registerAction(Request $request)
    {
        $newUser = new User();
        $form = $this->createForm('SoftUniBlogBundle\Form\UserType', $newUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $emailForm = $form->getData()->getEmail();
            $userForm = $this->container->get('app.user_service')->checkForDuplicateUser($emailForm);

            if (null !== $userForm) {
                $this->addFlash('info', "Username with email " . $emailForm . " is already taken!");
                return $this->render('user/register.html.twig', ['form' => $form->createView()]);
            }

            $password = $this->get('security.password_encoder')
                ->encodePassword($newUser, $newUser->getPassword());

            /** @var string $typeUser */
            $typeUser = $newUser->getUserType();
            /** @var Role $newRole */
            $newRole = $this->container->get('app.user_service')->findAndSetRole($typeUser);
            $newUser
                ->addRole($newRole)
                ->setPassword($password);

            $em->persist($newUser);
            $em->flush();

            return $this->redirectToRoute('security_login');
        }

        return $this->render('user/register.html.twig', array(
            'user' => $newUser,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a user entity.
     *
     * @Route("/{id}", name="user_show")
     */
    public function showAction(User $user)
    {
        /** @var Message[] $unreadMessages */
        $unreadMessages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findBy(['recipient' => $user, 'isReaded' => false]);

        $countMsg = count($unreadMessages);

        $deleteForm = $this->createDeleteForm($user);

        return $this->render('user/show.html.twig', array(
            'user' => $user,
            'delete_form' => $deleteForm->createView(),
            'countMsg' => $countMsg
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit", name="user_edit")
     */
    public function editAction(Request $request, User $user)
    {
        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm('SoftUniBlogBundle\Form\UserType', $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('user/edit.html.twig', array(
            'user' => $user,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a user entity.
     *
     * @Route("/{id}", name="user_delete")
     */
    public function deleteAction(Request $request, User $user)
    {
        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('user_delete', array('id' => $user->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
