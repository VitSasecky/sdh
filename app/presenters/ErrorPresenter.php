<?php

namespace App\Presenters;

use Nette;
use Nette\Application\Responses;
use Tracy\ILogger;

/**
 * Class ErrorPresenter
 *
 * @package App\Presenters
 */
class ErrorPresenter extends Nette\Object implements Nette\Application\IPresenter
{
	/** @var ILogger */
	private $logger;


	/**
	 * ErrorPresenter constructor.
	 *
	 * @param ILogger $logger
	 */
	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @param Nette\Application\Request $request
	 *
	 * @return Responses\CallbackResponse|Responses\ForwardResponse
	 */
	public function run(Nette\Application\Request $request)
	{
		$exception = $request->getParameter('exception');

		if ($exception instanceof Nette\Application\BadRequestException) {
			return new Responses\ForwardResponse($request->setPresenterName('Error4xx'));
		}

		$this->logger->log($exception, ILogger::EXCEPTION);
		return new Responses\CallbackResponse(function () {
			require __DIR__ . '/templates/Error/500.phtml';
		});
	}

}
