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
class UserRepository extends EntityRepository
{
	/**
	 * Vraci celkovy pocet uzivatelu
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCount()
	{
		return $this->_em->createQueryBuilder()
			->select('count(user.id)')
			->from(User::class,'user')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Vraci celkovy pocet neaktivnich uzivatelu
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCountDisabledUsers(){
		return $this->_em->createQueryBuilder()
			->select('count(user.id)')
			->from(User::class,'user')
			->where('enabled = :disabled')
			->setParameter('disabled',0)
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Vraci celkovy pocet aktivnich uzivatelu
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCountEnabledUsers(){
		return $this->_em->createQueryBuilder()
			->select('count(user.id)')
			->from(User::class,'user')
			->where('enabled = :enabled')
			->setParameter('enabled',1)
			->getQuery()
			->getSingleScalarResult();
	}

}