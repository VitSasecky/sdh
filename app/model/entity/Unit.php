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
use Nette\Utils\Strings;

/**
 * @ORM\Entity(repositoryClass="App\Entity\UnitRepository")
 * @ORM\Table(name="unit")
 */
class Unit extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=20)
	 */
	protected $firstName;

	/**
	 * @ORM\Column(type="string",length=50, options={"not null"})
	 */
	protected $surname;

	/**
	 * @ORM\ManyToOne(targetEntity="Position")
	 */
	protected $position;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $birthDate;

	/**
	 * @ORM\Column(type="string",length=25, nullable=true)
	 */
	protected $email;

	/**
	 * @ORM\Column(type="boolean", options={"default"="0"})
	 */
	protected $emergencyUnit;

	/**
	 * @ORM\Column(type="boolean", options={"default"="0"})
	 */
	protected $sex;

	/**
	 * @return mixed
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param mixed $firstName
	 *
	 * @return Unit
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @param mixed $surname
	 *
	 * @return Unit
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $position
	 *
	 * @return Unit
	 */
	public function setPosition($position)
	{
		$this->position = $position;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBirthDate()
	{
		return $this->birthDate;
	}

	/**
	 * @param mixed $birthDate
	 *
	 * @return Unit
	 */
	public function setBirthDate($birthDate)
	{
		$this->birthDate = $birthDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param mixed $email
	 *
	 * @return Unit
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEmergencyUnit()
	{
		return $this->emergencyUnit;
	}

	/**
	 * @param mixed $emergencyUnit
	 *
	 * @return Unit
	 */
	public function setEmergencyUnit($emergencyUnit)
	{
		$this->emergencyUnit = $emergencyUnit;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSex()
	{
		return $this->sex;
	}

	/**
	 * @param mixed $sex
	 *
	 * @return Unit
	 */
	public function setSex($sex)
	{
		$this->sex = $sex;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFullName()
	{
		$name = [];
		$name[] = Strings::trim($this->surname);
		$name[] = Strings::trim($this->firstName);
		return implode(' ', $name);
	}
}