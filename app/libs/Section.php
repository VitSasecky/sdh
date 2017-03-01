<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */

namespace App\Model\Entity;

use Nette\Object;

/**
 * Class Section
 * Entita/Polozka sekce se zakladnimi vlastnostmi
 *
 * @package App\Model\Entity
 */
class Section extends Object
{
	/**
	 * Nazev sekce
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Akce/Pozadavek na sekci
	 *
	 * @var string
	 */
	public $action;

	/**
	 * Flag, zda je to sekce modadlni, ci nikoliv
	 *
	 * @var boolean
	 */
	public $isModal;

}