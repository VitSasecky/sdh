<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 29. 12. 2016
 * Time: 14:16
 */
namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @ORM\Entity(repositoryClass="App\Entity\CounterRepository")
 * @ORM\Table(name="counter")
 */
class Counter extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="decimal", options={"default"="0"})
	 */
	protected $attendance;

	/**
	 * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
	 */
	protected $date;

	/**
	 * @ORM\Column(type="string",length=50)
	 */
	protected $remoteAddress;


	/**
	 * @param null $remoteAddress
	 *
	 * @return $this
	 */
	public function addAttendance($remoteAddress = null)
	{
		$this->date = new \DateTime();
		$this->attendance++;
		$this->remoteAddress = $remoteAddress;
		return $this;
	}
}