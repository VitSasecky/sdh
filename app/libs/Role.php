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
 * Class Role
 * Konfiguracni trida, obsahuje pouze definovane role a jejich ID
 * @package App\Model\Entity
 */
class Role extends Object
{
    const CONST_ROLE_ADMIN = 1;
    const CONST_ROLE_SDH_MEMBER = 2;
    const CONST_ROLE_GUEST = 3;
}