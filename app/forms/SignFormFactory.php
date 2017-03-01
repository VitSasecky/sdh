<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Prihlasovaci formular
 * Class SignFormFactory
 *
 * @package App\Forms
 */
class SignFormFactory extends Nette\Application\UI\Control
{

	const TEXTBOX_SIZE = 30;

	/**
	 * @param $url
	 *
	 * @return Form
	 */
	public function create($url)
	{

		$form = new Form;
		$form->addText('loginName')
			->setRequired('Zadejte prosím vaše jméno.')
			->setAttribute('class', 'signForm')
			->setAttribute('size', self::TEXTBOX_SIZE);

		$form->addPassword('password')
			->setRequired('Zadetje prosím vaše heslo')
			->setAttribute('class', 'signForm')
			->setAttribute('size', self::TEXTBOX_SIZE);

		$form->addCheckbox('remember', 'Zapamatovat');
		$form->addSubmit('login', 'Přihlásit se')->setAttribute('class', 'button');
		$form->addButton('registrovat', 'Registrovat')
			->setAttribute('onclick', 'window.location.href="' . $url . '"');
		return $form;
	}

}
