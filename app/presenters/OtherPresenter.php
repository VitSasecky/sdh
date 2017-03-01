<?php

namespace App\Presenters;

use App\Entity\Document;
use App\Model;

use Doctrine\ORM\EntityFilter;
use Tracy\Debugger;

/**
 * Other presenter.
 */
class OtherPresenter extends BasePresenter
{
	/**
	 * @var Model\Loader $loader @inject
	 */
	public $loader;

	/**
	 * Odstrani dokument dle ID v 1.parametru
	 *
	 * @param $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDeleteDocument($id)
	{
		try
		{
			$this->sdhModel->deleteDocument($id);
			$this->flashMessage('Položka byla úspěšně odstraněna.', Model\Entity\FlashMessage::SUCCESS);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k systémové chybě, položku se nepodařilo odstranit', Model\Entity\FlashMessage::ERROR);
		}
		$this->redirect('documents');
	}

	/**
	 * Odstrani udalost dle ID v 1.parametru
	 *
	 * @param $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDeleteEvent($id)
	{
		try
		{
			$this->sdhModel->deleteEvent($id);
			$this->flashMessage('Položka byla úspěšně odstraněna.', Model\Entity\FlashMessage::SUCCESS);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k systémové chybě, položku se nepodařilo odstranit', Model\Entity\FlashMessage::ERROR);
		}
		$this->redirect('Homepage:default');
	}

	/**
	 * Stahne dokument dle ID v 1.parametru
	 *
	 * @param $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDownloadDocument($id)
	{
		try
		{
			/** @var \stdClass $this ->template */
			$this->template->documents = $this->sdhModel->downloadDocument($id);
		} catch (Model\Exceptions\ModelException $e)
		{
			Debugger::log($e);
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k chybě, dokument se nepodařilo stáhnout. Kontakuje prosím administrátora.',
				Model\Entity\FlashMessage::ERROR
			);
			$this->redirect('Homepage:default');
		}
	}

	/**
	 *  Nacte sekci dokumentu
	 *
	 * @param int $page
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderDocuments($page = null)
	{
		$defaultPage = 1;
		$page = $page === null ? $defaultPage : $page;
		$errorMsg = 'Došlo k systémové chybě, stránku nelze zobrazit';
		$level = Model\Entity\FlashMessage::ERROR;

		$paginator = null;
		try
		{
			/** @var \stdClass $this ->template */
			$paginator = $this->sdhModel->getPaginator(Document::class, $page);
		} catch (Model\Exceptions\ModelException $e)
		{
			Debugger::log($e);
			if ($e->getCode() === Model\Entity\ErrorCode::CODE_NOT_FOUND)
			{
				$errorMsg = $e->getMessage();
				$level = Model\Entity\FlashMessage::WARNING;
			}
			$this->flashMessage($errorMsg, $level);
			$this->redirect('Homepage:default');
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage($errorMsg, $level);
			$this->redirect('Homepage:default');
		}

		try
		{
			$documentFilter = new EntityFilter();
			$documentFilter->setLimit($paginator->getLimit())
				->setOffset($paginator->getOffset());
			$this->template->documents = $this->sdhModel->getDocuments($documentFilter);
		} catch (Model\Exceptions\ModelException $e)
		{
			Debugger::log($e);
			if ($e->getCode() === Model\Entity\ErrorCode::CODE_NOT_FOUND)
			{
				$errorMsg = $e->getMessage();
				$level = Model\Entity\FlashMessage::WARNING;
			}
			$this->flashMessage($errorMsg, $level);
			$this->redirect('Homepage:default');
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage($errorMsg, $level);
			$this->redirect('Homepage:default');
		}

		if ($paginator)
		{
			$this->template->previousPage = $paginator->getPreviousPage();
			$this->template->currentPage = $paginator->getPage();
			$this->template->nextPage = $paginator->getNextPage();
			$this->template->countItems = $paginator->getTotalItems();
			$this->template->countPages = $paginator->getTotalPages();
			$this->template->firstPageItem = $paginator->getFirstItemOfPage();
			$this->template->lastPageItem = $paginator->getLastItemOfPage();
		}
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}


	public function renderDesignation()
	{
		/** @var \stdClass $this ->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderFire()
	{
		/** @var \stdClass $this ->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderNodes()
	{
		/** @var \stdClass $this ->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderSponsor()
	{
		/** @var \stdClass $this ->template */
		$this->template->sponsors = $this->sdhModel->getSponsors(new EntityFilter());
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderFlorian()
	{
		/** @var \stdClass $this ->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderExtinguishers()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderEffectiveness()
	{
		/** @var \stdClass $this ->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}


}
