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
use Nette\Utils\DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Entity\DocumentRepository")
 * @ORM\Table(name="document")
 */
class Document extends BaseEntity
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string",length=150)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="string",length=100)
	 */
	protected $mimeType;

	/**
	 * @ORM\ManyToOne(targetEntity="User")
	 */
	protected $author;

	/**
	 * @ORM\Column(type="decimal",length=10, scale=2)
	 */
	protected $size;

	/**
	 * @ORM\Column(type="datetime", options={"default"="CURRENT_TIMESTAMP"})
	 */
	protected $uploadedTime;

	/**
	 * @ORM\Column(type="string",length=50)
	 */
	protected $description;


	/**
	 * @ORM\Column(type="blob",options={"not null"})
	 */
	protected $content;

	/**
	 * @ORM\Column(type="string",length=50))
	 */
	protected $fileName;

	/**
	 * @ORM\Column(type="string",length=15, nullable=true))
	 */
	protected $extension;

	/**
	 * @return $this
	 */
	public function setUploadedTime()
	{
		$this->uploadedTime = new DateTime('now');
		return $this;
	}


	/**
	 * @param mixed $name
	 *
	 * @return Document
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @param mixed $extension
	 */
	public function setExtension($extension)
	{
		$this->extension = $extension;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return mixed
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}

	/**
	 * @return User
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return mixed
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @return mixed
	 */
	public function getUploadedTime()
	{
		return $this->uploadedTime;
	}

	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * @return mixed
	 */
	public function getFileName()
	{
		return $this->fileName;
	}

	/**
	 * @return mixed
	 */
	public function getExtension()
	{
		return $this->extension;
	}

	/**
	 * @param mixed $mimeType
	 *
	 * @return Document
	 */
	public function setMimeType($mimeType)
	{
		$this->mimeType = $mimeType;
		return $this;
	}

	/**
	 * @param mixed $author
	 *
	 * @return Document
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
		return $this;
	}

	/**
	 * @param mixed $size
	 *
	 * @return Document
	 */
	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * @param mixed $description
	 *
	 * @return Document
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @param mixed $content
	 *
	 * @return Document
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * @param mixed $fileName
	 *
	 * @return Document
	 */
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
		return $this;
	}
}