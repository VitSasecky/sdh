<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 18. 2. 2017
 * Time: 10:16
 */

namespace Doctrine\ORM;
/**
 * Class EntityFilter
 *
 * @package Doctrine\ORM
 */
class EntityFilter
{
	/**
	 * @var array
	 */
	private $filter;
	/**
	 * @var array
	 */
	private $order;

	/**
	 * @var integer|null
	 */
	private $limit;

	/**
	 * @var integer|null
	 */
	private $offset;

	/**
	 * @var string
	 */
	private $entity;

	/**
	 * EntityFilter constructor.
	 *
	 * @param null|array $filter
	 * @param null $order
	 *
	 * @internal param $entity
	 */
	public function __construct($filter= null, $order = null)
	{
		if ($filter === null)
		{
			$filter = [];
		}
		$this->filter = $filter;
		$this->order = $order;
	}

	/**
	 * @param $entity
	 *
	 * @return $this
	 */
	public function setEntity($entity)
	{
		$this->entity = $entity;
		return $this;
	}

	/**
	 * @param int|null $offset
	 *
	 * @return $this
	 */
	public function setOffset($offset = null)
	{
		$this->offset = $offset;
		return $this;
	}


	/**
	 * Nastavuje limit
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function setLimit($limit = null)
	{
		$this->limit = $limit;
		return $this;
	}

	/**
	 * Natavuje zpusob razeni
	 *
	 * @param array $order
	 *
	 * @return $this
	 */
	public function setOrder($order = null)
	{
		$this->order = $order;
		return $this;
	}



	/**
	 * @return array
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * Vraci nastaveny zpusob razeni
	 *
	 * @return array
	 */
	public function getOrder()
	{
		return $this->order;
	}

	/**
	 * @return string
	 */
	public function getEntity()
	{
		return $this->entity;
	}

	/**
	 * Vraci nastaveny limit
	 *
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @return int|null
	 */
	public function getOffset()
	{
		return $this->offset;
	}
}