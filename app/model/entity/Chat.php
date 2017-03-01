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
 * @ORM\Entity(repositoryClass="App\Entity\ChatRepository")
 * @ORM\Table(name="chat")
 */
class Chat extends BaseEntity
{
	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	protected $author;

	/**
	 * @ORM\Column(type="text", options={"not null"})
	 */
	protected $comment;

	/**
	 * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
	 */
	public $created;


	/**
	 * @ORM\Column(type="decimal", options={"default"=0})
	 */
	public $deleted =0;

	/**
	 * @return $this
	 */
	public function setCreated()
	{
		$this->created = new DateTime('now');
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
	 * @param $comment
	 *
	 * @return $this
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}


	/**
	 * @return $this
	 */
	public function setAsDeleted()
	{
		$this->deleted = 1;
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
	public function getDeleted()
	{
		return $this->deleted;
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
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @return mixed
	 */
	public function getCreated()
	{
		return $this->created;
	}

}