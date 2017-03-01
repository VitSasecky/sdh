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

/**
 * @ORM\Entity
 * @ORM\Table(name="subject")
 */
class Subject extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=20,options={"not null"})
	 */
	protected $type;

	/**
	 * @ORM\Column(type="string",length=50,options={"not null"})
	 */
	protected $name;


	/**
	 * @ORM\Column(type="string",length=100)
	 */
	protected $street;

	/**
	 * @ORM\Column(type="string",length=50)
	 */
	protected $city;

	/**
	 * @ORM\Column(type="string",length=5)
	 */
	protected $postcode;


	/**
	 * @ORM\Column(type="string",length=60)
	 */
	protected $township;


	/**
	 * @ORM\Column(type="string",length=40)
	 */
	protected $email;

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
	public function getStreet()
	{
		return $this->street;
	}

	/**
	 * @return mixed
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * @return mixed
	 */
	public function getPostcode()
	{
		return $this->postcode;
	}

	/**
	 * @return mixed
	 */
	public function getTownship()
	{
		return $this->township;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param mixed $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

}