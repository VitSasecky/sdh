<?php

namespace App\Forms;

use Nette,
    Nette\Application\UI\Form;

/**
 *  Konfiguracni formular pro vypnuti/zapnuti emailove notifikace
 * Class RegistrationFactory
 * @package App\Forms
 */
class NewsLetterFactory extends Nette\Application\UI\Control
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
        $form->addCheckbox('notification', 'e-mailové notifikace')
            ->setDefaultValue($actualStatus);
        $form->addSubmit('send', 'Provést změny');
        return $form;
    }
}
