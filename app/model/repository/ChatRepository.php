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
class ChatRepository extends EntityRepository
{
	/**
	 * Vraci celkovy pocet prispevku/postu v chatu
	 *
	 * @throws \Exception
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCount()
	{
		return $this->_em->createQueryBuilder()
			->select('count(chat.id)')
			->from(Chat::class, 'chat')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Ziska celkovy pocet prispevku konkretniho uzivatele
	 *
	 * @param integer $authorId - ID autora clanku
	 *
	 * @throws \Exception
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCountPostsByUser($authorId)
	{
		return $this->_em->createQueryBuilder()
			->select('count(chat.id)')
			->from(Chat::class, 'chat')
			->where('author_id = :id')
			->setParameter('id', $authorId)
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Ziska posledni prispevek.
	 * Pokud prvni parametr neni false, tak ziska posl. prispevek def. usera v 1.parametru
	 *
	 * @param bool $authorId - ID autora clanku
	 *
	 * @return mixed
	 */
	public function getLatestPost($authorId = null)
	{
		$qb = $this->_em->createQueryBuilder()
			->select('count(chat.id)')
			->from(Chat::class, 'chat');

		if ($authorId)
		{
			$qb->where('author_id = :id')
				->setParameter('id', $authorId);
		}
		return $qb->orderBy('chat.id', 'DESC')
			->getQuery()
			->setFirstResult(1)
			->setMaxResults(1)
			->getResult();
	}

}