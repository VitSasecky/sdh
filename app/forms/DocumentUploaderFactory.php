<?php

namespace App\Forms;

use App\Model\SdhModel;
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
	const INPUT_NAME_MAX_LEN = 100;
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('name', 'Název dokumentu')
			->setMaxLength(self::INPUT_NAME_MAX_LEN);
		$form->addTextArea('description', 'Popis dokumentu')
			->setMaxLength(SdhModel::SAVE_DOCUMENTS_MAX);
		$form->addMultiUpload('uploadFiles', 'Vyberte soubory:')
			->setRequired('Nevybral/a jste žádný soubor k náhrání');
		$form->addSubmit('UploadFiles', 'Nahraj');

		return $form;
	}
}
