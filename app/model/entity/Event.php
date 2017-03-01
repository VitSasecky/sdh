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
 * @ORM\Entity(repositoryClass="App\Entity\EventRepository")
 * @ORM\Table(name="event")
 */
class Event extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=40, options={"not null"})
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string",length=10)
	 */
	protected $type;

	/**
	 * @ORM\Column(type="string",length=500)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $date;

	/**
	 * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
	 */
	protected $created;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	protected $author;

	/**
	 * @ORM\Column(type="string",length=100)
	 */
	protected $place;

	/**
	 * @return $this
	 */
	public function setCreated()
	{
		$this->created = new DateTime('now');
		return $this;
	}

	/**
	 * @param mixed $name
	 *
	 * @return Event
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @param mixed $type
	 *
	 * @return Event
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @param mixed $description
	 *
	 * @return Event
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @param mixed $date
	 *
	 * @return Event
	 */
	public function setDate($date)
	{
		$this->date = $date;
		return $this;
	}

	/**
	 * @param mixed $author
	 *
	 * @return Event
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
		return $this;
	}

	/**
	 * @param mixed $place
	 *
	 * @return Event
	 */
	public function setPlace($place)
	{
		$this->place = $place;
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
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return DateTime
	 */
	public function getDate()
	{
		return $this->date;
	}

	/**
	 * @return mixed
	 */
	public function getCreated()
	{
		return $this->created;
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
	public function getPlace()
	{
		return $this->place;
	}


}