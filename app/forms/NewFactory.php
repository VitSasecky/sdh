<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro nahrani/vlozeni strucnych novinek/informaci
 * Class ContactFormFactory
 *
 * @package App\Forms
 */
class NewFactory extends Nette\Application\UI\Control
{

	const TEXT_AREA_COLS = 72;
	const TEXT_AREA_ROWS = 5;
	const TEXT_AREA_MAX_LENGTH = 300;

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('title', 'Zadejte titulek')
			->addRule(Form::MAX_LENGTH, 'Titulek může obsahovat nejvýše %d znaků', 50)
			->setRequired('Titulek je povinný.');

		$form->addTextArea('text', 'Vložte novinku:')
			->setMaxLength(self::TEXT_AREA_MAX_LENGTH)
			->setRequired('Vložte obsah');

		$form->addSubmit('uploadFile', 'Nahraj novinku');
		return $form;
	}

}
