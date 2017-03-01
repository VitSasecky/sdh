<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 29. 12. 2016
 * Time: 14:16
 */

namespace App\Entity;

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
class CounterRepository extends EntityRepository
{
	/**
	 * Vraci aktualni denni navstevnost aplikace
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getDailyAttendance()
	{
		return $this->_em->createQueryBuilder()
			->select('count(counter.attendance)')
			->from(Counter::class, 'counter')
			->where('current_date() = date(counter.date )')
			->getQuery()
			->getSingleScalarResult();
	}


	/**
	 * Vraci aktualni mesicni navstevnost aplikace
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getMonthlyAttendance()
	{
		return $this->_em->createQueryBuilder()
			->select('count(counter.attendance)')
			->from(Counter::class, 'counter')
			->where('month(counter.date) = month(current_date())')
			->andWhere('year(counter.date) = year(current_date())')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Vraci aktualni rocni navstevnost aplikace
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @throws \Doctrine\ORM\NoResultException
	 */
	public function getYearlyAttendance()

	{
		return $this->_em->createQueryBuilder()
			->select('count(counter.attendance)')
			->from(Counter::class, 'counter')
			->where('year(counter.date) = year(current_date())')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Vraci aktualni celkovou navstevnost aplikace
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @throws \Doctrine\ORM\NoResultException
	 */
	public function getTotalAttendance()
	{
		return $this->_em->createQueryBuilder()
			->select('count(counter.attendance)')
			->from(Counter::class, 'counter')
			->getQuery()
			->getSingleScalarResult();
	}
}