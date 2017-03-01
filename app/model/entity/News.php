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
 * @ORM\Entity(repositoryClass="App\Entity\NewsRepository")
 * @ORM\Table(name="news")
 */
class News extends BaseEntity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=50, options={"not null"})
	 */
	protected $title;
	/**
	 * @ORM\Column(type="string", length=300, options={"not null"})
	 */
	protected $text;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	public $author;

	/**
	 * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
	 */
	protected $created;

	/**
	 * @param mixed $title
	 *
	 * @return News
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}

	/**
	 * @param mixed $text
	 *
	 * @return News
	 */
	public function setText($text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * @param mixed $author
	 *
	 * @return News
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setCreated()
	{
		$this->created = new DateTime('now');
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
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @return User
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