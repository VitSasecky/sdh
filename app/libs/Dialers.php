<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */

namespace App\Model\Forms;


use App\Model\Entity\Role;

/**
 * Class Dialer
 * Trida statickych ciselniku
 * @package App\Model\Forms
 */
class Dialer
{
	const DIALER_MEMBERSHIP = 'member';
	const DIALER_SEX = 'sex';
	const DIALER_ROLE = 'role';

	/**
	 * Vraci specificky ciselnik
	 * @param $dialerType - 1.paramter urcuje, jaky ciselnik ma byt vracen
	 *
	 * @return array
	 */
	private static function getDialer($dialerType)
	{
		$dialer = [];
		switch ($dialerType)
		{
			case self::DIALER_MEMBERSHIP:
				$dialer = self::getMembershipDialer();
				break;
			case self::DIALER_ROLE:
				$dialer = self::getRoleDialer();
				break;
			case Dialer::DIALER_SEX:
				$dialer = self::getSexDialer();
				break;
		}
		return $dialer;
	}

	/**
	 * @param $id - ID, resp. hodnota v ciselniku
	 * @param $dialerType - typ/nazev ciselniku
	 *
	 * @return bool|mixed
	 */
	public static function getDialerText($id, $dialerType)
	{
		$dialer = self::getDialer($dialerType);
		$result = false;
		if ($id && isset($dialer[$id]))
		{
			$result = $dialer[$id];
		}
		return $result;
	}

	/**
	 * Vraci ciselnik clenstvi
	 * @return array
	 */
	public static function getMembershipDialer()
	{
		return [
			1 => 'ano',
			0 => 'ne'
		];
	}

	/**
	 * Vraci ciselnik pohlavi
	 * @return array
	 */
	public static function getSexDialer()
	{
		return
			[
				1 => 'muž',
				2 => 'žena'
			];
	}

	/**
	 * Vraci ciselnik roli
	 * @return array
	 */
	public static function getRoleDialer()
	{
		return [
			Role::CONST_ROLE_ADMIN      => 'administrátor',
			Role::CONST_ROLE_SDH_MEMBER => 'člen SDH',
			Role::CONST_ROLE_GUEST      => 'návštěvník webu'
		];
	}

	/**
	 * Vraci ciselnik typu udalosti
	 * @return array
	 */
	public static function getTypeDialer()
	{
		return [
			'Soutěž'  => 'Soutěž',
			'Schůze'  => 'Schůze',
			'Brigáda' => 'Brigáda',
			'Ostatní' => 'Ostatní'
		];
	}
}