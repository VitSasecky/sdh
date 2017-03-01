<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */

namespace App\Model;

use App\Model\Entity\FlashMessage;
use Nette\DirectoryNotFoundException;
use Nette\Http\FileUpload;
use Nette\Utils\Finder;
use Nette\Utils\Strings;
use Nette\Utils\Image;
use Tracy\Debugger;


/**
 * Loader, ktery projede definovanou slozku a hleda vsechny typy souboru, ktere nasledne zpracuje.
 * Class GalleryLoader
 *
 * @package App\Model
 */
class Loader
{
	/**
	 * @var int
	 */
	private $imageSize;

	/**
	 * @var int
	 */
	private $quality;
	/**
	 * @var int
	 */
	private $thumbWidth;

	/**
	 * @var array
	 */
	private static $images = ['*.jpg', '*.png', '*.bmp', '*.gif'];

	/**
	 * @var array
	 */
	private static $extensions = ['jpg', 'png', 'bmp', 'gif'];

	/**
	 * @var string
	 */
	private $photoDir;

	/**
	 * @var int
	 */
	private $thumbQuality;

	/**
	 * @var string
	 */
	private $folder;


	/**
	 * Vytvori thumb slozku fotogalerie (pro zmensene fotky)
	 *
	 * @return bool
	 */
	private function crateThumbFolder()
	{
		$res = false;
		chmod($this->photoDir . '/thumbs', 0777);
		if (mkdir($this->photoDir . '/thumbs') && !is_dir($this->photoDir . '/thumbs'))
		{
			$res = true;
		}
		return $res;
	}

	/**
	 * Vytvori/sestavi novou fotogalerii
	 *
	 * @return $this
	 */
	private function build()
	{
		$thumbFolder = $this->crateThumbFolder(); //vytvori thumb slozku
		foreach (Finder::findFiles(self::$images)->from($this->photoDir)->exclude('thumbs') as $key => $file)
		{
			$image = null;

			/*** @var \SplFileInfo $file */
			$newFileName = Strings::fixEncoding($file); //odstrani neplatne znaky napr. diakritiku
			$newFileName = empty($newFileName)
				? $file->getPath() . DIRECTORY_SEPARATOR . time() . $file->getExtension()
				: $newFileName;

			rename($file, $newFileName);

			try
			{
				$image = Image::fromFile($newFileName);
			} catch (\Exception $e)
			{
				Debugger::log($e);
				continue;
			}

			if (!$image instanceof Image)
			{
				continue;
			}

			try
			{
				$image->resize($this->imageSize, null, Image::SHRINK_ONLY);
				$image->save($newFileName, $this->quality);
				if ($thumbFolder && in_array(strtolower($file->getExtension()), self::$extensions, true))
				{
					$thumb = $this->photoDir . '/thumbs/' . $file->getBasename();
					if (!file_exists($thumb))
					{
						$this->makeThumb($file, $thumb);
					}
				}
			} catch (\Exception $e)
			{
				Debugger::dump($e);
				continue;
			}
		}
		return $this;
	}


	/**
	 * Vytvori thumb soubor do thumb podslozky definovane v 2.parametru
	 *
	 * @param \SplFileInfo $file
	 * @param $dest
	 *
	 * @return bool
	 */
	private
	function makeThumb(\SplFileInfo $file, $dest)
	{
		$res = false;
		$image = null;
		try
		{
			$image = Image::fromFile($file);
		} catch (\Exception $e)
		{
			Debugger::log($e);
		}

		if ($image instanceof Image)
		{
			$image->resize($this->thumbWidth, null, Image::SHRINK_ONLY);
			$image->sharpen();
			try
			{
				if ($image->save($dest, $this->thumbQuality))
				{
					$res = true;
				}
			} catch (\Exception $e)
			{
				Debugger::log($e);
				$res = false;
			}
		}
		return $res;
	}

	/**
	 * Nahraje soubory z 1 parametru do slozky v 2.parametru
	 *
	 * @param $files
	 * @param $folder
	 * @param $createDir
	 *
	 * @return $this
	 */
	protected
	function upload($files, $folder, $createDir = null)
	{
		$success = 0;
		/**
		 * @var FileUpload $file
		 */
		if (is_array($files))
		{
			foreach ($files as $file)
			{
				$splFileInfo = new \SplFileInfo($file->getName());
				if (!in_array(Strings::lower($splFileInfo->getExtension()), self::$extensions, true))
				{
					FlashMessage::addFlash(
						sprintf('Soubor: "%s" není obrázek, a proto nebude součástí galerie.'
							, $file->getName())
						, FlashMessage::SUCCESS
					);
					continue;
				}

				if ($file->getImageSize())
				{
					$name = Strings::toAscii($file->getName());
					$fileName = $this->photoDir . DIRECTORY_SEPARATOR . $name;
					if (file_exists($fileName))
					{
						$newName = time() . '_' . $name;
						$fileName = $this->photoDir . DIRECTORY_SEPARATOR . $newName;
						FlashMessage::addFlash(
							sprintf('Soubor: "%s" již ve fotogalerii: "%s" existuje. Nový přodělěný název souboru je: "%s"'
								, $name, $folder, $newName), FlashMessage::WARNING
						);
					}
					if (!rename($file->getTemporaryFile(), $fileName))
					{
						FlashMessage::addFlash('Obrázek: "%s" se nepodařilo zařadit do fotogalerie', FlashMessage::ERROR);
						continue;
					}
					$success++;
				}
			}
		}

		$state = FlashMessage::WARNING;
		if ($success)
		{
			$message = sprintf('Vaše soubory byly úspěšně do fotogalerie: "%s" přidány. Celkem úspěšně přidaných souborů: %d'
				, $this->folder, $success
			);
			$state = FlashMessage::SUCCESS;
		} else
		{
			$message = sprintf('Žádné nové footgrafie nebyly do fotogalerie: "%s" přidány.', $this->folder);
			if ($createDir === null)
			{
				$message = sprintf('Vytvořená fotogalerie: "%s" je prázdná, a proto byla smazána.', $this->folder);
			}
		}
		FlashMessage::addFlash($message, $state);
		$this->build();
		return $this;
	}

	/**
	 * Loader constructor.
	 *
	 * @param $params
	 */
	public
	function __construct($params)
	{
		$this->photoDir = $params['photos']['dir'];
		$this->imageSize = $params['photos']['size'];
		$this->quality = $params['photos']['quality'];
		$this->thumbWidth = $params['photos']['thumbWidth'];
		$this->thumbQuality = $params['photos']['thumbQuality'];
	}

	/**
	 * Prida fotky do slozky definovane v 2.parametru
	 *
	 * @param $files
	 * @param $folder
	 *
	 * @throws DirectoryNotFoundException
	 * @return $this
	 * @throws \Nette\DirectoryNotFoundException
	 */
	public
	function addPhotos($files, $folder)
	{
		if (!$folder)
		{
			FlashMessage::addFlash(
				'Zadejte prosím název fotogalerie, nevo zvolte již existující.',
				FlashMessage::ERROR
			);
			return $this;
		}

		$createDir = false;
		$this->folder = Strings::toAscii($folder);
		$this->photoDir = $this->photoDir . DIRECTORY_SEPARATOR . $this->folder;
		if (!is_dir($this->photoDir))
		{
			if (!mkdir($this->photoDir, 0777, true) && !is_dir($this->photoDir))
			{
				throw new DirectoryNotFoundException(
					sprintf('Došlo k systémové chybě, fotogalerie: "%s" nebude vytvořena',
						$this->folder)
				);
			}
			$createDir = true;
			FlashMessage::addFlash(sprintf('Fotoglarie: "%s" byla úspěšně vytvořena.', $this->folder), FlashMessage::SUCCESS);
		}
		return $this->upload($files, $folder, $createDir);
	}
}