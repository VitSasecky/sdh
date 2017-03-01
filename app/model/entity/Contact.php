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
 * @ORM\Entity(repositoryClass="App\Entity\ContactRepository")
 * @ORM\Table(name="contact")
 */
class Contact extends BaseEntity
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=40)
	 */
	protected $type;

	/**
	 * @ORM\Column(type="string", length=40, options={"not null"})
	 */
	protected $email;

}