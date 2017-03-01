<?php

namespace App\Presenters;

use App\Entity\Chat;
use App\Entity\Event;
use App\Forms\ArticleFactory;
use App\Forms\ChatFactory;
use App\Forms\DocumentUploaderFactory;
use App\Forms\NewFactory;
use App\Forms\PhotoDownloaderFactory;
use App\Forms\PhotoUploaderFactory;
use App\Model;
use Doctrine\ORM\EntityFilter;
use Nette\Application\UI\Form;
use App\Forms\EventFactory;
use Tracy\Debugger;


/**
 * UserSection presenter.
 */
class UserSectionPresenter extends BasePresenter
{
	/**
	 * @var array
	 */
	private static $smiles = [
		':|'   => 'icontexto-emoticons-03',
		':-|'  => 'icontexto-emoticons-03',
		':-o'  => 'icontexto-emoticons-10',
		':-O'  => 'icontexto-emoticons-10',
		':o'   => 'icontexto-emoticons-10',
		':O'   => 'icontexto-emoticons-10',
		';)'   => 'icontexto-emoticons-04',
		';-)'  => 'icontexto-emoticons-04',
		':p'   => 'icontexto-emoticons-08',
		':-p'  => 'icontexto-emoticons-08',
		':P'   => 'icontexto-emoticons-08',
		':-P'  => 'icontexto-emoticons-08',
		':D'   => 'icontexto-emoticons-02',
		':-D'  => 'icontexto-emoticons-02',
		'8)'   => 'icontexto-emoticons-06',
		'8-)'  => 'icontexto-emoticons-06',
		':)'   => 'icontexto-emoticons-03',
		':-)'  => 'icontexto-emoticons-01',
		':('   => 'icontexto-emoticons-11',
		':-('  => 'icontexto-emoticons-11',
		':_('  => 'icontexto-emoticons-11',
		':\'(' => 'icontexto-emoticons-11',
		'X-('  => 'icontexto-emoticons-14',
		'X('   => 'icontexto-emoticons-14'
	];

	/** @var PhotoUploaderFactory @inject */
	public $photoUploader;

	/** @var DocumentUploaderFactory @inject */
	public $docUploader;

	/** @var EventFactory @inject */
	public $eventFactory;

	/** @var ChatFactory @inject */
	public $chatFactory;

	/** @var ArticleFactory @inject */
	public $articleFactory;

	/** @var PhotoDownloaderFactory @inject */
	public $photoDownloader;

	/** @var NewFactory @inject */
	public $newFactory;


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
	 * Vytvori komponentu slouzici k uploadu obsahu
	 *
	 * @return Form
	 * @throws \Nette\DirectoryNotFoundException
	 */
	public function createComponentPhotoUploader()
	{
		$form = $this->photoUploader->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			if ($values['directories'])
			{
				$dirBaseName = $values['directories'];
			} else
			{
				$dirBaseName = trim($values['dirname'], " \t\n\r\0\x0B \\//");
			}

			try
			{
				$this->sdhModel->getLoader()->addPhotos($values['uploadFiles'], $dirBaseName);

				/** @var array $flashes $flashes */
				$flashes = Model\Entity\FlashMessage::getFlashes();
				if (is_array($flashes) && count($flashes))
				{
					foreach ($flashes as $flash)
					{
						$this->flashMessage($flash['text'], $flash['status']);
					}
				}
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
		};
		return $form;
	}


	/**
	 * Vytvori komponentu pro upload novinek
	 *
	 * @return Form
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function createComponentNewUploader()
	{
		$form = $this->newFactory->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			try
			{
				$this->sdhModel->saveInformation($values);
				$this->flashMessage(sprintf('Novinka: "%s" byla úspěšně přidána', $values['title'])
					, Model\Entity\FlashMessage::SUCCESS
				);
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
		};
		return $form;
	}


	/**
	 * Vytvori komponentu pro upload dokumentu
	 *
	 * @return Form
	 */
	public function createComponentDocumentUploader()
	{
		$form = $this->docUploader->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			Debugger::barDump($form, __FUNCTION__);
			try
			{
				$this->sdhModel->saveDocuments($values);
			} catch (\Exception $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}

			/** @var array $flashes $flashes */
			$flashes = Model\Entity\FlashMessage::getFlashes();
			if (is_array($flashes))
			{
				foreach ($flashes as $flash)
				{
					$this->flashMessage($flash['text'], $flash['status']);
				}
			}
		};
		return $form;
	}

	/**
	 * Vytvori komponentu slouzici k uploadu obsahu
	 *
	 * @return Form
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentPhotoDownloader()
	{
		$form = $this->photoDownloader->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			try
			{
				$this->sdhModel->zipDirectory($values['directories']);
				$form->getPresenter()->redirect('UserSection:uploadPhoto');
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
			$form->getPresenter()->redirect('UserSection:uploadPhoto');
		};
		return $form;
	}


	/**
	 * Vytvori kompnentu pro nahrani novych clanku
	 *
	 * @return Form
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentArticle()
	{
		$form = $this->articleFactory->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			try
			{
				$this->sdhModel->saveArticle($values);
				$this->flashMessage(sprintf('Váš článek: "%s" byl úspěšně přidán', $values['title'])
					, Model\Entity\FlashMessage::SUCCESS
				);
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
			$form->getPresenter()->redirect('UserSection:uploadArticles');
		};
		return $form;
	}


	/**
	 * Vytvori komponentu slouzici k uploadu obsahu
	 *
	 * @return Form
	 * @throws \Exception
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentChat()
	{
		$form = $this->chatFactory->create();
		/**
		 * @param Form $form
		 * @param $values
		 */
		$form->onSuccess[] = function (Form $form, $values)
		{
			try
			{
				$this->chatModel->addComment($values['comment'], $this->getUser()->id);
				//$this->flashMessage(sprintf('Váš komentář byl úspěšně přidán'), Model\Entity\FlashMessage::SUCCESS);
			} catch (Model\Exceptions\ModelException $e)
			{
				Debugger::log($e);
				$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			}
			$form->getPresenter()->redirect('UserSection:chat');
		};
		return $form;
	}


	/**
	 * Nema vlastni default sekci, proto se provede presmerovani na hlavni stranku
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderDefault()
	{
		$this->redirect('Homepage:default');
	}


	public function renderUploadPhoto()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderUploadDocuments()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	/**
	 * @param $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDeletePost($id)
	{
		try
		{
			$this->chatModel->deleteComment($id);
			$this->flashMessage(
				sprintf('Komentář s ID: %d byl úspěšně odebrán', $id),
				Model\Entity\FlashMessage::SUCCESS
			);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage(
				'Došlo k systémové chybě, nepodařilo se odstranit komentář.',
				Model\Entity\FlashMessage::ERROR
			);
		}
		$this->redirect('UserSection:chat');
	}

	/**
	 * @param int $page
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderChat($page = null)
	{
		$defaultPage = 1;
		$page = $page === null ? $defaultPage : $page;
		$paginator = false;
		$chatFilter = new EntityFilter();

		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);

		try
		{
			$yesterday = new \DateTime();
			$this->template->yesterday = $yesterday->modify('-1 day');
		} catch (\Exception $e)
		{
			$this->template->yesterday = null;
			Debugger::log($e);
		}

		try
		{
			$paginator = $this->sdhModel->getPaginator(Chat::class, $page);
			$chatFilter->setLimit($paginator->getLimit())
				->setOffset($paginator->getOffset());
			$this->template->comments = $this->sdhModel->getChat($chatFilter);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage(
				"Došlo k systémové chybě, nepodařilo se sesavit diskuzní fórum.\nKontaktuje prosím administrátora",
				Model\Entity\FlashMessage::ERROR
			);
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

	/**
	 * @param $subject
	 *
	 * @return mixed
	 */
	public function smilify($subject)
	{
		$size = 20;
		$replace = [];

		$subject = htmlspecialchars($subject); //escapovat html
		foreach (self::$smiles as $smiley => $imgName)
		{
			//zde nescapuji, ponvadz je to html element pro zobrazeni obrazku a je to bezpecne
			$replace[] = '<img src="../images/emoticons/' . $imgName . '.ico" alt="' . $smiley . '" class="smiley" width="'
				. $size . '" height="' . $size . '"/>';
		}
		return str_replace(array_keys(self::$smiles), $replace, $subject);
	}

	public function renderUploadArticles()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderUploadNews()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderDownloads()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	/**
	 * @param int $page
	 *
	 * @throws \App\Model\Exceptions\PageException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Exception
	 */
	public function renderEvents($page = null)
	{
		$paginator = null;
		$defaultPage = 1;
		$page = $page === null ? $defaultPage : $page;

		$eventFilter = new EntityFilter([], ['date' => 'DESC', 'name' => 'ASC']);

		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		try
		{
			$paginator = $this->sdhModel->getPaginator(Event::class, $page);
			$eventFilter->setLimit($paginator->getLimit())
				->setOffset($paginator->getOffset());
			$this->template->events = $this->sdhModel->getEvents($eventFilter);
		} catch (Model\Exceptions\EventException $e)
		{
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
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
}
