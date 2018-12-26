<?php

namespace SoftUniBlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SoftUniBlogBundle\Entity\Accommodation;
use SoftUniBlogBundle\Entity\Comment;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\AccommodationType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AccommodationController extends Controller
{
    /**
     * @Route("/accommodation/create", name="accommodation_create")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $accommodation = new Accommodation();
        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();

            try {
                $file->move($this->getParameter('accommodation_directory'), $fileName);
            } catch (FileException $exception) {

            }

            $accommodation->setImage($fileName);
            $currentUser = $this->getUser();
            $accommodation->setOwner($currentUser);
            $accommodation->setViewCount(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($accommodation);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('accommodation/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/accommodation/{id}", name="accommodation_view")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAccommodation($id) {
        $accommodation = $this
            ->getDoctrine()
            ->getRepository(Accommodation::class)
            ->find($id);

        $comments = $this->getDoctrine()->getRepository(Comment::class)
            ->findBy(['accommodation' => $accommodation], ['dateAdded' => 'desc']);

        $accommodation->setViewCount($accommodation->getViewCount() + 1);
        $em = $this->getDoctrine()->getManager();
        $em->persist($accommodation);
        $em->flush();

        return $this->render('accommodation/accommodation.html.twig', ['accommodation' => $accommodation, 'comments' => $comments]);

    }

    /**
     * @Route("/accommodation/edit/{id}", name="accommodation_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id)
    {
        /** @var Accommodation $accommodation */
        $accommodation = $this
            ->getDoctrine()
            ->getRepository(Accommodation::class)
            ->find($id);

        if ($accommodation === null) {
            return $this->redirectToRoute('blog_index');
        }

        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();

        if (!$currentUser->isOwner($accommodation) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute('blog_index');
        }

        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $file */
            $file = $form->getData()->getImage();
            $fileName = md5(uniqid('', true)) . '.' . $file->guessExtension();

            try {
                $file->move($this->getParameter('accommodation_directory'), $fileName);
            } catch (FileException $exception) {

            }

            $accommodation->setImage($fileName);
            $currentUser = $this->getUser();
            $accommodation->setOwner($currentUser);

            $em = $this->getDoctrine()->getManager();
            $em->merge($accommodation);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('accommodation/edit.html.twig', ['form' => $form->createView(),
            'accommodation' => $accommodation]);
    }

    /**
     * @Route("/accommodation/delete/{id}", name="accommodation_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, $id)
    {
        /** @var Accommodation $accommodation */
        $accommodation = $this->getDoctrine()->getRepository(Accommodation::class)->find($id);

        if ($accommodation === null) {
            return $this->redirectToRoute('blog_index');
        }

        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();

        if (!$currentUser->isOwner($accommodation) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute('blog_index');
        }

        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentUser = $this->getUser();
            $accommodation->setOwner($currentUser);

            $em = $this->getDoctrine()->getManager();
            $em->remove($accommodation);
            $em->flush();

            return $this->redirectToRoute('blog_index');
        }

        return $this->render('accommodation/delete.html.twig', ['form' => $form->createView(),
            'accommodation' => $accommodation]);
    }

    /**
     * @Route("/my-accommodations", name="my_accommodations")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function myArticles() {
        $accommodations = $this
            ->getDoctrine()
            ->getRepository(Accommodation::class)
            ->findBy(['owner' => $this->getUser()]);

        return $this->render('accommodation/my_accommodations.html.twig',
            ['accommodations' => $accommodations]);
    }

    /**
     * @Route("/accommodation/like/{id}", name="accommodation_likes")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function likes($id) {

        return $this->redirectToRoute('blog_index');
    }
}
