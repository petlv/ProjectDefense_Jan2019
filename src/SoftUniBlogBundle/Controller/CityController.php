<?php

namespace SoftUniBlogBundle\Controller;

use SoftUniBlogBundle\Entity\City;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;
use SoftUniBlogBundle\Form\CityType;

/**
 * City controller.
 *
 * @Route("city")
 */
class CityController extends Controller
{
    /**
     * Creates a new city entity.
     *
     * @Route("/new", name="city_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $city = new City();
        $form = $this->createForm(CityType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($city);
            $em->flush();

            $this->addFlash('alertMessage', 'City successfully added!');

            return $this->redirectToRoute('city_show', array(
                'id' => $city->getId(),
                'user' => $this->getUser(),
                'countMsg' => $countMsg
            ));
        }

        return $this->render('city/new.html.twig', array(
            'city' => $city,
            'form' => $form->createView(),
            'user' => $this->getUser(),
            'countMsg' => $countMsg
        ));
    }

    /**
     * Finds and displays a city entity.
     *
     * @Route("/{id}", name="city_show")
     * @Method("GET")
     * @param City $city
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(City $city)
    {
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        return $this->render('city/show.html.twig', array(
            'city' => $city,
            'user' => $this->getUser(),
            'countMsg' => $countMsg
        ));
    }

    /**
     * Displays a form to edit an existing city entity.
     *
     * @Route("/{id}/edit", name="city_edit")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param City $city
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, City $city)
    {
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $editForm = $this->createForm(CityType::class, $city);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('alertMessage', 'City successfully edited!');

            return $this->redirectToRoute('city_show', array(
                'id' => $city->getId(),
                'user' => $this->getUser(),
                'countMsg' => $countMsg
            ));
        }

        return $this->render('city/edit.html.twig', array(
            'city' => $city,
            'form' => $editForm->createView(),
            'user' => $this->getUser(),
            'countMsg' => $countMsg
        ));
    }

    /**
     * Deletes a city entity.
     *
     * @Route("/{id}/delete", name="city_delete")
     * @Method("DELETE")
     * @param Request $request
     * @param City $city
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request, City $city)
    {
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $deleteForm = $this->createForm(CityType::class, $city);
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($city);
            $em->flush();
            $this->addFlash('alertMessage', 'City successfully deleted!');

            return $this->redirectToRoute('dashboard', array(
                'user' => $this->getUser(),
                'countMsg' => $countMsg
            ));
        }

        return $this->render('city/delete.html.twig', array(
            'city' => $city,
            'form' => $deleteForm->createView(),
            'user' => $this->getUser(),
            'countMsg' => $countMsg
        ));
    }
}
