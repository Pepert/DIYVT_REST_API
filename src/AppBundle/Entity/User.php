<?php
namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="users",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="users_email_unique",columns={"email"})}
 * )
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $firstname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $lastname;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $screenName;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $imgurl;

    /**
     * @ORM\OneToMany(targetEntity="Post", mappedBy="user")
     * @var Post[]
     */
    protected $posts;

    /**
     * @ORM\ManyToMany(targetEntity="Post", cascade={"persist"})
     * @var Post[]
     */
    protected $favouritePosts;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $upvotedPosts;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $downvotedPosts;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="user")
     * @var Comment[]
     */
    protected $comments;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->favouritePosts = new ArrayCollection();
        $this->upvotedPosts = [];
        $this->downvotedPosts = [];
        $this->comments = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    public function getScreenName()
    {
        return $this->screenName;
    }

    public function setScreenName($screenName)
    {
        $this->screenName = $screenName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);
    }

    public function getImgurl()
    {
        return $this->imgurl;
    }

    public function setImgurl($imgurl)
    {
        $this->imgurl = $imgurl;
    }

    public function getPosts()
    {
        return $this->posts;
    }

    public function setPosts($posts)
    {
        $this->posts = $posts;
        return $this;
    }

    public function addPost(Post $posts)
    {
        $this->posts[] = $posts;
        $posts->setUser($this);
        return $this;
    }

    public function removeAllPosts()
    {
        $this->posts = new ArrayCollection();
    }

    public function getFavouritePosts()
    {
        return $this->favouritePosts;
    }

    public function setFavouritePosts($favouritePosts)
    {
        $this->favouritePosts = $favouritePosts;
        return $this;
    }

    public function addFavouritePost(Post $favouritePosts)
    {
        $this->favouritePosts[] = $favouritePosts;
        $favouritePosts->addFavouriteUsers($this);
        return $this;
    }

    public function deleteFavouritePost(Post $favouritePosts)
    {
        $this->favouritePosts->removeElement($favouritePosts);
        $favouritePosts->getFavouriteUsers()->removeElement($this);
        return $this;
    }

    public function removeAllFavouritePosts()
    {
        $this->favouritePosts = new ArrayCollection();
    }

    public function getUpvotedPosts()
    {
        return $this->upvotedPosts;
    }

    public function setUpvotedPosts($upvotedPosts)
    {
        $this->upvotedPosts = $upvotedPosts;
        return $this;
    }

    public function addUpvotedPosts($upvotedPosts)
    {
        $this->upvotedPosts[] = $upvotedPosts;
        return $this;
    }

    public function removeAllUpvotedPosts()
    {
        $this->upvotedPosts = [];
    }

    public function getDownvotedPosts()
    {
        return $this->downvotedPosts;
    }

    public function setDownvotedPosts($downvotedPosts)
    {
        $this->downvotedPosts = $downvotedPosts;
        return $this;
    }

    public function addDownvotedPosts($downvotedPosts)
    {
        $this->downvotedPosts[] = $downvotedPosts;
        return $this;
    }

    public function removeAllDownvotedPosts()
    {
        $this->downvotedPosts = [];
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
        $comment->setUser($this);
        return $this;
    }

    public function removeAllComments()
    {
        $this->comments = new ArrayCollection();
    }
}