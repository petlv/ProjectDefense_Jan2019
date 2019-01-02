<?php

namespace SoftUniBlogBundle\Controller;

use SoftUniBlogBundle\Entity\Accommodation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends Controller
{
    const PAGINATION_LIMIT = 2;

    /**
     * @Route("/", name="blog_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var Accommodation $accommodations */
        $accommodations = $this
            ->getDoctrine()
            ->getRepository(Accommodation::class)
            ->findBy([], ['dateAdded' => 'desc', 'viewCount' => 'desc']);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $accommodations, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            self::PAGINATION_LIMIT/*limit per page*/
        );

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $this->getUser();

            /** @var int $countMsg */
            $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

            return $this->render('home/index.html.twig', [
                'pagination' => $pagination,
                'user' => $currentUser,
                'countMsg' => $countMsg]
            );
        }

        return $this->render('home/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Only for testing purposes!
     *
     * @Route("/test", name="test_action")
     */
    public function testAction() {



        return $this->render('home/test.html.twig');

    }
}
