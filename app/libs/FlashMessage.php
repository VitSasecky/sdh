<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */

namespace App\Model\Entity;

/**
 * Class FlashMessage
 *
 * @package App\Model\Entity
 */
class FlashMessage
{
	const WARNING = 'warning';
	const SUCCESS = 'success';
	const ERROR = 'error';
	const INFO = 'info';

	public static $flashes = [];

	/**
	 * @param $text
	 * @param $status
	 *
	 * @return int
	 */
	public static function addFlash($text, $status)
	{
		$num = count(self::$flashes);
		self::$flashes[$num]['text'] = $text;
		self::$flashes[$num]['status'] = $status;
		return $num;
	}

	/**
	 * @return mixed
	 */
	public static function getFlashes()
	{
		return self::$flashes;
	}


}