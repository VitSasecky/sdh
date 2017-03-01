<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 29. 12. 2016
 * Time: 14:16
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Entity\ArticleRepository")
 * @ORM\Table(name="article")
 */
class Article extends BaseEntity
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=100)
	 */
	protected $title;

	/**
	 * @ORM\Column(type="text",columnDefinition="blob not null")
	 */
	protected $content;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	public $author;

	/**
	 * @ORM\Column(type="datetime",options={"default"="CURRENT_TIMESTAMP"})
	 */
	public $created;

	/**
	 * @return $this
	 */
	public function setCreated()
	{
		$this->created = new DateTime('now');
		return $this;
	}

	/**
	 * @param $title
	 *
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	return $this;
	}

	/**
	 * @param $content
	 *
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * @param $author
	 *
	 * @return $this
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return mixed
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return mixed
	 */
	public function getCreated()
	{
		return $this->created;
	}
}