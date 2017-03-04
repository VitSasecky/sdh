<?php

namespace App\Presenters;

use App\Entity\User;
use App\Forms\AccountFactory;
use App\Forms\EventFactory;
use App\Forms\NewsLetterFactory;
use App\Forms\RegistrationFactory;
use App\Forms\UnityFactory;
use App\Forms\UsersFactory;
use Doctrine\ORM\EntityFilter;
use Nette,
	App\Model;
use Tracy\Debugger;


/**
 * Summary presenter.
 */
class SummaryPresenter extends BasePresenter
{
	/** @var EventFactory @inject */
	public $eventFactory;

	/**
	 * @var NewsLetterFactory @inject
	 */
	public $newsLetterFactory;

	/**
	 * @var UnityFactory $unitFactory @inject
	 */
	public $unityFactory;


	/** @var RegistrationFactory $registrationFactory @inject */
	public $registrationFactory;

	/**
	 * @var AccountFactory @inject
	 */
	public $accountFactory;

	/**
	 * @var UsersFactory @inject
	 */
	public $usersFactory;


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();
	}

	/**
	 * Vytvori komponentu pro nahrani noveho SDH clena
	 *
	 * @return bool|Nette\Application\UI\Form
	 * @internal param $name
	 *
	 */
	protected function createComponentUnityForm()
	{
		try
		{
			$unityGrid = $this->unityFactory->create($this->sdhModel);
			$unityGrid->onSuccess[] = function (Nette\Application\UI\Form $form, $values)
			{
				Debugger::barDump($form, __FUNCTION__);
				$message = sprintf(
					'SDH jednotka byla právě úspěšně rozšířena o člena: "%s"', $values['first_name'] . ' ' . $values['surname']
				);

				try
				{
					$this->sdhModel->addUnitMember($values); //prida noveho clena
					$this->flashMessage($message, Model\Entity\FlashMessage::SUCCESS);
					$this->redirect('Homepage:default');
				} catch (Model\Exceptions\ModelException $e)
				{
					Debugger::log($e);
					$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
				}
			};
		} catch (Model\Exceptions\ModelException $e)
		{
			Debugger::log($e);
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			exit; //ukonceni presenteru
		}
		return $unityGrid;
	}


	/**
	 * Vytvori komponentu registracniho formulare
	 *
	 * @return Nette\Application\UI\Form
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentEditAccountForm()
	{

		/**
		 * @var User $loggedUser
		 */
		$loggedUser = $this->sdhModel->getItem(User::class, $this->getUser()->id);
		$form = $this->registrationFactory->create($loggedUser);

		$form->onSuccess[] = function (Nette\Application\UI\Form $form, $values)
		{
			$success = false;
			$values['enabled'] = true;
			try
			{
				$values['id_user'] = $this->getUser()->getId();
				$this->sdhModel->modifyUser($values);
				$success = true;
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			} catch (\Exception $e)
			{
				Debugger::log($e);
				$this->flashMessage('Došlo k systémové chybě, požadavek nelze zpracovat',
					Model\Entity\FlashMessage::ERROR
				);
			}

			if ($success)
			{
				$this->flashMessage(
					'Změny byly úspěšně provedeny',
					Model\Entity\FlashMessage::SUCCESS
				);
				$this->flashMessage(
					'Nekteré změny se projeví až při dalším přihlášení.',
					Model\Entity\FlashMessage::WARNING
				);
			}
			$form->getPresenter()->redirect('Homepage:default');
		};
		return $form;
	}

	/**
	 * Vytvori komponentu pro deaktivaci/aktivaci uzivatelskych uctu
	 *
	 * @return Nette\Application\UI\Form
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentDeleteAccountForm()
	{
		/** @var \stdClass $identity */
		$identity = $this->getUser()->getIdentity();
		$form = $this->accountFactory->create($identity->data);
		$form->onSuccess[] = function (Nette\Application\UI\Form $form)
		{
			try
			{
				$this->sdhModel->deactivateUser($this->getUser()->id);
				$this->flashMessage('Váš účet byl úspěšně deaktivován', Model\Entity\FlashMessage::SUCCESS);
				$this->getUser()->logout();
			} catch (\Exception $e)
			{
				Debugger::log($e);
				$this->flashMessage('Došlo k chybě uživatelský účet se nepořilo odstranit', Model\Entity\FlashMessage::ERROR);
			}
			$form->getPresenter()->redirect('Homepage:default');
		};
		return $form;
	}

	/**
	 * Vytvori komponentu pro vytvoreni novych udalosti
	 *
	 * @return Nette\Application\UI\Form
	 * @throws \Nette\Application\AbortException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function createComponentEvents()
	{
		$form = $this->eventFactory->create();
		$form->onSuccess[] = function (Nette\Application\UI\Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			try
			{
				$this->sdhModel->createEvent($values);
				$this->flashMessage(sprintf(
						'Nová událost: "%s" byla úspěšně vytvořena.', $values['name'])
					, Model\Entity\FlashMessage::SUCCESS
				);

				$this->redirect('Summary:createEvent');
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
		};
		return $form;
	}

	/**
	 * Vytvori komponentu pro zapnuti/vypnuti notikace prihlaseneho uzivatele
	 *
	 * @return Nette\Application\UI\Form
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function createComponentNewsLetterForm()
	{
		/** @var \stdClass $identity */
		$identity = $this->getUser()->getIdentity();
		$form = $this->newsLetterFactory->create($identity->NOTIFICATION);
		$form->onSuccess[] = function (Nette\Application\UI\Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			$message = 'E-mailové notifikace jsou nyní vypnuté';
			if ($values['notification'])
			{
				$message = 'E-mailové notifikace jsou nyní povoleny';
			}

			/** @var \stdClass $identity */
			$identity = $this->getUser()->getIdentity();
			if ($identity instanceof Nette\Security\Identity)
			{
				$identity->NOTIFICATION = (int)$values['notification']; //uprava identity, aktualizace hodnoty
			}

			try
			{
				$this->sdhModel->changeNotification($values['notification']);
				$this->flashMessage($message, Model\Entity\FlashMessage::SUCCESS);
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
		};
		return $form;
	}

	/**
	 * Vytvori komponentu pro upravu uzivatelskeho uctu
	 *
	 * @return Nette\Application\UI\Form
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentEditUsersForm()
	{
		$form = $this->usersFactory->create();
		$form->onSuccess[] = function (Nette\Application\UI\Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			try
			{
				$this->sdhModel->modifyUser($values, true);
				$this->flashMessage('Změna byla úspěšně provedena.', Model\Entity\FlashMessage::SUCCESS);
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage('Došlo k systémové chybě, změnu se nepodařilo provést.', Model\Entity\FlashMessage::ERROR);
			}
			$this->redirect('Summary:editUsers');
		};
		return $form;
	}


	public function renderGeneral()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderNewsletters()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderUnit()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderCreateEvent()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}


	/**
	 * Umoznuje editaci uzivatelu - akticace/deaktivace
	 *
	 * @param int $page
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderEditUsers($page = null)
	{
		$defaultPage = 1;
		$page = $page === null ? $defaultPage : $page;
		$userFilter = new EntityFilter();
		$paginator = null;
		$roles = [];
		$errorMessge = 'Došlo k systémové chybě, nepoařilo se načíst seznam uživatelských účtů';

		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);

		try
		{
			$paginator = $this->sdhModel->getPaginator(User::class, $page);
			$userFilter->setLimit($paginator->getLimit())
				->setOffset($paginator->getOffset());
			$users = $this->sdhModel->getUsers($userFilter);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage($errorMessge, Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		}

		try
		{
			/** @var array $users */
			/*** @var User $user */
			foreach ($users as $user)
			{
				if ($user->getRole()->getId() && !isset($roles[$user->getRole()->getId()]))
				{
					$roles[$user->getRole()->getId()] = Model\Forms\Dialer::getDialerText(
						$user->getRole()->getId()
						, Model\Forms\Dialer::DIALER_ROLE
					);
				}
			}
			$this->template->roles = $roles;
			$this->template->users = $users;
		} catch (\Exception $e)
		{
			Debugger::log($e);
			if ($e->getCode() === Model\Entity\ErrorCode::CODE_NOT_FOUND)
			{
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::WARNING);
			} else
			{
				Debugger::log($e);
				$this->flashMessage(
					'Došlo k systémové chybě, nepoařilo se načíst seznam uživatelských účtů',
					Model\Entity\FlashMessage::ERROR
				);
			}
			$this->redirect('Homepage:default');
		}

		if ($paginator instanceof Model\Paginator)
		{
			$this->template->previousPage = $paginator->getPreviousPage();
			$this->template->currentPage = $paginator->getPage();
			$this->template->nextPage = $paginator->getNextPage();
			$this->template->countItems = $paginator->getTotalItems();
			$this->template->countPages = $paginator->getTotalPages();
			$this->template->firstPageItem = $paginator->getFirstItemOfPage();
			$this->template->lastPageItem = $paginator->getLastItemOfPage();
		}
	}

	public function renderVisits()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}


	public function renderDeleteAccount()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}


	public function renderActivity()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderAccount()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderStatistics()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderSupport()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

}
