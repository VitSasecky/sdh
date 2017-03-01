<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 29. 12. 2016
 * Time: 14:16
 */

namespace App\Entity;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;


/**
 * An EntityRepository serves as a repository for entities with generic as well as
 * business specific methods for retrieving entities.
 *
 * This class is designed for inheritance and users can subclass this class to
 * write their own repositories with business-specific methods to locate entities.
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class EventRepository extends EntityRepository
{
	/**
	 * Vraci celkovy pocet udalosti
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCount()
	{
		return $this->_em->createQueryBuilder()
			->select('count(event.id)')
			->from(Event::class, 'event')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Vraci udalosti
	 *
	 * @param bool $onlyNext - pokud je true, tak pouze nasl. udalost
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getEvents($onlyNext = null)
	{
		$eventRepository = $this->_em->getRepository(Event::class);
		$event = $eventRepository->createQueryBuilder('ev')
			->where('ev.date > :date')
			->setParameter('date', new \DateTime())
			->getQuery()
			->getResult();


		if (!$event)
		{
			throw new EntityNotFoundException('Žádná další událost není k dispozici.');
		}

		return $onlyNext   //pokud je vyzadvana pouze nasledujici udalost
			? $event[0]
			: $event;
	}


	/**
	 * @param $event
	 */
	/**
	 * @param $event
	 */
	public function getCountPreviousEvents($event)
	{
		//pktodo dodelat
	}


	/**
	 * @param $event
	 */
	/**
	 * @param $event
	 */
	public function getCountFollowingEvents($event)
	{
		//pktodo dodelat
	}
}