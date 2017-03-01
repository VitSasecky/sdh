<?php

namespace App\Forms;

use App\Model\Forms\Dialer;
use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro editaci uctu
 * Class UsersFactory
 *
 * @package App\Forms
 */
class UsersFactory extends Nette\Application\UI\Control
{
	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addHidden('id_user');
		$form->addSelect('id_role', '', Dialer::getRoleDialer())
			->setPrompt('---Neprovádět změny---');
		$form->addCheckbox('enabled');
		$form->addCheckbox('membership');
		$form->addCheckbox('notification');
		$form->addSubmit('send', 'upravit');
		//$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		return $form;
	}

}
