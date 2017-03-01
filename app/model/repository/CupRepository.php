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
class CupRepository extends EntityRepository
{
	/**
	 * Vraci celkovy pocet poharu dosazene druzstvem SDH Babice
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCount()
	{
		return $this->_em->createQueryBuilder()
			->select('count(cup.id)')
			->from(Cup::class, 'cup')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Ziska celkovy pocet poharu daneho umisteni dle 1.parametru
	 *
	 * @param $position - konkretni SDH umisteni
	 *
	 * @return mixed
	 */
	public function getCountCupsOfPosition($position)
	{
		return $this->_em->createQueryBuilder()
			->select('count(cup.id)')
			->from(Cup::class, 'cup')
			->where('position = :pos')
			->setParameter('pos', $position)
			->getQuery()
			->setMaxResults(1)
			->getResult();
	}

}