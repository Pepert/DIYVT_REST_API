<?php
namespace AppBundle\Controller\Post;

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Form\Type\CommentType;
use AppBundle\Entity\Comment;
use Symfony\Component\Validator\Constraints\Date;

class CommentController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"comment"})
     * @Rest\Get("/posts/{category}/{id}/comments")
     */
    public function getCommentsAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->find($request->get('id')); // L'identifiant en tant que paramétre n'est plus nécessaire
        /* @var $post Post */

        if (empty($post)) {
            return $this->postNotFound();
        }

        return $post->getComments();
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"comment"})
     * @Rest\Post("/posts/{category}/{id}/comments")
     */
    public function addCommentsAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->find($request->get('id'));
        /* @var $post Post */

        if (empty($post)) {
            return $this->postNotFound();
        }

        $comment = new Comment();
        $comment->setPost($post);
        $form = $this->createForm(CommentType::class, $comment);

        $form->submit($request->request->get("comment"));
        $userId = $request->request->get("user_id");

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');

            $user = $em->getRepository('AppBundle:User')
                ->find($userId);

            $comment->setUser($user);
            $comment->setDate();

            $em->persist($comment);
            $em->flush();
            return $comment;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"comment"})
     * @Rest\Post("/delete/comment/{id}")
     */
    public function deleteCommentsAction(Request $request)
    {
        $comment = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Comment')
            ->find($request->get('id'));
        /* @var $comment Comment */

        if (empty($comment)) {
            return false;
        }

        $post = $comment->getPost();
        $post->deleteComment($comment);
        $this->get('doctrine.orm.entity_manager')->persist($post);
        $this->get('doctrine.orm.entity_manager')->remove($comment);
        $this->get('doctrine.orm.entity_manager')->flush();

        return true;
    }

    /**
     * @Rest\View(serializerGroups={"comment"})
     * @Rest\Post("/edit/comment/{id}")
     */
    public function editCommentsAction(Request $request)
    {
        $comment = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Comment')
            ->find($request->get('id'));
        /* @var $comment Comment */

        if (empty($comment)) {
            return false;
        }

        $text = $request->request->get("text");
        $comment->setText($text);
        $this->get('doctrine.orm.entity_manager')->persist($comment);
        $this->get('doctrine.orm.entity_manager')->flush();

        return true;
    }

    /**
     * @Rest\View(serializerGroups={"comment"})
     * @Rest\Post("/upvote/comment/{id}/{user}")
     */
    public function upvoteCommentAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('user'));

        $upvotedPosts = $user->getUpvotedPosts();

        if(!in_array($request->get('id'), $upvotedPosts)) {
            $comment = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Comment')
                ->find($request->get('id'));
            /* @var $comment Comment */

            if (empty($comment)) {
                return $this->commentNotFound();
            } else {
                $comment->setUpvote($comment->getUpvote() + 1);
                $user->addUpvotedPosts($request->get('id'));

                $em = $this->get('doctrine.orm.entity_manager');

                $em->persist($comment);
                $em->persist($user);
                $em->flush();
            }

            return $comment->getUpvote();
        } else {
            return "already_upvoted";
        }
    }

    /**
     * @Rest\View(serializerGroups={"comment"})
     * @Rest\Post("/downvote/comment/{id}/{user}")
     */
    public function downvoteCommentAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('user'));

        $downvotedPosts = $user->getDownvotedPosts();
        if(!in_array($request->get('id'), $downvotedPosts)) {
            $comment = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Comment')
                ->find($request->get('id'));
            /* @var $comment Comment */

            if (empty($comment)) {
                return $this->commentNotFound();
            } else {
                $comment->setDownvote($comment->getDownvote() + 1);
                $user->addDownvotedPosts($request->get('id'));

                $em = $this->get('doctrine.orm.entity_manager');

                $em->persist($comment);
                $em->persist($user);
                $em->flush();
            }

            return $comment->getDownvote();
        } else {
            return "already_downvoted";
        }
    }

    private function postNotFound()
    {
        return View::create(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
    }

    private function commentNotFound()
    {
        return View::create(['message' => 'Comment not found'], Response::HTTP_NOT_FOUND);
    }
}