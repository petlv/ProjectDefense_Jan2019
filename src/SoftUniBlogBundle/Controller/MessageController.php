<?php

namespace SoftUniBlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Message controller.
 *
 * @Route("message")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')");
 */
class MessageController extends Controller
{

    const PAGINATION_LIMIT = 5;

    /**
     * Lists all message entities.
     *
     * @Route("/mailbox", name="mailbox")
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mailboxAction(Request $request)
    {
        $currentUser = $this->getUser();

        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $em = $this->getDoctrine()->getManager();
        $messages = $em->getRepository('SoftUniBlogBundle:Message')->findBy(
            array('recipient' => $currentUser),
            array('dateAdded' => 'DESC')
        );

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $messages, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            self::PAGINATION_LIMIT/*limit per page*/
        );

        return $this->render('message/mailbox.html.twig', array(
            'messages' => $messages,
            'pagination' => $pagination,
            'user' => $currentUser,
            'countMsg' => $countMsg
        ));
    }

    /**
     * Creates a new message entity.
     *
     * @Route("/new/user/{id}/accommodation/{accommodationId}", name="message_new")
     * @Method({"GET", "POST"})
     * @param Request $request
     * @param $id
     * @param $accommodationId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function newAction(Request $request, $id, $accommodationId)
    {
        $currentUser = $this->getUser();
        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        /** @var User $recipient */
        $recipient = $this->getDoctrine()->getRepository(User::class)
            ->find($id);

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        $alertMessage = 'Form is not submitted correctly. Fill it again!';

        if ($form->isSubmitted() && $form->isValid()) {

            $message
                ->setSender($currentUser)
                ->setRecipient($recipient)
                ->setIsReaded(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            $alertMessage = 'Message sent successfully!';

            $this->addFlash('message', $alertMessage);

            return $this->redirectToRoute('accommodation_show', array(
                'id' => $accommodationId,
                'user' => $recipient,
                'countMsg' => $countMsg,
            ));
        }

        return $this->render('message/new.html.twig', array(
            'message' => $message,
            'form' => $form->createView(),
            'id' => $accommodationId,
            'user' => $recipient,
            'countMsg' => $countMsg,
        ));
    }

    /**
     * @Route("/reply/user/{userId}/message/{messageId}", name="message_reply")
     * @param Request $request
     * @param $userId
     * @param $messageId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function replyAction(Request $request, $userId, $messageId)
    {
        $message = $this->getDoctrine()->getRepository(Message::class)->find($messageId);

        $sendMessage = new Message();
        $form = $this->createForm(MessageType::class, $sendMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sendMessage
                ->setSender($this->getUser())
                ->setRecipient($message->getSender())
                ->setIsReaded(false);

            $em = $this->getDoctrine()->getManager();
            $em->persist($sendMessage);
            $em->flush();

            $this->addFlash('alertMessage', 'Message sent successfully!');
            /** @var int $countMsg */
            $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

            return $this->redirectToRoute('mailbox', array(
                'user' => $this->getUser(),
                'countMsg' => $countMsg,
            ));
        }

        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        return $this->render('message/reply.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'user' => $this->getUser(),
            'countMsg' => $countMsg
        ]);
    }

    /**
     * Finds and displays a message entity.
     *
     * @Route("/{id}", name="message_show")
     * @Method("GET")
     * @param Message $message
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Message $message)
    {
        $message->setIsReaded(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

        /** @var int $countMsg */
        $countMsg = $this->container->get('app.message_service')->countUnreadMessages();

        $deleteForm = $this->createDeleteForm($message);

        return $this->render('message/show.html.twig', array(
            'message' => $message,
            'delete_form' => $deleteForm->createView(),
            'user' => $this->getUser(),
            'countMsg' => $countMsg
        ));
    }

    /**
     * Displays a form to edit an existing message entity.
     *
     * @Route("/{id}/edit", name="message_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Message $message)
    {
        $deleteForm = $this->createDeleteForm($message);
        $editForm = $this->createForm('SoftUniBlogBundle\Form\MessageType', $message);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('message_edit', array('id' => $message->getId()));
        }

        return $this->render('message/edit.html.twig', array(
            'message' => $message,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a message entity.
     *
     * @Route("/{id}", name="message_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Message $message)
    {
        $form = $this->createDeleteForm($message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($message);
            $em->flush();
        }

        return $this->redirectToRoute('message_index');
    }

    /**
     * Creates a form to delete a message entity.
     *
     * @param Message $message The message entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Message $message)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('message_delete', array('id' => $message->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
