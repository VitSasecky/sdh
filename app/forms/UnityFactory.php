<?php

namespace App\Forms;

use App\Model\Forms\Dialer;
use App\Model\SdhModel;
use Nette,
	Nette\Application\UI\Form;

/**
 *  Formular pro pridani novych clenu
 * Class RegistrationFactory
 *
 * @package App\Forms
 */
class UnityFactory extends Nette\Application\UI\Control
{
	const TEXTBOX_SIZE = 36;

	/**
	 * @param SdhModel $model
	 *
	 * @return Form
	 */
	public function create(SdhModel $model)
	{
		$form = new Form;
		$form->addText('first_name', 'Jméno:')
			->setAttribute('class', 'required input_field')
			->setAttribute('size', self::TEXTBOX_SIZE)
			->addCondition(Form::FILLED)
			->addRule(Form::MIN_LENGTH, 'Křestní jméno musí obsahovat alespoň %d znaky', 2)
			->addRule(Form::MAX_LENGTH, 'Křestní jméno může obsahovat pouze %d znaků', 20);

		$form->addText('surname', 'Příjmení:')
			->setAttribute('class', 'required input_field')
			->setAttribute('size', self::TEXTBOX_SIZE)
			->setRequired('Zadejte prosím příjmení člena.')
			->addRule(Form::MIN_LENGTH, 'Příjmení musí obsahovat alespoň %d znaky', 2)
			->addRule(Form::MAX_LENGTH, 'Příjmení může obsahovat pouze %d znaků', 50);

		$form->addSelect('position', 'Vyberte pozici', $model->getPositionDialer());

		$form->addRadioList('sex', 'Pohlaví:', Dialer::getSexDialer())
			->setDefaultValue(1);
		$form->addRadioList('membership', 'Člen jednotky:', Dialer::getMembershipDialer())
			->setDefaultValue(0);

		/**
		 * @var Nette\Forms\Rendering\DefaultFormRenderer $renderer
		 */
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = 'dl';
		$renderer->wrappers['pair']['container'] = NULL;
		$renderer->wrappers['label']['container'] = 'dt';
		$renderer->wrappers['control']['container'] = 'dd';

		$form->addSubmit('addPerson', 'Přidat');
		return $form;
	}
}
