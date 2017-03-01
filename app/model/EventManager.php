<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 2. 2017
 * Time: 6:05
 */

namespace App\Model;

use App\Entity\EventRepository;
use App\Entity\Event;
use App\Model\Exceptions\EventException;
use App\Model\Exceptions\ModelException;
use Doctrine\ORM\EntityManager;
use Nette;
use Nette\Security\User;
use Tracy\Debugger;
use Tracy\Logger;

/**
 * Class Event
 * Spravce pro mainpulaci s udalostmi
 *
 * @package App\Model
 */
class EventManager extends BaseModel
{
	/**
	 * Entita uzivatele
	 *
	 * @var User
	 */
	private $user;


	/**
	 * EventManager constructor.
	 *
	 * @param EntityManager $em
	 * @param User $user
	 * @param Logger $logger
	 */
	public function __construct(EntityManager $em, User $user, Logger $logger)
	{
		$this->user = $user;
		parent::__construct($em, $logger);
	}


	/**
	 * Vytvori novou udalost dle hodnot z 1.parametru
	 *
	 * @param array $values - pole hodnot udalosti
	 *
	 * @return Event - sestavena vysledna entita udalosti
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws EventException
	 */
	public function createEvent($values)
	{
		try
		{
			$datetime = new \DateTime($values['date']);
			list($hour, $minutes) = explode(':', $values['time']);
			$datetime->setTime($hour, $minutes);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			throw new EventException($e->getMessage());
		}
		unset($values['time']);

		$event = new Event(); //nova entita ualosti
		$event->setAuthor($this->entityManager->find(\App\Entity\User::class, $this->user->getId()))
			->setName($values['name'])
			->setDate($datetime)
			->setPlace($values['place'])
			->setDescription($values['description'])
			->setType($values['type'])
			->setCreated();

		$this->check($event);
		$this->entityManager->persist($event);
		$this->entityManager->flush();
		return $event;
	}


	/**
	 * Zkontroluje cas a datum, zda jde o nadchazejici udalost
	 *
	 * @param Event $event - entita, pro kterou se provadi kontrola.
	 *
	 * @return $this
	 * @throws ModelException
	 */
	private function check(Event $event)
	{
		$actualDateTime = new Nette\Utils\DateTime();
		$actualDay = (int)$actualDateTime->format('d');
		$actualMonth = (int)$actualDateTime->format('m');
		$actualYear = (int)$actualDateTime->format('Y');

		$eventYear = (int)$event->getDate()->format('Y');
		$eventMonth = (int)$event->getDate()->format('m');
		$eventDay = (int)$event->getDate()->format('d');

		$oldEvent = false;
		if ($eventYear < $actualYear || ($eventYear === $actualYear && $actualMonth > $eventMonth))
		{
			$oldEvent = true;   //stary rok
		}

		if (!$oldEvent && (($eventYear === $actualYear) && ($actualMonth === $eventMonth) && ($eventDay < $actualDay)))
		{
			$oldEvent = true; //zkoumani mesicu ve stejnem roce, oper prosle datum
		}

		if (!$oldEvent && ($actualYear === $eventYear && $actualMonth === $eventMonth && $actualDay === $eventDay))
		{
			$actualHour = date('H');    //aktualni den musim zkoumat cas
			$actualMin = date('i');
			list($eventHour, $eventMin) = explode(':', $event->getDate()->format('H:i'));
			if ($actualHour > $eventHour)
			{
				$oldEvent = true;
			}elseif ($actualHour === $eventHour && ($actualMin >= $eventMin))
			{
				$oldEvent = true;
			}
		}

		if ($oldEvent)
		{
			throw new ModelException('Událost již proběhla, zadejte prosím nadcházející události.');
		}
		return $this;
	}

	/**
	 * Nacte udalost/i, pokud je 1.parametr true, pak nacita pouze nejblizsi nadchazejici udalost
	 *
	 * @param bool $onlyNext - pokud je true, ORM vrati pouze prvni aktivni/nadchazejici udalost
	 *
	 * @return array - vraci vysledne entity/udalosti.
	 * @throws \Exception
	 * @throws EventException
	 */
	public function getEvent($onlyNext = null)
	{
		/**
		 * @var EventRepository $repository
		 */
		$repository = $this->entityManager->getRepository(Event::class);
		return $repository->getEvents($onlyNext);
	}


}