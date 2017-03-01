<?php

namespace App\Forms;

use App\Model\Forms\Dialer;
use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro vytvoreni nadchazejicich udalosti
 * Class EventFactory
 *
 * @package App\Forms
 */
class EventFactory extends Nette\Object
{
	const MAX_CHARS_EVENT_NAME = 30;
	const MAX_CHARS_DESCRIPTION = 500;

	const TEXTBOX_SIZE = 30;
	const TEXTBOX_MAX_LENGTH = 100;

	const TEXT_AREA_COLS = 52;
	const TEXT_AREA_ROWS = 5;

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addText('name', 'Název události:')->setRequired('Uveďte prosím název události')
			->setAttribute('class', 'required input_field')
			->setAttribute('size', self::TEXTBOX_SIZE)
			->setRequired('Název události je povinný.')
			->addRule(Form::MAX_LENGTH, 'Název události může obsahovat pouze %d znaků', self::MAX_CHARS_EVENT_NAME);

		$form->addText('place', 'Místo konání:')
			->setMaxLength(self::TEXTBOX_MAX_LENGTH)
			->setAttribute('class', 'required input_field')
			->setAttribute('size', self::TEXTBOX_SIZE)
			->setRequired('Zvote prosím místo konání');

		$form->addSelect('type', 'Typ události:', Dialer::getTypeDialer())
			->setPrompt('Zvolte typ události')
			->setAttribute('class', 'required input_field')
			->setRequired('Zvolte prosím typ události');


		$form->addText('date', 'Den:')->setType('date')
			->setAttribute('class', 'required input_field')
			->setRequired('Zvote prosím den události');

		$form->addText('time', 'Čás:')->setType('time')
			->setAttribute('class', 'required input_field')
			->setRequired('Zvote prosím čas události události');

		$form->addTextArea('description', 'Popis události:', self::TEXT_AREA_COLS, self::TEXT_AREA_ROWS)
			->addRule(Form::MAX_LENGTH, 'Popis události může obsahovat pouze %d znaků', self::MAX_CHARS_DESCRIPTION);

		$form->addSubmit('create', 'Vytvořit');
		return $form;

	}

}
