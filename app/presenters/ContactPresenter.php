<?php

namespace App\Presenters;


use App\Model;


/**
 * Contact presenter.
 */
class ContactPresenter extends BasePresenter
{

	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		$config = $this->sdhModel->getConfig();

		/** @var \stdClass $this->template */
		$this->template->posX = isset($config['location']['posX']) ? $config['location']['posX'] : null;
		$this->template->posY = isset($config['location']['posY']) ? $config['location']['posY'] : null;
		parent::beforeRender();
	}


	public function renderAdmin()
	{
		/** @var \stdClass $this->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		$this->template->sdh = $this->sdhModel->getSDHSubject();
	}

	public function renderCommander()
	{

		/** @var \stdClass $this->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		$this->template->sdh = $this->sdhModel->getSDHSubject();
	}

	public function renderUnity()
	{
		/** @var \stdClass $this->template */
		$this->template->title = Model\SdhModel::getTitle($this->presenter);
		$this->template->sdh = $this->sdhModel->getSDHSubject();
	}
}
