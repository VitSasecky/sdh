<?php

/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */

namespace App\Model;

use App\Entity\User,
	App\Entity\Role;
use App\Model\Exceptions\ModelException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Nette;
use Nette\Security\Passwords;


/**
 * Class UserManager
 * Manager pro spravu a manipulaci uzivatelu
 *
 * @package App\Model
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	/**
	 * @var EntityManager
	 */
	private $entityManager;

	/**
	 * UserManager constructor.
	 *
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * Pokusi se autentizovat uzivatele, v pripade chyby vyhodi vyjimku
	 *
	 * @param array $credentials - pole s loginem a zakryptovanym heslem
	 *
	 * @return Nette\Security\Identity - vrati identitu uzivatele
	 * @throws \Exception
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		$foundedUser = false;
		list($username, $password) = $credentials;
		$userRepository = $this->entityManager->getRepository(User::class);
		$users = $userRepository->findBy(['login' => $username, 'enabled' => true]);
		if (!$users)
		{
			throw new Nette\Security\AuthenticationException(
				sprintf('Uživatelský účet: %s neexistuje, nebo není aktivní', $username),
				self::IDENTITY_NOT_FOUND
			);
		}

		$invalidPass = false;
		/*** @var User $user */
		foreach ($users as $user)
		{
			if (!Passwords::verify($password, $user->getPassword()))
			{
				$invalidPass = true;
				continue;
			} elseif (Passwords::needsRehash($user->getPassword()))
			{
				$user->setPassword($password); //rehash hesla
			}

			/*** @var User $foundedUser */
			$foundedUser = $user;
			break;
		}

		if ($invalidPass)
		{
			throw new Nette\Security\AuthenticationException('Heslo je nesprávné.', self::INVALID_CREDENTIAL);
		}
		return $this->getUserData($foundedUser);
	}

	/**
	 * Modifikuje entitu uzivatele dle hodnot z pole 1.parametru
	 *
	 * @param $values
	 * @param bool $onlyConfiguration
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws ModelException
	 */
	public function modifyUser($values, $onlyConfiguration = null)
	{
		/*** @var User $user */
		$user = $this->entityManager->getRepository(User::class)->find($values['id_user']);
		if (!$user)
		{
			throw new ModelException('Došlo k systémové chybě, veškeré změny nebudou provedeny');
		}

		if (isset($values['id_role']) && $values['id_role'] > 0)
		{
			$user->setRole($this->entityManager->find(Role::class, $values['id_role']));
		}

		if (!$onlyConfiguration)
		{
			$user->setFirstName($values['firstname'])
				->setSurname($values['surname'])
				->setBirthDate($values['birth'])
				->setEmail($values['email'])
				->setSex($values['sex'])
				->setNickname($values['nickname'])
				->setPhone($values['phone'])
				->setAddress($values['address']);
		} else
		{
			$user->setNotification($values['enabled']);
		}

		$user->setMembership($values['membership'])
			->setNotification($values['notification']);

		if (isset($values['changePhoto']) && $values['changePhoto'] === true)
		{
			$photo = null;
			if (isset($values['userPhoto']) && $values['userPhoto'])
			{
				$photo = $values['userPhoto'];
				/**
				 * @var Nette\Http\FileUpload $photo
				 */
				$values['userPhoto'] = $photo->getContents();
			}
			$user->setPhoto($values['userPhoto']);
		}

		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $this;
	}

	/**
	 * Aktivuje/deaktivuje uzivatele dle ID z 2.parametru
	 *
	 * @param $status
	 * @param $userId - ID entity uzivatele
	 *
	 * @return User - vrati entitu uzivatele se zmenou stavu
	 */
	private function changeActivityStatus($status, $userId)
	{
		/*** @var \App\Entity\User $user */
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		$user = $this->entityManager->getRepository(\App\Entity\User::class)->find($userId);
		if ($userId)
		{
			$user->setEnabled($status);
		}
		return $user;
	}

	/**
	 * Deaktivuje uzivatele dle ID z 1.parametru
	 *
	 * @param $userId - ID entity uzivatele
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function deactivateUser($userId)
	{
		$user = $this->changeActivityStatus(false, $userId);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $this;
	}

	/**
	 * Aktivuje uzivatele dle ID z 1.parametru
	 *
	 * @param $userId - ID uzivatele, jenz ma byt aktivovan
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function activateUser($userId)
	{
		$user = $this->changeActivityStatus(true, $userId);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $this;
	}

	/**
	 * Nacte vsechny enttity/uzivatele.
	 * Omezeni pouze na nacteni aktivnich uzivatelu lze omezit 1.parametrem
	 *
	 * @param bool $onlyActivated - Pokud je true, tak vraci pouze aktivni uzivatele
	 *
	 * @return array - pole entit uzivatelu
	 */
	public function getAlUsers($onlyActivated = null)
	{
		$criteria = [];
		if ($onlyActivated)
		{
			$criteria = ['enabled' => true];
		}
		return $this->entityManager
			->getRepository(User::class)
			->findBy($criteria, ['surname' => 'ASC']);
	}

	/**
	 * Zkontroluje uzivatele
	 *
	 * @param User $checkedUser
	 *
	 * @return bool - vraci true v pripade uspechu, jinak false
	 * @throws Nette\Security\AuthenticationException
	 */
	private function checkUser(User $checkedUser)
	{
		$userRepository = $this->entityManager->getRepository(User::class);
		$users = $userRepository->findBy(
			[
				'login' => $checkedUser->getLogin(),
				'email' => $checkedUser->getEmail()
			]
		);

		if ($users)
		{
			/*** @var User $user */
			foreach ($users as $user)
			{
				if ($checkedUser->getLogin() === $user->getLogin())
				{
					$errorMessage = sprintf('Přihlašovací jméno: %s již existuje, zvolte prosím jiné', $user->getLogin());
					throw new Nette\Security\AuthenticationException($errorMessage, self::INVALID_CREDENTIAL);
				} elseif ($checkedUser->getEmail() === $user->getEmail())
				{
					$message = sprintf('Email: %s je již přiřazen j k jinému účtu', $user->getEmail());
					throw new Nette\Security\AuthenticationException($message, self::INVALID_CREDENTIAL);
				}
			}
		}
		return true;
	}


	/**
	 * Vytvori novou entitu uzivatele
	 *
	 * @param $values - proprety entity noveho uzivatele
	 *
	 * @return User - v priapde uspechu vraci entitu noveho uzivatele
	 * @throws \Nette\Security\AuthenticationException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	private function createUser($values)
	{
		$user = new User(); //sestavi entitu uzivatele
		$user->setFirstName($values['firstname'])
			->setSurname($values['surname'])
			->setEmail($values['email'])
			->setNickname($values['nickname'])
			->setLogin();

		$this->checkUser($user); //zkontroluje data
		$user->setPassword($values['password'])
			->setSex($values['sex'])
			->setBirthDate($values['birth'])
			->setAddress($values['address'])
			->setPhone($values['phone'])
			->setRole(
				$this->entityManager->find(Role::class, $values['role'])
			)
			->setMembership($values['membership'])
			->setDescription($values['description'])
			->setNotification(true)
			->setEnabled(true)
			->setCreated();

		if ($values['userPhoto'] instanceof Nette\Http\FileUpload)
		{
			$user->setPhoto($values->userPhoto->getContents());
		}
		$this->entityManager->persist($user); //provede ulozeni noveho uzivatele
		$this->entityManager->flush();
		return $user;
	}

	/**
	 * Prida noveho uzivatele a vraci jeho login
	 *
	 * @param $values - property entity uzivatele
	 *
	 * @return mixed - vraci login nove entity/uzivatele
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws Nette\Security\AuthenticationException
	 */
	public function addUser($values)
	{
		$user = $this->createUser($values);
		return $user->getLogin(); //pokud se podarilo pridat uzivatele
	}

	/**
	 * Vrati identitu/property uzivatele
	 *
	 * @param $foundedUser - entita nalezeneho uzivatele
	 *
	 * @return Nette\Security\Identity
	 * @throws \Exception
	 */
	public function getUserData(User $foundedUser)
	{
		if (!$foundedUser)
		{
			throw new EntityNotFoundException('Přihlášení se nezdařilo, uživatel nebyl nalezen.');
		}

		$photoBinary = null;
		if ($foundedUser->getPhoto())
		{
			$info = fstat($foundedUser->getPhoto());
			$photoBinary = fread($foundedUser->getPhoto(), $info['size']);
		}

		return new Nette\Security\Identity(
			$foundedUser->getId(),
			$foundedUser->getRole()->getId()
			, [
				'LOGIN'        => $foundedUser->getLogin(),
				'FIRSTNAME'    => $foundedUser->getFirstName(),
				'SURNAME'      => $foundedUser->getSurname(),
				'EMAIL'        => $foundedUser->getEmail(),
				'NOTIFICATION' => $foundedUser->getNotification(),
				'DESCRIPTION'  => $foundedUser->getDescription(),
				'ID_ROLE'      => $foundedUser->getRole()->getId(),
				'PHOTO'        => $photoBinary
			]
		);
	}
}

