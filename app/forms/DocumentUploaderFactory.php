<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro upload ruznych druhu souboru
 * Class UploaderFactory
 *
 * @package App\Forms
 */
class DocumentUploaderFactory extends Nette\Object
{
	const TEXTBOX_MAX_LENGTH = 50;
	const TEXTBOX_SIZE = 50;

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('name', 'Název dokumentu')
			->setMaxLength(self::TEXTBOX_MAX_LENGTH)
			->setAttribute('size', self::TEXTBOX_SIZE);
		$form->addText('description', 'Popis dokumentu')
			->setMaxLength(self::TEXTBOX_MAX_LENGTH)
			->setAttribute('size', self::TEXTBOX_SIZE);
		$form->addMultiUpload('uploadFiles', 'Vyberte soubory:')
			->setRequired('Nevybral/a jste žádný soubor k náhrání');
		$form->addSubmit('UploadFiles', 'Nahraj');

		return $form;
	}
}
