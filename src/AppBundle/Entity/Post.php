<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use DateTime;

/**
 * @ORM\Entity()
 * @ORM\Table(name="posts")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\Column(type="date")
     */
    protected $date;

    /**
     * @ORM\Column(type="string")
     */
    protected $category;

    /**
     * @ORM\Column(type="string")
     */
    protected $subcategory;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $srcUrls;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $links;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="post")
     * @var Comment[]
     */
    protected $comments;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="post")
     * @var User
     */
    protected $user;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="post")
     * @var User[]
     */
    protected $favouriteUsers;


    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->favouriteUsers = new ArrayCollection();
        $this->srcUrls = new ArrayCollection();
        $this->links = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate()
    {
        $this->date = new DateTime();
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function getSubcategory()
    {
        return $this->subcategory;
    }

    public function setSubcategory($subcategory)
    {
        $this->subcategory = $subcategory;
        return $this;
    }

    public function getSrcUrls()
    {
        return $this->srcUrls;
    }

    public function setSrcUrls($srcUrls)
    {
        $this->srcUrls = $srcUrls;
        return $this;
    }

    public function addSrcUrls($srcUrl)
    {
        $this->srcUrls[] = $srcUrl;
        return $this;
    }

    public function removeAllSrcUrls()
    {
        $this->srcUrls = new ArrayCollection();
    }

    public function getLinks()
    {
        return $this->links;
    }

    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    public function addLinks($link)
    {
        $this->links[] = $link;
        return $this;
    }

    public function removeAllLinks()
    {
        $this->links = new ArrayCollection();
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
        $comment->setPost($this);
        return $this;
    }

    public function deleteComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
        return $this;
    }

    public function removeAllComments()
    {
        $this->comments = new ArrayCollection();
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getFavouriteUsers()
    {
        return $this->favouriteUsers;
    }

    public function setFavouriteUsers($favouriteUsers)
    {
        $this->favouriteUsers = $favouriteUsers;
        return $this;
    }

    public function addFavouriteUsers(User $favouriteUser)
    {
        $this->favouriteUsers[] = $favouriteUser;
        //$favouriteUsers->setPost($this);
        return $this;
    }

    public function removeAllFavouriteUsers()
    {
        $this->favouriteUsers = new ArrayCollection();
    }
}