<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 2. 2017
 * Time: 6:05
 */
namespace App\Model;


use App\Model\Exceptions\ModelException;
use Doctrine\ORM\EntityFilter;
use Doctrine\ORM\EntityManager;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Object;
use Tracy\Logger;
use DoctrineExtensions\Query\Mysql\Date;
use DoctrineExtensions\Query\Mysql\Month;
use DoctrineExtensions\Query\Mysql\Year;


/**
 * Zaklad vsech modelu pracujici s ORM
 * Class BaseModel
 *
 * @package App\Model
 */
class BaseModel extends Object
{
	/**
	 * Slouzi k logovani/protokolovani
	 *
	 * @var Logger
	 */
	public static $logger;

	/**
	 * Entita, ktera umoznuje ORM manipulaci
	 *
	 * @var EntityManager
	 */
	public $entityManager;


	/**
	 * BaseModel constructor.
	 *
	 * @param EntityManager $em
	 * @param Logger $logger
	 */
	public function __construct(EntityManager $em, Logger $logger)
	{
		$this->entityManager = $em;
		self::$logger = $logger;
	}

	/**
	 * K vyuziti extension je potreba volat tuto metodu, ktera roziruje ORM manipulaci
	 * s databazovymi metodami year, month, date
	 *
	 * @throws \Exception
	 * @return $this
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function startup()
	{
		$this->entityManager->getConfiguration()->addCustomDatetimeFunction('YEAR', Year::class);
		$this->entityManager->getConfiguration()->addCustomDatetimeFunction('MONTH', Month::class);
		$this->entityManager->getConfiguration()->addCustomDatetimeFunction('DATE', Date::class);
		return $this;
	}

	/**
	 * Zaloguje zpravu s levelem ve 2.parametru
	 *
	 * @param $message
	 * @param $priority
	 *
	 * @return $this
	 */
	public function log($message, $priority)
	{
		self::$logger->log($message, $priority);
		return $this;
	}

	/**
	 * Zjisti pocet entit v databazi
	 *
	 * @param $entityName
	 *
	 * @throws \Exception
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCountItems($entityName)
	{
		$qb = $this->entityManager->createQueryBuilder();
		return $qb->select(
			$qb->expr()->count('u'))
			->from($entityName, 'u')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Nacte entitu/y
	 *
	 * @param EntityFilter $filter - filter polozek
	 *
	 * @return array
	 *
	 */
	public function getItems(EntityFilter $filter)
	{
		$repository = $this->entityManager->getRepository($filter->getEntity());
		return $repository->findBy($filter->getFilter(), $filter->getOrder(), $filter->getLimit(), $filter->getOffset());
	}

	/**
	 * Nacte/vytvori entitu s id v 2.parametru
	 *
	 * @param $entityName
	 * @param $id
	 *
	 * @return BaseEntity
	 * @throws ModelException
	 */
	public function getItem($entityName, $id)
	{
		$item = $this->entityManager->getRepository($entityName)->find($id);
		if (!$item instanceof BaseEntity)
		{
			throw new ModelException(
				sprintf('entita %s nebyla nalezena', $entityName)
			);
		}
		return $item;
	}

	/**
	 * Odstrani entitu/polozku dle id v 2.parametru
	 *
	 * @param $entityName - nazev, ktery slouzi k identifikaci ORM entity
	 * @param $id - id entity
	 *
	 * @throws \Exception
	 * @return $this
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 */
	public function deleteItem($entityName, $id)
	{
		$this->entityManager->remove($this->getItem($entityName, $id));
		$this->entityManager->flush();
		return $this;
	}

}