<?php

namespace SoftUniBlogBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SoftUniBlogBundle\Entity\Accommodation;
use SoftUniBlogBundle\Entity\City;
use SoftUniBlogBundle\Entity\Comment;
use SoftUniBlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Accommodation controller.
 *
 * @Route("accommodation")
 */
class AccommodationController extends Controller
{
    /**
     * Lists all accommodation entities.
     *
     * @Route("/", name="list_accommodations")
     * @Method("GET")
     */
    public function allIndexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $accommodations = $em->getRepository('SoftUniBlogBundle:Accommodation')->findAll();

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $userId = $this->getUser()->getId();
            /** @var User $user */
            $currentUser = $this->container->get('app.user_service')->getCurrentUserFromDb($userId);
            /** @var int $countMsg */
            $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

            return $this->render('accommodation/index.html.twig', array(
                'accommodations' => $accommodations,
                'user' => $currentUser,
                'countMsg' => $countMsg
            ));
        }

        return $this->render('accommodation/index.html.twig', array(
            'accommodations' => $accommodations,
        ));
    }

    /**
     * Lists only own created accommodation entities.
     *
     * @Route("/mylists", name="my_accommodations")
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function myIndexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $accommodations = $em->getRepository('SoftUniBlogBundle:Accommodation')->findBy(['owner' => $this->getUser()]);

        $userId = $this->getUser()->getId();
        /** @var User $user */
        $currentUser = $this->container->get('app.user_service')->getCurrentUserFromDb($userId);
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        return $this->render('accommodation/index.html.twig', array(
            'accommodations' => $accommodations,
            'user' => $currentUser,
            'countMsg' => $countMsg
        ));
    }

    /**
     * Creates a new accommodation entity.
     *
     * @Route("/create", name="accommodation_create")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $accommodation = new Accommodation();
        $form = $this->createForm('SoftUniBlogBundle\Form\AccommodationType', $accommodation);
        $form->handleRequest($request);

        $userId = $this->getUser()->getId();
        /** @var User $user */
        $currentUser = $this->container->get('app.user_service')->getCurrentUserFromDb($userId);
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        /** @var ArrayCollection|null $allCities */
        $allCities = $this->getDoctrine()->getRepository(City::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();

            try {
                $file->move($this->getParameter('accommodation_directory'), $fileName);
            } catch (FileException $exception) {

            }

            /** @var string $cityName */
            $cityName = $accommodation->getCityName();
            /** @var City $city */
            $city = $this->getDoctrine()->getRepository(City::class)->findOneBy(['name' => $cityName]);

            $accommodation
                ->setImage($fileName)
                ->setOwner($currentUser)
                ->setCity($city);

            $em = $this->getDoctrine()->getManager();
            $em->persist($accommodation);
            $em->flush();

//            return $this->redirectToRoute('blog_index');

            return $this->redirectToRoute('accommodation_show', array(
                'id' => $accommodation->getId(),
                'user' => $currentUser,
                'countMsg' => $countMsg));
        }

        return $this->render('accommodation/create.html.twig', array(
            'accommodation' => $accommodation,
            'form' => $form->createView(),
            'user' => $currentUser,
            'countMsg' => $countMsg,
            'allCities' => $allCities
        ));
    }

    /**
     * Finds and displays a accommodation entity.
     *
     * @Route("/{id}", name="accommodation_show")
     * @Method("GET")
     * @param Request $request
     * @param Accommodation $accommodation
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function showAction(Request $request, Accommodation $accommodation)
    {
        $accommodation->addViewCount();
        $em = $this->getDoctrine()->getManager();
        $em->persist($accommodation);
        $em->flush();

        $comments = $this->getDoctrine()->getRepository(Comment::class)
            ->findBy(['accommodation' => $accommodation], ['dateAdded' => 'desc']);

        $deleteForm = $this->createDeleteForm($accommodation);

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $this->getUser();
            /** @var int $countMsg */
            $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

            return $this->render('accommodation/show.html.twig', array(
                'accommodation' => $accommodation,
                'delete_form' => $deleteForm->createView(),
                'user' => $currentUser,
                'countMsg' => $countMsg,
                'comments' => $comments,
            ));
        }

        return $this->render('accommodation/show.html.twig', array(
            'accommodation' => $accommodation,
            'delete_form' => $deleteForm->createView(),
            'comments' => $comments
        ));
    }

    /**
     * @Route("/{id}/like", name="accommodation_add_like")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Accommodation $accommodation
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addLike(Accommodation $accommodation) {

        $currentUser = $this->getUser();
        $currentUser->addLike($accommodation);

        $accommodation->addLike();
        $id = $accommodation->getId();

        $em = $this->getDoctrine()->getManager();
        $em->persist($accommodation);
        $em->persist($currentUser);
        $em->flush();

        return $this->redirectToRoute('accommodation_show', ['id' => $id]);
    }

    /**
     * Displays a form to edit an existing accommodation entity.
     *
     * @Route("/{id}/edit", name="accommodation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Accommodation $accommodation)
    {
        $userId = $this->getUser()->getId();
        /** @var User $user */
        $currentUser = $this->container->get('app.user_service')->getCurrentUserFromDb($userId);
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $deleteForm = $this->createDeleteForm($accommodation);
        $editForm = $this->createForm('SoftUniBlogBundle\Form\AccommodationType', $accommodation);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('accommodation_edit', array(
                'id' => $accommodation->getId(),
                'user' => $currentUser,
                'countMsg' => $countMsg,
                ));
        }

        return $this->render('accommodation/edit.html.twig', array(
            'accommodation' => $accommodation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'user' => $currentUser,
            'countMsg' => $countMsg,
        ));
    }

    /**
     * Deletes a accommodation entity.
     *
     * @Route("/{id}", name="accommodation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Accommodation $accommodation)
    {
        $userId = $this->getUser()->getId();
        /** @var User $user */
        $currentUser = $this->container->get('app.user_service')->getCurrentUserFromDb($userId);
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $form = $this->createDeleteForm($accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($accommodation);
            $em->flush();
        }

        return $this->redirectToRoute('my_accommodations', array(
            'user' => $currentUser,
            'countMsg' => $countMsg,
        ));
    }

    /**
     * Creates a form to delete a accommodation entity.
     *
     * @param Accommodation $accommodation The accommodation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Accommodation $accommodation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('accommodation_delete', array('id' => $accommodation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
