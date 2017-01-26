<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity()
 * @ORM\Table(name="comments")
 */
class Comment
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    /**
     * @ORM\Column(type="integer")
     */
    protected $upvote;

    /**
     * @ORM\Column(type="integer")
     */
    protected $downvote;

    /**
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="Post", inversedBy="comments")
     * @var Post
     */
    protected $post;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="post")
     * @var User
     */
    protected $user;


    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getUpvote()
    {
        return $this->upvote;
    }

    public function setUpvote($upvote)
    {
        $this->upvote = $upvote;
    }

    public function getDownvote()
    {
        return $this->downvote;
    }

    public function setDownvote($downvote)
    {
        $this->downvote = $downvote;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate()
    {
        $this->date = new DateTime();;
        return $this;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function setPost($post)
    {
        $this->post = $post;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }
}