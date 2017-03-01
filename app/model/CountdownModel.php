<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 2. 2017
 * Time: 6:45
 */

namespace App\Model;

use App\Entity\Event;
use App\Model\Exceptions\EventException;
use Tracy\Debugger;
use Tracy\Logger;

/**
 * Class CountdownModel
 * Mddel/trida, ktera slouzi pro praci s odpoctavadlem
 *
 * @package App\Model
 */
class CountdownModel extends BaseModel
{
	/**
	 * Dny
	 *
	 * @var integer
	 */
	public $days;

	/**
	 * Hodiny
	 *
	 * @var integer
	 */
	public $hours;

	/**
	 * Minuty
	 *
	 * @var double
	 */
	public $minutes;

	/**
	 * sekundy
	 *
	 * @var double
	 */
	public $seconds;


	/**
	 * Vraci pocet dni aplikovaneho odpocitavadla
	 *
	 * @return int
	 */
	public function getDays()
	{
		return $this->days;
	}

	/**
	 * Vraci pocet hodin aplikovaneho odpocitavadla
	 *
	 * @return int
	 */
	public function getHours()
	{
		return $this->hours;
	}

	/**
	 * Vraci pocet minut aplikovaneho odpocitavadla
	 *
	 * @return float
	 */
	public function getMinutes()
	{
		return $this->minutes;
	}

	/**
	 * Vraci pocet sekund aplikovaneho odpocitavadla
	 *
	 * @return float
	 */
	public function getSeconds()
	{
		return $this->seconds;
	}

	/**
	 * Vyplni odpocitavadlo na dalsi udalost
	 *
	 * @param Event $nextEvent - udalost, pro ktere bude odpocitavadlo vyhodnoceno a naplneno
	 *
	 * @return $this
	 * @throws EventException
	 */
	public function fill(Event $nextEvent)
	{
		///vyber udalost, ktera je vetsi nez nasledujici cas
		try
		{
			$datetimeNow = new \DateTime();
			$datetime = $nextEvent->getDate();
		} catch (\Exception $e)
		{
			static::$logger->log($e, Logger::EXCEPTION);
			throw new EventException($e->getMessage());
		}
		Debugger::barDump($nextEvent->getDate(), 'nasledujici udalost');
		$diff = $datetimeNow->diff($datetime);  //rozdil v case oproti aktualnimu casu

		$this->days = $diff->d;
		$this->hours = $diff->h;
		$this->minutes = $diff->i;
		$this->seconds = $diff->s;
		return $this;
	}

}