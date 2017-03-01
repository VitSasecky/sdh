<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;

/**
 * Formular pro pridani komentare v chatovaci mistnosti
 * Class ContactFormFactory
 *
 * @package App\Forms
 */
class ChatFactory extends Nette\Application\UI\Control
{
	const MAX_CHARS_DESCRIPTION = 800;
	const TEXT_AREA_ROWS = 6;
	const TEXT_AREA_COLS = 68;

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;
		$form->addTextArea('comment', 'Přidejte nový komentář:', self::TEXT_AREA_COLS, self::TEXT_AREA_ROWS)
			->setRequired('Zadejte prosím báš příspěvek.')
			->addRule(Form::MAX_LENGTH, 'Komentář může obsahovat pouze %d znaků', self::MAX_CHARS_DESCRIPTION);
		$form->addSubmit('addComment', 'Přidat komentář')
			->setAttribute('class', 'btn btn-success');

		/**
		 * @var Nette\Forms\IFormRenderer $renderer
		 */
		/*	$renderer = $form->getRenderer();
			$renderer->wrappers['controls']['container'] = 'dl';
			$renderer->wrappers['pair']['container'] = null;
			$renderer->wrappers['label']['container'] = 'dt';
			$renderer->wrappers['control']['container'] = 'dd';
			*/
		return $form;
	}

}
