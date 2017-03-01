<?php

namespace App\Presenters;


use App\Model\Entity\FlashMessage;
use App\Model\Exceptions\ModelException;
use Grido\Components\Filters\Filter;
use Grido\Grid;
use Tracy\Debugger;

/**
 * Class UnitPresenter
 *
 * @package App\Presenters
 */
final class UnitPresenter extends BasePresenter
{
	/** @var string @persistent */
	public $ajax = 'on';

	/** @var string @persistent */
	public $filterRenderType = Filter::RENDER_INNER;


	/**
	 * Vytvori komponentu grid s cleny SDH
	 *
	 * @return \Grido\Grid
	 * @throws \Grido\Exception
	 * @throws \Nette\Application\AbortException
	 */
	protected function createComponentGrid()
	{
		try
		{
			$unityGrid = $this->sdhModel->createUnityGrid();
		} catch (ModelException $e)
		{
			Debugger::log($e);
			$this->flashMessage($e->getMessage(), FlashMessage::ERROR);
			$this->redirect('Homepage:default');
			exit; //ukonceni presenteru
		}
		return $unityGrid;
	}

	/**
	 * Dojde k vykresleni gridu
	 */
	public function renderDefault()
	{
		$this['grid']; // komponenta grid (createComponentGrid)
	}

	public function handleCloseTip()
	{
		$this->getHttpResponse()->setCookie('grido-examples-first', 0, 0);
		$this->redirect('this');
	}


	/**
	 * Zpracuje veskere operace
	 *
	 * @param string $operation
	 * @param array $id
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function handleOperations($operation, $id)
	{
		if ($id)
		{
			$row = implode(', ', $id);
			$this->flashMessage("Process operation '$operation' for row with id: $row...", 'info');
		} else
		{
			$this->flashMessage('No rows selected.', FlashMessage::ERROR);
		}

		if ($this->isAjax())
		{
			/** @var Grid $grid */
			$grid = isset($this['grid']) ? $this['grid']: null;
			$grid !==null && $grid->reload();
			$this->redrawControl('flashes');
		} else
		{
			$this->redirect($operation, ['id' => $id]);
		}
	}

	/**
	 * Provede se pri stisknuti tlacitka vymazat - GRID
	 *
	 * @throws \Nette\Application\AbortException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Exception
	 */
	public function actionDelete()
	{
		$id = $this->getParameter('id');
		try
		{
			$message = "Odstranění řádku: $id bylo úspěšně provedeno.";
			$messageType = FlashMessage::SUCCESS;
			$this->sdhModel->deleteUnitPerson($id);
		} catch (ModelException $e)
		{
			Debugger::log($e);
			$message = $e->getMessage();
			$messageType = FlashMessage::ERROR;
		}
		$this->flashMessage($message, $messageType);
		$this->redirect('default');
	}

	/**
	 * Prida uzivatele
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionAdd()
	{
		try
		{
			$message = '%s byl úspěšně přidán.';
			$messageType = FlashMessage::SUCCESS;
			// $this->sdhModel->addUnitMember();
		} catch (ModelException $e)
		{
			$message = $e->getMessage();
			$messageType = FlashMessage::ERROR;
		}
		$this->flashMessage($message, $messageType);
		$this->redirect('default');
	}

	/**
	 * Provede se pri stisknuti tlacitka - GRID
	 *
	 * @throws \Nette\Application\AbortException
	 */
	public function actionPrint()
	{
		$id = $this->getParameter('id');
		$id = is_array($id) ? implode(', ', $id) : $id;
		$this->flashMessage("Action '$this->action' for row with id: $id done.", FlashMessage::SUCCESS);
		$this->redirect('default');
	}

	/**
	 * Provede se pri stisknuti tlacitka upravit - GRID
	 */
	/*public function actionEdit($id, $data,$tata, $lala)
	{
			$grido = new \Grido\Grid();
			$b = $grido->getFilter('firstname');
			$a = $this->getParameters();;
			$this->flashMessage("Editace řádku: $id byla úspěšně provedena.", 'success');
			$this->redirect('default');
	}*/
}
