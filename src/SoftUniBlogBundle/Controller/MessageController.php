<?php

namespace SoftUniBlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SoftUniBlogBundle\Entity\Message;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Message controller.
 *
 * @Route("message")
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')");
 */
class MessageController extends Controller
{
    /**
     * Lists all message entities.
     *
     * @Route("/", name="message_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $messages = $em->getRepository('SoftUniBlogBundle:Message')->findAll();

        return $this->render('message/index.html.twig', array(
            'messages' => $messages,
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
                'alertMessage' => $alertMessage,
            ));
        }

        return $this->render('message/new.html.twig', array(
            'message' => $message,
            'form' => $form->createView(),
            'id' => $accommodationId,
            'user' => $recipient,
            'countMsg' => $countMsg,
            'alertMessage' => $alertMessage,
        ));
    }

    /**
     * Finds and displays a message entity.
     *
     * @Route("/{id}", name="message_show")
     * @Method("GET")
     */
    public function showAction(Message $message)
    {
        $deleteForm = $this->createDeleteForm($message);

        return $this->render('message/show.html.twig', array(
            'message' => $message,
            'delete_form' => $deleteForm->createView(),
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
