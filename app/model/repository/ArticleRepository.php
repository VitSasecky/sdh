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
 * Class ArticleRepository
 *
 * @package App\Entity
 */
class ArticleRepository extends EntityRepository
{
	/**
	 * Ziska celkovy pocet clanku
	 *
	 * @throws \Exception
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getCount()
	{
		return $this->_em->createQueryBuilder()
			->select('count(article.id)')
			->from(Article::class, 'article')
			->getQuery()
			->getSingleScalarResult();
	}

	/**
	 * Ziskej predchozi id clanku, pokud existuje
	 *
	 * @param $articleId - ID entity aktualnio clanku
	 *
	 * @return mixed
	 */
	public function getPreviousArticle($articleId)
	{
		$prev = $this->_em->getRepository(Article::class)
			->createQueryBuilder('ar')
			->where('ar.id > :id')
			->setParameter('id', $articleId)
			->orderBy('ar.id', 'asc')
			->getQuery()
			->setMaxResults(1)
			->getResult();

		return isset($prev[0]) ? $prev[0] : false;
	}

	/**
	 * Ziska dalsi clanek
	 * @param $articleId - ID entity aktualniho clanku
	 *
	 * @return bool
	 */
	public function getNextArticle($articleId)
	{
		$prev = $this->_em->getRepository(Article::class)
			->createQueryBuilder('ar')
			->where('ar.id > :id')
			->setParameter('id', $articleId)
			->orderBy('ar.id', 'desc')
			->getQuery()
			->setMaxResults(1)
			->getResult();


		return isset($prev[0]) ? $prev[0] : false;
	}


	/**
	 * Vrati posledni nahrany/nejnovejsi clanek
	 *
	 * @return array
	 */
	public function getLatestArticle()
	{
		return $this->_em->createQueryBuilder()
			->select('*')
			->from(Article::class, 'article')
			->orderBy('created', 'DESC')
			->getQuery()
			->setFirstResult(1)
			->setMaxResults(1)
			->getResult();
	}

}