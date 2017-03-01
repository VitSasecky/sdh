<?php

namespace App\Presenters;


use App\Entity\Event;
use App\Forms\ContactFormFactory;


use Latte\Engine;
use Latte\Macros\MacroSet;
use Nette,
	App\Model;
use Nette\Application\UI\Form;
use App\Forms\SignFormFactory;
use App\Model\Exceptions\ModelException;
use Tracy\Debugger;

/**
 * Base presenter for all application presenters.
 */
class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var  Model\SdhModel */
	public $sdhModel;

	/** @var  Model\ChatModel */
	public $chatModel;

	/** @var Model\CounterModel */
	public $counterModel;

	/** @var Model\CountdownModel */
	public $countdownModel;


	/***********TOVARNICKY*************/
	/**
	 * @var SignFormFactory @inject
	 */
	public $signFactory;

	/** @var ContactFormFactory @inject */
	public $contactFormFactory;


	/**
	 * BasePresenter constructor.
	 *
	 * @param Model\SdhModel $sdhModel
	 * @param Model\ChatModel $chatModel
	 * @param Model\CountdownModel $countdownModel
	 * @param Model\CounterModel $counterModel
	 */
	public function __construct(Model\SdhModel $sdhModel, Model\ChatModel $chatModel, Model\CountdownModel $countdownModel
		, Model\CounterModel $counterModel)
	{
		$this->sdhModel = $sdhModel;
		$this->chatModel = $chatModel;
		$this->countdownModel = $countdownModel;
		$this->counterModel = $counterModel;
		parent::__construct();
	}


	/**
	 * Provede odhlášení uživatele, pokud je uživatel skutečně přihlášen
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function handleLogout()
	{
		$this->setView('default');
		try
		{
			$this->sdhModel->logOutUser(); //odhlaseni uzivatele
			$this->flashMessage(sprintf('Uživatel byl úspěšně odhlášen.'));
		} catch (ModelException $e)
		{
			Debugger::log($e);
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
		}
		$this->redirect('Homepage:default');
	}


	/**
	 * Zacatek zivotniho cyklu presenteru
	 * - nacteni webove hlavicky
	 * - nacteni prvni nejblizsi udalosti
	 * - kontrola prihlaseni, pripadne nastaveni role
	 *
	 * @throws \Exception
	 */
	public function startup()
	{
		/** @var \stdClass $this ->template */
		parent::startup();
		$eventExist = $countDown = false;
		$this->template->headerTitle = $this->sdhModel->getWebTitle();  //nacteni weboveho titulku
		$configuration = $this->sdhModel->buildUserConfiguration();  //naplni uzivatelska nastaveni
		$this->template->role = $this->sdhModel->getRole();
		if (is_array($configuration))
		{
			foreach ($configuration as $property => $value)
			{
				$this->template->{$property} = $value;
			}
		}

		try
		{
			$this->template->now = new \DateTime();
		} catch (\Exception $e)
		{
			Debugger::log($e);
		}

		if ($this->getUser()->isLoggedIn()) //pokud je uzivatel prihlaseny, zjistji foto a jeho roli urcujici prava
		{
			/** @var \stdClass $identity */
			$identity = $this->getUser()->getIdentity();
			$this->template->photo = $identity->PHOTO;
			$this->sdhModel->setRole($identity->ID_ROLE);
		}

		try
		{
			/*** @var Event $nextEvent */
			$nextEvent = $this->sdhModel->getNextEvent();  //naplneni aktualni udalosti, pokud je k dispozici
			$countDown = $this->countdownModel->fill($nextEvent);
			$eventExist = true;
		} catch (Model\Exceptions\EventException $e)
		{
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::INFO);
			$this->template->eventExist = false;
		} catch (ModelException $e)
		{
			Debugger::log($e);
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
		}

		if ($countDown)
		{
			$this->template->countDown = $countDown;
		}

		if ($eventExist)
		{
			$this->template->eventName = $nextEvent->getName();
			$this->template->eventType = $nextEvent->getType();
			$this->template->eventLocation = $nextEvent->getPlace();
			$this->template->eventExist = true;
			$this->template->description = $nextEvent->getDescription();
			$this->template->author = $nextEvent->getAuthor()->getFullName();
		}
		$this->template->eventExist = $eventExist;
	}

	/**
	 * Sestavi komponentu kontaktniho formulare
	 *
	 * @return Form
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentContactForm()
	{
		$form = $this->contactFormFactory->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			try
			{    //odesle email administratori, pokud je to mozne
				$this->sdhModel->sendEmails($values['email'], $values['subject'], $values['message']);
				$this->flashMessage('Zpráva byla úspěšně odeslána.', Model\Entity\FlashMessage::SUCCESS);
			} catch (ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
			$this->redirect('Homepage:default');
		};
		return $form;
	}


	/**
	 * Sestavi komponentu prihlasovaciho formulare
	 *
	 * @return Form
	 * @throws \Nette\Application\UI\InvalidLinkException
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentSignForm()
	{
		$form = $this->signFactory->create($this->link('Section:add-user'));
		$form->onSuccess[] = function (Form $form, $values)
		{
			try
			{
				$this->sdhModel->loginUser($values['loginName'], $values['password']);
				$this->flashMessage(
					sprintf('Uživatel: "%s" byl úspěšně přihlášen', $values['loginName'])
					, Model\Entity\FlashMessage::SUCCESS
				);
				$this->flashMessage(sprintf('Vítejte uživateli: "%s"', $values['loginName']), Model\Entity\FlashMessage::SUCCESS);
			} catch (ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
			$form->getPresenter()->redirect('Homepage:default');
		};
		return $form;
	}


	/**
	 * @param null $class
	 *
	 * @return Nette\Application\UI\ITemplate
	 */
	protected function createTemplate($class = null)
	{
		$template = parent::createTemplate();
		$latte = new Engine();
		$set = new MacroSet($latte->getCompiler());
		$set->addMacro('scache', '?>?<?php echo strtotime(date(\'Y-m-d hh \')); ?>"<?php');
		$latte->addFilter('scache', $set);
		return $template;
	}
}
