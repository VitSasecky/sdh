<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 6. 2. 2017
 * Time: 6:33
 */

namespace App\Model;


use App\Entity\Counter;
use Doctrine\ORM\EntityManager;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Tracy\Debugger;
use Tracy\Logger;

/**
 * Class CounterModel
 * Pocitadlo pristupu/navstev, ktere vyhodnocuje, zda ma byt pricten pristup dle cookie
 *
 * @package App\Model
 */
class CounterModel extends BaseModel
{
	/**
	 * @var IRequest $request
	 */
	private $request;

	/**
	 * @var IResponse $response
	 */
	private $response;

	/**
	 * Nazev cookie, se kterou pracuje pocitadlo pristupu
	 *
	 * @var string
	 */
	private $cookieName = 'sdh_counter';

	/**
	 * CounterModel constructor.
	 *
	 * @param EntityManager $em
	 * @param Logger $logger
	 * @param IRequest $httpRequest
	 * @param IResponse $httpResponse
	 */
	public function __construct(EntityManager $em, Logger $logger, IRequest $httpRequest, IResponse $httpResponse)
	{
		$this->request = $httpRequest;
		$this->response = $httpResponse;
		parent::__construct($em, $logger);
	}

	/**
	 * Nazev cookie pro odpocitavadlo
	 *
	 * @return string
	 */
	public function getCookieName()
	{
		return $this->cookieName;
	}

	/**
	 * Prida pristup do pocitadla navstev
	 *
	 * @param $hours
	 *
	 * @return bool
	 */
	public function addAttendance($hours)
	{
		$result = false;
		$hours = !$hours ? 3 : $hours; //default
		$counter = new Counter();
		$remoteAdress = $this->request->getRemoteAddress();
		$counter->addAttendance($remoteAdress);
		try
		{
			$this->entityManager->persist($counter);
			$this->entityManager->flush();
			$result = true;
		} catch (\Exception $e)
		{
			Debugger::log($e);
		}

		if ($result)
		{
			$this->response->setCookie($this->cookieName, 'true', time() + (3600 * $hours));
		}
		return $result;
	}

	/**
	 * Vyhodnoti dle cookie (zda vyprsela, ci stale existuje), zda ma byt pridan dalsi pristup na stranky
	 *
	 * @param int $hours - pocet hodin, po kterych zanikne cookie. Stezejni pro urceni dalsiho ci stavacihio pristupu
	 * Pokud cookie existuje, pristup byl jiz zapsan, pokud ne pocitadlo navstev se v db inkrementuje
	 *
	 * @return mixed
	 */
	public function evaluateAttendance($hours = null)
	{
		$hours = $hours !== null ? $hours : 2;
		$cookieName = 'sdh_counter';
		$attendanceApplied = $this->request->getCookie($cookieName);
		if (!$attendanceApplied)
		{
			$this->addAttendance($hours);
		}
		return $attendanceApplied;
	}
}