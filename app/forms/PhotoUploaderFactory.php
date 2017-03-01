<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;
use Tracy\Debugger;


/**
 * Formular pro upload ruznych druuhu souboru
 * Class UploaderFactory
 *
 * @package App\Forms
 */
class PhotoUploaderFactory extends Nette\Object
{
	const TEXTBOX_SIZE = '35';

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('dirname', 'Zadejte název fotogalerie')
			->setAttribute('size', self::TEXTBOX_SIZE);

		$select = [];
		$select[0] = '---Nová fotogalerie---';
		try
		{
			/*** @var \SplFileInfo $directory */
			foreach (Nette\Utils\Finder::findDirectories('*')->in('../www/gallery/albums') as $dirname => $directory)
			{
				$select[$directory->getFilename()] = $directory->getBasename();
			}
		} catch (\Exception $e)
		{
			Debugger::log($e);
		}finally{
			ksort($select, SORT_STRING);
			$form->addSelect('directories', 'Vyberte fotogalerii', $select);
		}
		$form->addMultiUpload('uploadFiles', 'Vyberte fotografie:')
			->setRequired('Nevybral/a jste žádné fotografie k náhrání');
		$form->addSubmit('UploadFiles', 'Nahraj');
		return $form;
	}
}
