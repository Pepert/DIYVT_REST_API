<?php
namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Form\Type\PostType;
use AppBundle\Entity\Post;

class PostController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Get("/posts")
     */
    public function getPostsAction(Request $request)
    {
        $posts = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->findAll();
        /* @var $posts Post[] */

        return $posts;
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Get("/posts/{category}")
     */
    public function getPostListAction(Request $request)
    {
        $listPosts = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->findBy(
                array('category' => $request->get('category')),
                array('date' => 'desc')
            );
        /* @var $listPosts Post[] */

        if (empty($listPosts)) {
            return false;
        }

        return $listPosts;
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Get("/posts/{category}/{id}")
     */
    public function getPostAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->find($request->get('id'));
        /* @var $post Post */

        if (empty($post)) {
            return new JsonResponse(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        return $post;
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Post("/post/update/{id}")
     */
    public function updatePostTextAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->find($request->get('id'));
        /* @var $post Post */

        if (empty($post)) {
            return false;
        }

        $text = $request->request->get("text");
        $post->setContent($text);
        $this->get('doctrine.orm.entity_manager')->persist($post);
        $this->get('doctrine.orm.entity_manager')->flush();

        return new JsonResponse([
            'status' => 'ok'
        ]);
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Post("/post/search")
     */
    public function searchAction(Request $request)
    {
        $listPosts = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->fetchResults($request->request->get("search"));
        /* @var $listPosts Post[] */

        if (empty($listPosts)) {
            return false;
        }

        return $listPosts;
    }

    /**
     * @Rest\Post("/upload")
     */
    public function uploadAction()
    {
        header('Access-Control-Allow-Origin: *');
        $target_path = $this->getParameter('upload_dir') . '/';

        $target_path = $target_path . basename( $_FILES['file']['name']);

        if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            echo "Upload and move success";
        } else{
            echo $target_path;
            echo print_r($_FILES);
        }

        return new JsonResponse([
            'message' => ini_get('upload_max_filesize'),
            'route' => $target_path
        ]);
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"post"})
     * @Rest\Post("/posts")
     */
    public function addPostsAction(Request $request)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->submit($request->request->get("post"));
        $userId = $request->request->get("user_id");
        $urls = $request->request->get("urls");
        $links = $request->request->get("links");

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');

            $user = $em->getRepository('AppBundle:User')
                ->find($userId);

            $post->setUser($user);
            $post->setDate();

            $user->addFavouritePost($post);

            $post->setSrcUrls($urls);
            $post->setLinks($links);

            $em->persist($post);
            $em->flush();
            return $post;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Get("/getfavouriteusers/{user_id}/{post_id}")
     */
    public function isFavouriteUserAction(Request $request)
    {
        $userId = $request->get("user_id");
        $postId = $request->get("post_id");

        $post = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->findBy(
                array('id' => $postId)
            );

        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findBy(
                array('id' => $userId)
            );

        if($post[0]->getFavouriteUsers()->contains($user[0])) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"post"})
     * @Rest\Post("/addfavourite/{id}")
     */
    public function addFavouritePostAction(Request $request)
    {
        $userId = $request->request->get("user_id");

        $em = $this->get('doctrine.orm.entity_manager');

        $post = $em
            ->getRepository('AppBundle:Post')
            ->find($request->get('id'));
        $user = $em
            ->getRepository('AppBundle:User')
            ->find($userId);

        $user->addFavouritePost($post);

        $em->persist($user);
        $em->persist($post);
        $em->flush();

        return true;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"post"})
     * @Rest\Post("/deletefavourite/{id}")
     */
    public function deleteFavouritePostAction(Request $request)
    {
        $userId = $request->request->get("user_id");

        $em = $this->get('doctrine.orm.entity_manager');

        $post = $em
            ->getRepository('AppBundle:Post')
            ->find($request->get('id'));
        $user = $em
            ->getRepository('AppBundle:User')
            ->find($userId);

        $user->deleteFavouritePost($post);

        $em->persist($user);
        $em->persist($post);
        $em->flush();

        return true;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"post"})
     * @Rest\Delete("/posts/{category}/{id}")
     */
    public function removePostAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $post = $em->getRepository('AppBundle:Post')
            ->find($request->get('id'));
        /* @var $place Post */

        if (!$post) {
            return;
        }

        foreach ($post->getComments() as $comment) {
            $em->remove($comment);
        }

        $em->remove($post);
        $em->flush();
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Put("/posts/{category}/{id}")
     */
    public function updatePostAction(Request $request)
    {
        $post = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:Post')
            ->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
        /* @var $post Post */

        if (empty($post)) {
            return \FOS\RestBundle\View\View::create(['message' => 'Post not found'], Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(PostType::class, $post);

        // Le paramètre false dit à Symfony de garder les valeurs dans notre
        // entité si l'utilisateur n'en fournit pas une dans sa requête
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($post);
            $em->flush();
            return $post;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Post("/posts/media")
     */
    public function setMediaAction(Request $request)
    {
        return $request->request->all();
    }
}