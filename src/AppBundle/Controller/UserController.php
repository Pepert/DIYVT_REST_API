<?php
namespace AppBundle\Controller;

use FOS\RestBundle\View\View;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use AppBundle\Entity\User;
use AppBundle\Form\Type\UserType;

class UserController extends Controller
{
    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->findAll();
        /* @var $users User[] */

        return $users;
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{user_id}")
     */
    public function getUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('user_id'));
        /* @var $user User */

        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $user;
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * @Rest\Post("/favourites")
     */
    public function getFavouritesPostAction(Request $request)
    {
        $userId = $request->request->get("user_id");

        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em
            ->getRepository('AppBundle:User')
            ->find($userId);

        $listPosts = $user->getFavouritePosts();

        return $listPosts;
    }

    /**
     * @Rest\View()
     * @Rest\Post("/users/{id}")
     */
    public function checkUserPassAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')
            ->find($request->get('id'));
        /* @var $user User */

        $sentPass = $request->request->get('password');

        return password_verify($sentPass, $user->getPassword());
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Post("/users/pass/{id}")
     */
    public function updatePassAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
        /* @var $user User */

        if (empty($user)) {
            return View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $password = $request->request->get("password");
        $user->setPassword($password);
        $this->get('doctrine.orm.entity_manager')->persist($user);
        $this->get('doctrine.orm.entity_manager')->flush();

        return new JsonResponse([
            'status' => 'ok'
        ]);
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED)
     * @Rest\Post("/users")
     */
    public function addUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{id}")
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('AppBundle:User')
            ->find($request->get('id'));
        /* @var $user User */

        if ($user) {
            $em->remove($user);
            $em->flush();
        }
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Put("/users/{id}")
     */
    public function updateUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('id')); // L'identifiant en tant que paramètre n'est plus nécessaire
        /* @var $user User */

        if (empty($user)) {
            return View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $firstname = $request->request->get("firstname");
        $lastname = $request->request->get("lastname");
        $screenName = $request->request->get("screenName");
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setScreenName($screenName);
        $this->get('doctrine.orm.entity_manager')->persist($user);
        $this->get('doctrine.orm.entity_manager')->flush();

        return new JsonResponse([
            'status' => 'ok'
        ]);
    }

    /**
     * @Rest\View()
     * @Rest\Post("/email")
     */
    public function sendMailAction(Request $request)
    {
        $subject = $request->request->get('subject');
        $email = $request->request->get('email');
        $content = $request->request->get('content');

        $mail = \Swift_Message::newInstance()
            ->setFrom($email)
            ->setTo('playpero@hotmail.com')
            ->setSubject($subject)
            ->setBody($content)
        ;

        $this->get('mailer')->send($mail);

        return true;
    }

    /**
     * @Rest\View()
     * @Rest\Get("/users/newpass/{user_id}")
     */
    public function getNewPassAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('AppBundle:User')
            ->find($request->get('user_id'));
        /* @var $user User */

        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $bytes = random_bytes(4);
        $newPass = bin2hex($bytes);

        $user->setPassword($newPass);

        $mail = \Swift_Message::newInstance()
            ->setFrom('DIYVT@info.com')
            ->setTo($user->getEmail())
            ->setSubject('New password - Do It Yourself VT')
            ->setBody('Your new password is '.$newPass.'. You can change your password in your profile section.')
        ;

        $this->get('mailer')->send($mail);

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->flush();

        return true;
    }
}