<?php

/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 13. 8. 2016
 * Time: 11:50
 */

namespace App\RSS;

use Nette\Object;

/**
 * Class RSS
 *
 * @package App\Model\Entity
 */
class Entity extends Object
{
	/**
	 * @var integer
	 */
	public $id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $created;

	/**
	 * @var string
	 */
	public $url;
}