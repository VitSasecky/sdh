<?php

namespace App\Forms;

use App\Entity\User;
use App\Model\Forms\Dialer;
use Nette,
	Nette\Application\UI\Form;

/**
 * Registracni formular
 * Class RegistrationFactory
 *
 * @package App\Forms
 */
class RegistrationFactory extends Nette\Application\UI\Control
{
	const MAX_CHARS_DESCRIPTION = 700;
	const MAX_CHARS_ADDRESS = 255;
	const MAX_CHARS_PASSWORD = 20;
	const MIN_CHARS_PASSWORD = 6;

	/**
	 * @param User|bool $user
	 * @throws  Nette\InvalidArgumentException
	 * @return Form
	 */
	public function create($user = null)
	{
		$firstName = $surName = $birthDate = $address = $email = $nickName = $role = null;
		$description = $phone = $photo = null;
		$sex = 1;
		$memberShip = 0;
		$notification = true;
		$submitLabel = 'Registrovat';

		$editUser = false;
		if ($user instanceof User)
		{
			$editUser = true;
			$firstName = $user->getFirstName();
			$surName = $user->getSurname();
			$birthDate = $user->getBirthDate()->format('Y-m-d');
			$address = $user->getAddress();
			$sex = $user->getSex();
			$email = $user->getEmail();
			$memberShip = (int)$user->getMembership();
			$nickName = $user->getNickname();
			$role = $user->getRole()->getId();
			$notification = $user->getNotification();
			$description = $user->getDescription();
			$phone = $user->getPhone();
			$photo = $user->getPhoto();
			$submitLabel = 'Upravit';
		}

		$form = new Form;
		$form->addText('firstname', 'Jméno:')
			->setDefaultValue($firstName)
			->setAttribute('class', 'required input_field')
			->addCondition(Form::FILLED)
			->addRule(Form::MIN_LENGTH, 'Křestní jméno musí obsahovat alespoň %d znaky', 2)
			->addRule(Form::MAX_LENGTH, 'Křestní jméno může obsahovat pouze %d znaků', 20);

		$form->addText('surname', 'Příjmení:')
			->setDefaultValue($surName)
			->setAttribute('class', 'required input_field')
			->setRequired('Zadejte prosím vaše příjmení.')
			->addRule(Form::MIN_LENGTH, 'Příjmení musí obsahovat alespoň %d znaky', 2)
			->addRule(Form::MAX_LENGTH, 'Příjmení může obsahovat pouze %d znaků', 50);

		$form->addText('birth', 'Datum narození:')
			->setDefaultValue($birthDate)
			->setType('date')
			->setAttribute('class', 'required input_field');

		$form->addRadioList('sex', 'Pohlaví:', Dialer::getSexDialer())
			->setDefaultValue($sex)
			->getSeparatorPrototype()
			->setName(null);

		$form->addText('email', 'Email:')
			->setDefaultValue($email)
			->setType('email')
			->setAttribute('class', 'required input_field')
			->setRequired('Zadejte prosím vaši emailovou adresu.')
			->addRule(Form::EMAIL, 'Zadaný email: %s není validní', $form['email']);

		$form->addRadioList('membership', 'Člen jednotky:', Dialer::getMembershipDialer())
			->setDefaultValue($memberShip)
			->getSeparatorPrototype()
			->setName(null);

		$form->addText('nickname', 'Přezdívka:')
			->setDefaultValue($nickName)
			->setAttribute('class', 'required input_field')
			->addRule(Form::MAX_LENGTH, 'Nick může obsahovat pouze %d znaků', 25);

		$form->addPassword('password', 'Heslo:')
			->setAttribute('class', 'required input_field')
			->setRequired('Zvolte si heslo')
			->addCondition(Form::FILLED)
			->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', self::MIN_CHARS_PASSWORD)
			->addRule(Form::MAX_LENGTH, 'Heslo může obsahovat pouze %d znaků', self::MAX_CHARS_PASSWORD);

		$form->addPassword('passwordVerify', 'Heslo znovu:')
			->setAttribute('class', 'required input_field')
			->setRequired('Zadejte prosím heslo ještě jednou pro kontrolu')
			->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password'])
			->setOmitted();

		$form->addSelect('role', 'Zvolte typ:', Dialer::getRoleDialer())
			->setDefaultValue($role)
			->setPrompt('Vyberte typ účtu')
			->setAttribute('class', 'required input_field')
			->setRequired('Vyberte prosím typ vašeho účtu');

		$form->addCheckbox('notification', 'e-mailová notifikace')
			->setDefaultValue($notification);

		$form->addText('phone', 'Telefonní číslo:', 0)
			->setDefaultValue($phone)
			->setAttribute('class', 'required input_field')
			->setOption('phone_description', 'Vyplňte číslo prosím bez předvolby')
			->addCondition(Form::FILLED)
			->addRule(Form::INTEGER, 'Telefon může obsahovat pouze číslice');     // pak bude muset obsahovat číslici

		$form->addText('address', 'Adresa:')
			->setDefaultValue($address)
			->setAttribute('class', 'required input_field')
			->addRule(Form::MAX_LENGTH, 'Adresa může obsahovat pouze %d znaků', self::MAX_CHARS_ADDRESS);

		$form->addUpload('userPhoto', 'Fotografie')
			->setDefaultValue($photo)
			->addRule(Form::MAX_FILE_SIZE, 'Maximální velikost fotografie je 120 kB.', 121 * 1024 /* v bytech */)
			->setAttribute('class', 'required input_field');

		if($editUser){
			$form->addCheckbox('changePhoto', 'Změnit fotografii')
				->setAttribute('class', 'checkbox_label')
				->setDefaultValue(false);
		}

		$form->addTextArea('description', 'Popis uživatele:')
			->setDefaultValue($description)
			->addRule(Form::MAX_LENGTH, 'Popis uživatele může obsahovat pouze %d znaků', self::MAX_CHARS_DESCRIPTION)
			->setAttribute('rows',6)
			->setAttribute('cols', 40);
		$form->addSubmit('send', $submitLabel);
		//$form->addProtection('Vypršel časový limit, odešlete formulář znovu');
		return $form;
	}
}
