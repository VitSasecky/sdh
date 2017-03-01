<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 21. 1. 2017
 * Time: 10:42
 */

namespace App\Model;


use App\Model\Exceptions\PageException;

/**
 * Class Paginator
 * Trida, ktera slouzi k sestaveni strankovace
 *
 * @package App\Model
 */
class Paginator
{
	/**
	 * @var integer
	 */
	private $defaultPage = 1;

	/**
	 * @var integer
	 */
	private $currentPage;

	/**
	 * @var integer
	 */
	private $previousPage;

	/**
	 * @var integer
	 */
	private $limit;

	/**
	 * @var integer
	 */
	private $totalItems;
	/**
	 * @var integer
	 */
	private $offset;

	/**
	 * @var integer
	 */
	private $nextPage;

	/**
	 * @var integer
	 */
	private $lastItem;

	/**
	 * @var integer
	 */
	private $totalPages;


	/**
	 * Paginator constructor.
	 *
	 * @param $currentPage
	 * @param int $limit
	 * @param int $totalItems
	 * @param int $defaultPage
	 *
	 * @throws PageException
	 */
	public function __construct($currentPage, $limit, $totalItems, $defaultPage = null)
	{
		if ($limit < 1)
		{
			throw new PageException('Neni definovan pocet polozek');
		}
		$this->limit = $limit;                                  //limit = celkem polozek
		$this->currentPage = $currentPage !== null ? $currentPage : 1;   //soucasna stranka
		$this->defaultPage = $defaultPage !== null ? $defaultPage : 1;   //vychozi stranka

		//$this->offset = ($this->currentPage - 1) * $this->limit + 1;
		$this->offset = ($this->currentPage - 1) * $this->limit;
		$this->totalItems = $totalItems;

		if ($this->limit < $this->totalItems)
		{
			$this->lastItem = $this->limit * $this->currentPage;
			if ($this->lastItem > $this->totalItems)
			{
				$this->lastItem = $totalItems;
			}
		}
		$this->totalPages = ceil($totalItems / $limit); //celkovy pocet stranek
		$this->previousPage = $this->currentPage > $this->defaultPage //predchozi stranka
			? $this->currentPage - 1
			: 0;

		$this->nextPage = $this->currentPage <= $this->totalPages //nasledujici stranka
			? $this->currentPage + 1
			: 0;
	}


	/**
	 * Vraci cislo prvni polozky na  aktualni strance
	 *
	 * @return int
	 */
	public function getFirstItemOfPage()
	{
		return $this->offset + 1;
	}

	/**
	 * Vraci cislo posleni polozky na aktualni strance
	 *
	 * @return int
	 */
	public function getLastItemOfPage()
	{
		return $this->lastItem;
	}

	/**
	 * Celkem polozek na stranku
	 *
	 * @return int
	 */
	public function getTotalItems()
	{
		return $this->totalItems;
	}


	/**
	 * Zjisti celkovy pocet stranek
	 *
	 * @return float|int
	 */
	public function getTotalPages()
	{
		return $this->totalPages;
	}

	/**
	 * Vraci aktualni stranku
	 *
	 * @return mixed
	 */
	public function getPage()
	{
		return $this->currentPage;
	}

	/**
	 * Zjisti pocatek stranky|prvni polozka stranky = offset
	 *
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * Zjisti nasledujici stranku
	 *
	 * @return int
	 */
	public function getNextPage()
	{
		return $this->nextPage;
	}


	/**
	 * Zjisti limit = pocet polozek stranky
	 *
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * Zjisti vychozi strankz
	 *
	 * @return int
	 */
	public function getDefaultPage()
	{
		return $this->defaultPage;
	}

	/**
	 * Zjist predchozi stranku vuci aktualni strance
	 *
	 * @return int
	 */
	public function getPreviousPage()
	{
		return $this->previousPage;
	}
}