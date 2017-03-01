<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Kontaktni formular
 * Class ContactFormFactory
 *
 * @package App\Forms
 */
class ContactFormFactory extends Nette\Application\UI\Control
{
	const TYPE_ADMIN = 'ADMIN';
	const TYPE_SDH = 'SDH';
	const TYPE_COMMANDER = 'COMMANDER';

	const MAX_CHARS_MESSAGE = 10000;
	const MIN_CHARS_MESSAGE = 10;

	const TEXTBOX_SIZE = 40;
	const TEXTBOX_SIZE_SUBJECT = 60;

	const TEXT_AREA_COLS = 61;
	const TEXT_AREA_ROWS = 5;


	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form();
		$form->addText('author', 'Autor:')
			->setAttribute('size', self::TEXTBOX_SIZE)
			->setRequired('Zadejte prosím vaše jméno.')
			->setAttribute('class', 'required input_field');

		$form->addText('email', 'Email:')
			->setType('email')
			->setAttribute('class', 'required input_field')
			->setAttribute('size', self::TEXTBOX_SIZE)
			->setRequired('Zadejte prosím vaši emailovou adresu.')
			->addRule(Form::EMAIL, 'Zadaný email: %s není validní', $form['email']);

		$form->addText('subject', 'Předmět:')
			->setAttribute('size', self::TEXTBOX_SIZE_SUBJECT)
			->setRequired('Zadejte prosím předmět k emailu.')
			->setAttribute('class', 'required input_field')
			->addRule(Form::MIN_LENGTH, 'Předmět musí obsahovat alespoň %d znaky', 2);

		$form->addTextArea('message', 'Zpráva:', self::TEXT_AREA_COLS, self::TEXT_AREA_ROWS)
			->setRequired('Zpráva nebyla vyplněna.')
			->setAttribute('size', self::TEXTBOX_SIZE_SUBJECT + 5)
			->setAttribute('id', 'text')
			->setAttribute('class', 'validate-email required input_field')
			->addRule(Form::MIN_LENGTH, 'Zpráva musí obsahovat alespoň %d znaků', self::MIN_CHARS_MESSAGE)
			->addRule(Form::MAX_LENGTH, 'Zpráva je příliš dlouhá', self::MAX_CHARS_MESSAGE);

		$form->addSubmit('send', 'Odeslat');
		return $form;
	}
}
