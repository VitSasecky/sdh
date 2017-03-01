<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro aktivaci/deaktivaci uzivatele
 * Class RegistrationFactory
 *
 * @package App\Forms
 */
class AccountFactory extends Nette\Application\UI\Control
{
	/**
	 * @param $actualStatus
	 *
	 * @return Form
	 */
	public function create($actualStatus)
	{
		$actualStatus = (boolean)$actualStatus;
		$form = new Form;
		$form->addCheckbox('delete', 'deaktivace účtu')
			->setDefaultValue($actualStatus);
		$form->addSubmit('send', 'deaktivovat');
		//$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		return $form;
	}
}
