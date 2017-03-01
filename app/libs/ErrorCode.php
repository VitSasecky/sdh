<?php
/**
 * Created by PhpStorm.
 * User: Vit
 * Date: 1. 3. 2017
 * Time: 18:57
 */

namespace App\Model\Entity;

/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */
use Nette\Object;

/**
 * Class ErrorCode
 *
 * @package App\Model\Entity
 */
class ErrorCode extends Object
{
	const CODE_NOT_FOUND = 404;
	const CODE_INVALID_DATA = 300;
	const CODE_FATAL_ERROR = 500;
	const CODE_DB_ERROR = 550;
}