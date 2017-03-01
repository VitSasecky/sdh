<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro upload clanku
 * Class ContactFormFactory
 *
 * @package App\Forms
 */
class ArticleFactory extends Nette\Application\UI\Control
{
	const TEXT_SIZE = 119;
	const TEXT_AREA_COLS = 120;
	const TEXT_AREA_ROWS = 23;

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('title', 'Zadejte název článku')
			->setAttribute('size', self::TEXT_SIZE)
			->setRequired('Název článku je povinný.');
		$form->addTextArea('content', 'Vložte obsah článku:', self::TEXT_AREA_COLS, self::TEXT_AREA_ROWS)
			->setHtmlAttribute('id','ck_editor')
			->setRequired('Obsah článků je povinný.')
			->setHtmlAttribute('class','area_hidden');
		$form->addSubmit('uploadFile', 'Nahraj článek');
		return $form;
	}

}
