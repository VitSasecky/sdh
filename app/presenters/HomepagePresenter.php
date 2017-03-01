<?php

namespace App\Presenters;

use App\Entity\Article;


use App\Entity\News;
use
	App\Model;

use Doctrine\ORM\EntityFilter;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
	/**
	 * @param int $page
	 * @param int $infoPage
	 *
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Doctrine\ORM\NoResultException
	 */
	public function actionDefault($page = null, $infoPage = null)
	{
		/*** @var \stdClass $this ->template */
		$defaultPage = 1;
		$page = $page !== null ? $page : $defaultPage;
		$infoPage = $infoPage !== null ? $infoPage : $defaultPage;

		try
		{
			$paginator = $this->sdhModel->getPaginator(Article::class, $page);
			/**paginator pro clanky - properties**/
		} catch (\Exception $e)
		{
			$paginator = null;
			Debugger::log($e);
			$this->flashMessage('Došlo k neočekávané chybě');
		}

		if ($paginator)
		{
			$this->template->previousPage = $paginator->getPreviousPage();
			$this->template->currentPage = $paginator->getPage();
			$this->template->nextPage = $paginator->getNextPage();

			try
			{
				$articleFilter = new EntityFilter();
				$articleFilter->setLimit($paginator->getLimit())
					->setOffset($paginator->getOffset());
				$items = $this->sdhModel->getArticles($articleFilter);
			} catch (\Exception $e)
			{
				Debugger::log($e);
			}
			$this->template->mainArticle = isset($items[0]) ? $items[0] : null; //vyber prvni clanek
			$this->template->articles = $items;
			$this->template->countItems = $paginator->getTotalItems();
			$this->template->countPages = $paginator->getTotalPages();
			$this->template->firstPageItem = $paginator->getOffset();
			$this->template->lastPageItem = $paginator->getLastItemOfPage();
		}

		try
		{
			$paginator = $this->sdhModel->getPaginator(News::class, $infoPage);
			/**paginator pro NOVINKY - properties**/
		} catch (\Exception $e)
		{
			$paginator = null;
			Debugger::log($e);
			$this->flashMessage('Došlo k neočekávané chybě');
		}

		if ($paginator)
		{
			$this->template->infoPreviousPage = $paginator->getPreviousPage();
			$this->template->infoCurrentPage = $paginator->getPage();
			$this->template->infoNextPage = $paginator->getNextPage();

			try
			{
				$infoFilter = new EntityFilter();
				$infoFilter->setLimit($paginator->getLimit())
					->setOffset($paginator->getOffset());
				$items = $this->sdhModel->getInformations($infoFilter);
			} catch (\Exception $e)
			{
				$items = null;
				Debugger::log($e);
				$this->flashMessage('Došlo k neočekávané chybě');
			}

			if ($items)
			{
				$this->template->information = $items;
			}
			$this->template->infoCountItems = $paginator->getTotalItems();
			$this->template->infoCountPages = $paginator->getTotalPages();
			$this->template->infoLastPageItem = $paginator->getLastItemOfPage();
			$this->template->recipients = $this->sdhModel->getRecipients();
			$this->template->navigation = $this->sdhModel->createTabNavigator();
		}
		$this->counterModel->evaluateAttendance(); //zjisti, zda ma pricist navstevu, ci nikoliv

		try
		{
			$this->template->counter = $this->sdhModel->getWebCounter();
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k neočekávané chybě');
		}
	}

	/**
	 * Odstrani novinku dle id v 1.parametru
	 *
	 * @param $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDeleteInformation($id)
	{
		try
		{
			$this->sdhModel->deleteInformation($id);
			$this->flashMessage('Položka byla úspěšně odstraněna.', Model\Entity\FlashMessage::SUCCESS);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k systémové chybě, položku se nepodařilo odstranit', Model\Entity\FlashMessage::ERROR);
		}
		$this->redirect('default');
	}


	public function renderDefault()
	{
		/**
		 * @var \stdClass $this ->template
		 */
		$this->template->rss = $this->sdhModel->getRssNews(); //nacte RSS novinky
	}
}
