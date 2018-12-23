<?php

namespace SoftUniBlogBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SoftUniBlogBundle\Entity\Article;
use SoftUniBlogBundle\Entity\Comment;
use SoftUniBlogBundle\Entity\User;
use SoftUniBlogBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends Controller
{
    /**
     * @Route("/article/{id}/comment", name="add_comment")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @param Article $article
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     */
    public function addComment(Request $request, Article $article)
    {
        $user = $this->getUser();

        /**@var User $author */
        $author = $this->getDoctrine()->getRepository(User::class)->find($user->getId());

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form ->handleRequest($request);

        $comment->setAuthor($author);
        $comment->setArticle($article);

        $author->addComment($comment);
        $article->addComment($comment);

        $em = $this->getDoctrine()->getManager();
        $em->persist($comment);
        $em->flush();

        return $this->redirectToRoute('article_view', ['id' => $article->getId()]);
    }
}
