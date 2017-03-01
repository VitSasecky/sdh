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
use Nette\Security\Passwords;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;

/**
 * @ORM\Entity(repositoryClass="App\Entity\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends BaseEntity
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
	 * @ORM\Column(type="string",length=50,nullable=false)
	 */
	protected $surname;

	/**
	 * @ORM\Column(type="datetime",nullable=true)
	 */
	protected $birthDate;


	/**
	 * @ORM\Column(type="string",length=25, nullable=false)
	 */
	protected $email;

	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default"="0"})
	 */
	protected $membership;

	/**
	 * @ORM\Column(type="string",length=100, nullable=false)
	 */
	protected $password;

	/**
	 * @ORM\Column(type="string",length=25, nullable=false)
	 */
	protected $login;

	/**
	 * @ORM\Column(type="string",length=9,nullable=true)
	 */
	protected $phone;

	/**
	 * @ORM\Column(type="blob", nullable=true)
	 */
	protected $photo;

	/**
	 * @ORM\Column(type="string",length=255, nullable=true)
	 */
	protected $address;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $description;

	/**
	 * @ORM\ManyToOne(targetEntity="Role")
	 */
	protected $role;


	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default"="0"})
	 */
	protected $sex;

	/**
	 * @ORM\Column(type="datetime", nullable=false,options={"default": 0})
	 */
	private $created;


	/**
	 * @ORM\Column(type="string",length=100, nullable=true)
	 */
	protected $nickname;
	/**
	 * @ORM\Column(type="boolean", options={"default"="1"}, nullable=false)
	 */
	protected $enabled;

	/**
	 * @ORM\Column(type="boolean", nullable=false, options={"default"="1"})
	 */
	protected $notification;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $visitedAt;

	/**
	 * @ORM\Column(type="boolean",nullable=true, options={"default"="0"})
	 */
	protected $visited;

	/**
	 * @param $firstName
	 *
	 * @return $this
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
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
	 * @param $surname
	 *
	 * @return $this
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;
		return $this;
	}

	/**
	 * @param mixed $notification
	 *
	 * @return User
	 */
	public function setNotification($notification)
	{
		$this->notification = $notification;
		return $this;
	}


	/**
	 * @param mixed $birthDate
	 *
	 * @return User
	 */
	public function setBirthDate($birthDate)
	{
		if (!$birthDate instanceof DateTime)
		{
			$birthDate = new DateTime($birthDate);
		}
		$this->birthDate = $birthDate;
		return $this;
	}

	/**
	 * @param $email
	 *
	 * @return $this
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @param $membership
	 *
	 * @return $this
	 */
	public function setMembership($membership)
	{
		$this->membership = $membership;
		return $this;
	}

	/**
	 * @param $password
	 *
	 * @return $this
	 */
	public function setPassword($password)
	{
		$this->password = Passwords::hash($password);
		return $this;
	}

	/**
	 * @param $login
	 *
	 * @return $this
	 */
	public function setLogin($login = null)
	{
		if (!$login)
		{
			$login = $this->getNickname()
				?: $this->getEmail();
		}
		$this->login = $login;
		return $this;
	}

	/**
	 * @param $phone
	 *
	 * @return $this
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
		return $this;
	}

	/**
	 * @param $photo
	 *
	 * @return $this
	 */
	public function setPhoto($photo)
	{
		$this->photo = $photo;
		return $this;
	}

	/**
	 * @param $address
	 *
	 * @return $this
	 */
	public function setAddress($address)
	{
		$this->address = $address;
		return $this;
	}

	/**
	 * @param $description
	 *
	 * @return $this
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @param mixed $idRole
	 *
	 * @return User
	 */
	public function setRole($idRole)
	{
		$this->role = $idRole;
		return $this;
	}

	/**
	 * @param mixed $sex
	 *
	 * @return User
	 */
	public function setSex($sex)
	{
		$this->sex = $sex;
		return $this;
	}


	/**
	 * @param mixed $nickname
	 *
	 * @return User
	 */
	public function setNickname($nickname)
	{
		$this->nickname = $nickname;
		return $this;
	}

	/**
	 * @param mixed $enabled
	 *
	 * @return User
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
		return $this;
	}

	/**
	 * @param mixed $visitedAt
	 *
	 * @return User
	 */
	public function setVisitedAt($visitedAt)
	{
		$this->visitedAt = $visitedAt;
		return $this;
	}

	/**
	 * @param mixed $visited
	 *
	 * @return User
	 */
	public function setVisited($visited)
	{
		$this->visited = $visited;
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
	public function getNotification()
	{
		return $this->notification;
	}

	/**
	 * @return mixed
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @return mixed
	 */
	public function getSurname()
	{
		return $this->surname;
	}

	/**
	 * @return mixed
	 */
	public function getBirthDate()
	{
		return $this->birthDate;
	}

	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @return mixed
	 */
	public function getMembership()
	{
		return $this->membership;
	}

	/**
	 * @return mixed
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @return mixed
	 */
	public function getLogin()
	{
		return $this->login;
	}

	/**
	 * @return mixed
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @return mixed
	 */
	public function getPhoto()
	{
		return $this->photo;
	}

	/**
	 * @return mixed
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return Role
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @return mixed
	 */
	public function getSex()
	{
		return $this->sex;
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
	public function getNickname()
	{
		return $this->nickname;
	}

	/**
	 * @return mixed
	 */
	public function getEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @return mixed
	 */
	public function getVisitedAt()
	{
		return $this->visitedAt;
	}

	/**
	 * @return mixed
	 */
	public function getVisited()
	{
		return $this->visited;
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