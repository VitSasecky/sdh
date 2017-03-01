<?php

namespace App\Presenters;

use App\Entity\Article;


use App\Entity\Reference;

use App\Model;
use Doctrine\ORM\EntityFilter;
use Nette\Application\UI\Form;
use App\Forms\RegistrationFactory;
use Tracy\Debugger;

/**
 * Section presenter.
 */
class SectionPresenter extends BasePresenter
{
	/** @var RegistrationFactory @inject */
	public $registrationFactory;


	/**
	 * Vytvori komponentu registracniho formulare
	 *
	 * @return Form
	 * @throws \Nette\InvalidArgumentException
	 * @throws \Nette\Application\AbortException
	 */
	public function createComponentRegistrationForm()
	{
		$form = $this->registrationFactory->create();
		$form->onSuccess[] = function (Form $form, $values)
		{
			try
			{
				$loginUser = $this->sdhModel->addUser($values);
				$this->flashMessage(
					sprintf('Uživatel: "%s" byl úspěšně zaregistrován', $loginUser),
					Model\Entity\FlashMessage::SUCCESS
				);
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
			$form->getPresenter()->redirect('Homepage:default');
		};
		return $form;
	}

	/**
	 * Provede smazani polozky/entity z webu dle ID v 1.parametru
	 *
	 * @param $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionDeleteNew($id)
	{
		try
		{
			$this->sdhModel->deleteArticle($id);
			$this->flashMessage('Položka byla úspěšně odstraněna.', Model\Entity\FlashMessage::SUCCESS);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k systémové chybě, položku se nepodařilo odstranit', Model\Entity\FlashMessage::ERROR);
		}
		$this->redirect('news');

	}

	/**
	 * Vyrenderuje seznam novinek
	 *
	 * @param int $page
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderNewsList($page = null)
	{
		/** @var \stdClass $this ->template */
		$paginator = null;
		$defaultPage = 1;
		$page = $page === null ? $defaultPage : $page;
		$articleFilter = new EntityFilter();

		try
		{
			$paginator = $this->sdhModel->getPaginator(
				Article::class, $page
				, $this->sdhModel->getConfig()['paginator']['newsList']['itemsPerPage']
			);

			$articleFilter->setLimit($paginator->getLimit())
				->setOffset($paginator->getOffset());
			$this->template->articles = $this->sdhModel->getArticles($articleFilter);
		} catch (Model\Exceptions\ModelException $e)
		{
			Debugger::log($e);
			if ($e->getCode() === Model\Entity\ErrorCode::CODE_NOT_FOUND)
			{
				$this->flashMessage('Žádné novinky nejsou právě dispzozici', Model\Entity\FlashMessage::WARNING);
			} else
			{
				$this->flashMessage('Došlo k chybě, při načítání článků.', Model\Entity\FlashMessage::WARNING);
			}
			$this->redirect('Homepage:default');
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k systomové chybě, stránku nelze zobrazit', Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		} finally
		{
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

	/**
	 * Vyrenderuje sekci novinek
	 *
	 * @param int $articleId
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderNews($articleId = null)
	{
		$article = null;
		$defaultArticleId = 0;
		$articleId = $articleId === null ? $defaultArticleId : $articleId;

		/** @var \stdClass $this ->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		try
		{
			/*** @var Article $article */
			$article = $this->sdhModel->getArticle($articleId);
			$this->template->totalArticles = $this->sdhModel->getTotalArticles();
		} catch (Model\Exceptions\ModelException $e)
		{
			Debugger::log($e);
			$this->flashMessage('Článek není k dispzozici', Model\Entity\FlashMessage::WARNING);
			$this->redirect('Homepage:default');
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Došlo k neočekávané chybě', Model\Entity\FlashMessage::WARNING);
			$this->redirect('Homepage:default');
		}

		if ($article instanceof Article)
		{
			$this->template->articleId = $article->getId();
			$this->template->title = $article->getTitle();
			$this->template->article = $article->getContent();
		}
	}

	/**
	 * Vyrenderuje sekci s odkazy
	 *
	 * @param int $page
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderReferences($page = null)
	{
		$paginator = null;
		$defaultPage = 1;
		$page = $page === null ? $defaultPage : $page;
		$referenceFilter = new EntityFilter();

		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		try
		{
			$paginator = $this->sdhModel->getPaginator(Reference::class, $page);
			$referenceFilter->setLimit($paginator->getLimit())
				->setOffset($paginator->getOffset());
			$this->template->references = $this->sdhModel->getReferences($referenceFilter);
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Sekce odkazy sboru není v současné době k dispzozici', Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		} finally
		{
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

	/**
	 * Vyrenderuje sekci technologii SDH
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderTechnology()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		try
		{
			$this->template->technology = $this->sdhModel->getTechnology(new EntityFilter());
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Sekce technologie sboru není v současné době k dispzozici', Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Vyrenderuje historii SDH
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderHistory()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		try
		{
			$this->template->totalMembers = $this->sdhModel->getTotaltMembers();
			$this->template->fires = $this->sdhModel->getFires(new EntityFilter());
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage('Historie sboru není v současné době k dispzozici', Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		}
	}

	/**
	 * Vyrenderuje sekci s uspechy SDH v pozarnim sportu
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function renderCups()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		try
		{
			$this->template->cups = $this->sdhModel->getCups(new EntityFilter());
		} catch (\Exception $e)
		{
			Debugger::log($e);
			$this->flashMessage($e->getMessage(), Model\Entity\FlashMessage::ERROR);
			$this->redirect('Homepage:default');
		}
	}


	public function renderArchive()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderGallery()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}


	public function renderOurInformation()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

	public function renderAddUser()
	{
		/** @var \stdClass $this ->template title */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
	}

}
