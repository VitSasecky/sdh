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
 * @ORM\Entity(repositoryClass="App\Entity\SponsorRepository")
 * @ORM\Table(name="sponsor")
 */
class Sponsor extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=150, options={"not null"})
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string",length=255)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="string",length=200)
	 */
	protected $url;
}