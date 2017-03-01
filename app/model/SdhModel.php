<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 8. 2016
 * Time: 9:23
 */

namespace App\Model;

use App\Entity\ArticleRepository;
use App\Entity\Chat;
use App\Entity\CounterRepository;
use App\Entity\Cup;
use App\Entity\News;
use App\Entity\Fire;
use App\Entity\Position;
use App\Entity\Reference;
use App\Entity\Sponsor;
use App\Entity\Subject;
use App\Entity\Unit;
use App\Entity\Article;
use App\Entity\Technology;
use App\Entity\Document;
use App\Entity\Event;
use App\Model\Entity\ErrorCode;
use App\Model\Entity\FlashMessage;
use App\Model\Entity\Section;
use App\Model\Exceptions\EventException;
use App\Model\Exceptions\ModelException;
use App\Model\Exceptions\PageException;
use App\Model\Forms\Dialer;
use App\RSS\Configuration;
use App\RSS\Entity;
use Doctrine\ORM\EntityFilter;
use Doctrine\ORM\EntityManager;
use Grido\Components\Filters\Filter;
use Grido\DataSources\Doctrine;
use Grido\Grid;
use Nette\Application\UI\PresenterComponent;
use Nette\DirectoryNotFoundException;
use Nette\Http\FileUpload;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Mail\SendmailMailer;
use Nette\Security\AuthenticationException;
use Nette\Security\User;
use Nette\Utils\AssertionException;
use Nette\Utils\Finder;
use Nette\Utils\Html;
use Nette\Utils\Strings;
use Nette\Utils\Validators;
use Tracy\Debugger;
use Tracy\ILogger;
use Tracy\Logger;
use App\Entity\Counter;

/**
 * Class SdhModel
 * Primarni model cele aplikace. Pracuje predevsim s ORM entitami
 *
 * @package App\Model
 */
class SdhModel extends BaseModel
{
	/**
	 * Identifikator hlavniho subjektu/ druzstava SDH
	 */
	const SUBJECT_SDH = 'SDH';

	/**
	 * MAX. pocet dokumentu, ktere je mozne ulozit/nahrat
	 */
	const SAVE_DOCUMENTS_MAX = 50;

	/**
	 * Entita uzivatele
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Spravce pro praci s udalosmi
	 *
	 * @var EventManager
	 */
	private $event;

	/**
	 * Spravce pro praci s uzivateli
	 *
	 * @var UserManager
	 */
	private $userManager;

	/**
	 * Model, ktery umoznuje nahrat napr. fotografie, dokumenty apod.
	 *
	 * @var Loader
	 */
	private $loader;

	/**
	 * Hlavni titulek stranky
	 *
	 * @var string
	 */
	private $mainTitle;

	/**
	 * SDH konfigurace z DI
	 *
	 * @var array
	 */
	private $params;

	/**
	 * Kontaktni emaily
	 *
	 * @var array
	 */
	private $emails;

	/**
	 * @var integer
	 */
	private $role = 0;

	/**
	 * Titulky jednotlivych stranek webu
	 *
	 * @var array
	 */
	public static $titles = [];

	/**
	 * SdhModel constructor.
	 *
	 * @param EntityManager $em
	 * @param Logger $logger
	 * @param User $user
	 * @param UserManager $userManager
	 * @param EventManager $event
	 * @param Loader $loader
	 */
	public function __construct(EntityManager $em, Logger $logger, User $user,
	                            UserManager $userManager, EventManager $event, Loader $loader)
	{
		$this->event = $event;
		$this->userManager = $userManager;
		$this->user = $user;
		$this->loader = $loader;
		parent::__construct($em, $logger);
	}

	/**
	 * Sestavi odpoved po nahrani dokumentu
	 *
	 * @param $success - pocet aktualne uspesne nahranyh dokumentu
	 * @param $document - entita dokument, ktera byla prave uspesne nahrana
	 *
	 * @return $this
	 */
	private function getDocGuiResponse($success, Document $document)
	{
		$message = 'Žádné nové soubory nebyly přidány.';
		$state = FlashMessage::WARNING;
		if ($success)
		{
			try
			{
				$subject = 'SDH oznámení - nové dokumenty';
				$message = sprintf(
					"Právě nyní byly nahrány nové dokumenty. \n Celkový počet nahraných dokumentů: %d. \n Dokumenty nahrál: '%s'"
					, $success
					, $document->getAuthor()
					->getFullName()
				);
				//todo vytvorit latte sablonu na zpravu
				$this->sendNotifications($subject, $message);
			} catch (\Exception $e)
			{
				Debugger::log($e, ILogger::EXCEPTION);
			}
			$message = sprintf('Vaše soubory byly úspěšně přidány. Celkem úspěšně přidaných souborů: %d', $success);
			$state = FlashMessage::SUCCESS;
		}
		FlashMessage::addFlash($message, $state);
		return $this;
	}

	/**
	 * Uloží entitu dokument
	 *
	 * @param Document $document - ORM entita/polozka dokument
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	private function saveDocument(Document $document)
	{
		$this->entityManager->persist($document);
		$this->entityManager->flush();
		return $this;
	}

	/**
	 * Provede stazeni zipu do browseru
	 *
	 * @param $zipname - nazev zipu
	 */
	private function downloadZip($zipname)
	{
		//pkcheck nefunguje!!!!
		ini_set('upload_max_size', '256M');
		ini_set('post_max_size', '256M');
		ini_set('memory_limit', '256M');
		set_time_limit(0);
		if (is_readable($zipname) === true)
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Type: application/zip');
			header('Content-disposition: attachment;filename= ' . $zipname);
			header('Content-Length:' . filesize($zipname));
			readfile($zipname);
			flush();
			unlink($zipname);
		}
		exit;
	}

	/**
	 * Nastavi roli uzivatele
	 *
	 * @param $role
	 *
	 * @return $this
	 */
	public function setRole($role)
	{
		$this->role = $role;
		return $this;
	}

	/**
	 * Ziska objekt konfiguracniho pole z DI, se kterym model pracuje
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return $this->params;
	}


	/**
	 * Ziska instanci PhotoLoadera pro nahrani fotografii na server/web
	 *
	 * @return Loader
	 */
	public function getLoader()
	{
		return $this->loader;
	}


	/**
	 * Vraci nastavenou roli uzivatele
	 *
	 * @return int
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * Overuje, zda je uzivatel administrátor (nejvyssi pravo role)
	 *
	 * @return bool
	 */
	public function isAdminRole()
	{
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		return (bool)$this->user->loggedIn && $this->role === \App\Model\Entity\Role::CONST_ROLE_ADMIN;

	}

	/**
	 * Overuje, zda je uzivatel v SDH roli (2.nejvyssi pravo mezi rolemi)
	 *
	 * @return bool
	 */
	public function isSDHMemberRole()
	{
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		return (bool)$this->user->loggedIn && $this->role === \App\Model\Entity\Role::CONST_ROLE_SDH_MEMBER;
	}

	/**
	 * Overuje, zda je uzivatel navstevnik (tedy nejnizsi role)
	 *
	 * @return bool
	 */
	public function isVisitor()
	{
		/** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
		return (bool)$this->user->loggedIn && $this->role === \App\Model\Entity\Role::CONST_ROLE_SDH_MEMBER;
	}


	/**
	 * Pripravi titulky webovych stranek
	 * Title struktura webu - pole[Nazev presenteru][nazevAkce]
	 *
	 * @return array
	 */
	private function getTitles()
	{
		return self::$titles;
	}

	/**
	 * Vrati pole s aktualnimi metadaty pocitadla navstev
	 *
	 * @return mixed
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getWebCounter()
	{
		/**
		 * @var CounterRepository $counterRepository
		 */
		$counterRepository = $this->entityManager->getRepository(Counter::class);
		$webCounter['DAY'] = $counterRepository->getDailyAttendance();
		$webCounter['MONTH'] = $counterRepository->getMonthlyAttendance();
		$webCounter['YEAR'] = $counterRepository->getYearlyAttendance();
		$webCounter['TOTAL'] = $counterRepository->getTotalAttendance();
		return $webCounter;
	}

	/**
	 * Provadi inicializaci modelu, konfiguracniho pole apod.
	 *
	 * @param $params
	 *
	 * @throws AssertionException
	 * @throws \Exception
	 * @return $this
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \App\Model\Exceptions\ModelException
	 */
	public function initialize($params)
	{
		$this->startup();
		$this->params = $params;
		$this->mainTitle = $params['mainTitle'];
		self::$titles = $params['otherTitles'];
		Debugger::barDump($params, 'sdh config');

		$params['email']['sdh'] = $this->getSDHSubject()->getEmail();
		$this->assignEmails($params['email'])
			->getTitles();
		return $this;
	}

	/**
	 * Vrati sestaveny paginator pro konkretni entitu z parametru predanych z DI kontejneru
	 *
	 * @param $className - entita, pro kterou se paginator/strankovac sestavuje
	 * @param $page - udava pocet stranek
	 * @param bool $limit - max.limit - pokud neni vyplnen, defaultni hodnoty z DI
	 *
	 * @throws PageException
	 * @return Paginator
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Exception
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getPaginator($className, $page, $limit = null)
	{
		$pos = mb_strrpos($className, '\\');
		if ($pos === false)
		{
			throw new ModelException('Systémová chyba');
		}
		$entity = mb_substr($className, $pos + 1);
		$entity = mb_strtolower($entity);
		if (!$limit)
		{
			$limit = isset($this->params['paginator'][$entity]['itemsPerPage'])
				? $this->params['paginator'][$entity]['itemsPerPage']
				: $limit;
		}

		return new Paginator(
			$page
			, $limit
			, $this->getCountItems($className)
		);
	}


	/**
	 * Vrati prostr. cURL pozadavku aktualni RSS novinky SDH sborů
	 *
	 * @return array
	 */
	public function getRssNews()
	{
		$params = null;
		$xml = false;
		$url = Configuration::URL;
		$ch = curl_init();
		if ($ch !== false && is_resource($ch))
		{
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			$xml = curl_exec($ch);
			curl_close($ch);
		}

		if ($xml !== false)
		{
			$xmlObject = new \SimpleXMLElement($xml);
			$xmlObject = (array)$xmlObject;
			$max = Configuration::MAX_VIEW;
			$i = 0;

			if (isset($xmlObject[Configuration::RSS_ITEM]))
			{
				/*** @var array $data */
				$data = $xmlObject[Configuration::RSS_ITEM];
				foreach ($data as $item)
				{
					if (!$item->title)
					{
						continue;
					}

					if ($i >= $max)
					{
						break;
					}
					$rss = new Entity();
					$rss->title = $item->title;
					$rss->description = $item->description;
					$rss->url = $item->guid;
					$rss->created = $item->pubDate;
					$params[$i++] = $rss;
				}
			}
		}
		return $params;
	}


	/**
	 * Nacte vsechny cleny SDH
	 *
	 * @param array|EntityFilter $filter
	 *
	 * @return array
	 */
	public function getMembers(EntityFilter $filter)
	{
		$filter->setEntity(Unit::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['surname' => 'ASC']);
		}
		return $this->getItems($filter);
	}


	/**
	 * Stahne dokument dle id z prvniho parametru
	 *
	 * @param $id - id polozky/entity dokument
	 *
	 * @throws ModelException
	 */
	public function downloadDocument($id)
	{
		/*** @var Document $document */
		$document = $this->entityManager->getRepository(Document::class)->find($id);
		$content = fread($document->getContent(), $document->getSize());
		if (!$content)
		{
			throw new ModelException('Došlo k systémové chybě, nepořilo se stáhnout dokument');
		}
		$fileName = $document->getFileName() ?: 'document';
		if (ob_get_contents())
		{
			ob_clean();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $document->getMimeType());
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		echo $content;
		exit;
	}

	/**
	 * Uloží veškeré hodnot k entite dokument z 1.parametru
	 *
	 * @param $values - hoddnoty, property polozky/entity dokument
	 *
	 * @return $this
	 * @throws \Nette\DirectoryNotFoundException
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function saveDocuments($values)
	{
		$maxLen = self::SAVE_DOCUMENTS_MAX;
		$success = 0;
		if (!$this->user->isLoggedIn())
		{
			throw new ModelException('Neoprávněný požadavek, pro tuto operaci musíte být přihášen.');
		}
		$this->prepareDocumentDirectory();

		/**
		 * @var array $files
		 */
		$files = $values['uploadFiles'];
		$document = false;

		/*** @var FileUpload $file */
		foreach ($files as $file)
		{
			$document = new Document();
			$document->setAuthor($this->entityManager->find(\App\Entity\User::class, $this->user->getId()))
				->setName(
					isset($values['name']) && $values['name']
						? mb_substr($values['name'], 0, $maxLen)
						: $file->getName())
				->setContent($file->getContents())
				->setDescription(
					isset($values['description']) && $values['description']
						? mb_substr($values['description'], $maxLen)
						: null)
				->setSize($file->getSize())
				->setFileName($file->getName())
				->setMimeType($file->getContentType())
				->setUploadedTime();

			$this->saveDocument($document);
			$success++;
		}
		$this->getDocGuiResponse($success, $document);
		return $this;
	}


	/**
	 * Nacte vsechny dokumenty dle aplikovaneho filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getDocuments(EntityFilter $filter)
	{
		$filter->setEntity(Document::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['uploadedTime' => 'DESC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Vrati seznam entit uzivatelu dle filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getUsers(EntityFilter $filter)
	{
		$filter->setEntity(\App\Entity\User::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['surname' => 'DESC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Vrati seznam entit novinek dle filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getNews(EntityFilter $filter)
	{
		$filter->setEntity(News::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['uploadedTime' => 'DESC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Nacte vsechny odkazy/reference na jine stranky
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getReferences(EntityFilter $filter)
	{
		$filter->setEntity(Reference::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['description' => 'ASC']);
		}
		return $this->getItems($filter);
	}


	/*
	 * Odstrani dokument dle id v 1.parametru
	 *
	 * @param $id - ID ORM entity/polozky dokuments
	 *
	 * @return $this
	 */
	/**
	 * @param $id
	 *
	 * @return $this
	 */
	/**
	 * @param $id
	 *
	 * @return $this
	 * @throws \Exception
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \App\Model\Exceptions\ModelException
	 */
	public function deleteDocument($id)
	{
		$this->deleteItem(Document::class, $id);
		return $this;
	}


	/**
	 * Odstrani udalost dle id v 1.parametru
	 *
	 * @param $id - ID ORM entity/polozky udalosti
	 *
	 * @return $this
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Exception
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function deleteEvent($id)
	{
		$this->deleteItem(Event::class, $id);
		return $this;
	}

	/**
	 * Odstrani kratke inf. prispevky na hlavni strance
	 *
	 * @param $id - ID ORM entity/polozky Informace
	 *
	 * @return $this
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Exception
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function deleteInformation($id)
	{
		$this->deleteItem(News::class, $id);
		return $this;
	}


	/**
	 * Odstrani cely clanek dle id v 1.parametru
	 *
	 * @param $id - ID ORM entity/polozky clanek
	 *
	 * @return $this
	 * @throws \App\Model\Exceptions\ModelException
	 * @throws \Exception
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function deleteArticle($id)
	{
		$this->deleteItem(Article::class, $id);
		return $this;
	}

	/**
	 * Odstraneni clena SDH z gridu dle i v 1.parametru
	 *
	 * @param $id - id entity clena jednotky
	 *
	 * @return $this
	 * @throws \Exception
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function deleteUnitPerson($id)
	{
		return $this->deleteItem(Unit::class, $id);
	}


	/**
	 * Deaktivuje uzivatele dle id z 1.parametru
	 *
	 * @param $userId - ID entity uzivatele
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \App\Model\Exceptions\ModelException
	 */
	public function deactivateUser($userId = null)
	{
		if ($userId)
		{
			$this->userManager->deactivateUser($userId);
		}
		return $this;
	}


	/**
	 * Vrati vsechny udalosti.
	 *
	 * @return array
	 */
	public function showAllEvents()
	{
		return $this->getEvents(new EntityFilter());
	}

	/**
	 * Vytvori noveho SDH clena dle hodnot z 1.parametru
	 *
	 * @param $values - hodnoty pro pridani nove entity clena
	 *
	 * @return $this
	 * @throws ModelException
	 */
	public function addUnitMember($values)
	{
		$unit = new Unit();
		$unit->setFirstName($values['first_name'])
			->setSurname($values['surname'])
			->setEmergencyUnit($values['membership'])
			->setSex($values['sex']);

		try
		{
			$unit
				->setPosition($this->entityManager->find(Position::class, $values['position']));
			$this->entityManager->persist($unit);
			$this->entityManager->flush();
		} catch (\Exception $e)
		{
			Debugger::log($e, ILogger::EXCEPTION);
		}
		return $this;
	}


	/**
	 * Ziska/nacte vsechny uzivatele do pole
	 *
	 * @param bool $activated - urci, zda ma nacist pouze aktivni, ci vsechny uzivatele
	 *
	 * @return array|\Nette\Database\IRow[]|\Nette\Database\ResultSet
	 * @throws ModelException
	 */
	public function getAllUsers($activated = null)
	{
		$users = $this->userManager->getAlUsers($activated);
		if (!$users)
		{
			$typeAccount = $activated ? 'aktivní' : '';
			throw new ModelException(sprintf('V současné době nejsou k dispozici žádné %s uživatelské účty', $typeAccount));
		}
		return $users;
	}

	/**
	 * Odesle notifikace vsecm aktivnim prijemcum
	 *
	 * @param $subject - predmet emailu
	 * @param $message - zprava/ telo emailu
	 *
	 * @return $this
	 * @throws \App\Model\Exceptions\ModelException
	 */
	private function sendNotifications($subject, $message)
	{
		$recipients = $this->getRecipients(); //identifikuje veskere prijemce
		if ($recipients)
		{
			$emails = [];
			/*** @var \App\Entity\User $user */
			foreach ($recipients as $user)
			{
				$emails[] = $user->getEmail();
			}
			$emails = array_filter($emails);
			$this->sendEmails($emails, $subject, $message);
		}
		return $this;
	}


	/**
	 * Vytvori udalost, pokud jiz neprobehla
	 *
	 * @param $values - hodnoty nove udalosti
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \App\Model\Exceptions\ModelException
	 */
	public function createEvent($values)
	{
		try
		{
			$event = $this->event->createEvent($values);
			try
			{
				$subject = sprintf('SDH oznámení - nová událost: "%s"', $event->getName());
				$message = sprintf(
					"Upozorňujeme, že právě nyní byla vytvořena nová událost: '%s'
					\nTyp události: %s\nDen konání: %s\nMísto konání: %s'"
					, $event->getName()
					, $event->getType()
					, $event->getDate()->format('d.m.Y H:i:s')
					, $event->getPlace()
				);
				$this->sendNotifications($subject, $message);  //todo vytvorit latte sablonu na zpravu
			} catch (\Exception $e)
			{
				Debugger::log($e);
			}
		} catch (EventException $e)
		{
			$this->log(
				sprintf('Vytvoření události: %s se nezdařilo', var_export($values, true))
				, Logger::ERROR
			);
			$this->log($e, Logger::EXCEPTION);
		}
		return $this;
	}

	/**
	 * Vytvori ciselnik funkci v SDH
	 *
	 * @param bool $defaultLabel
	 *
	 * @return array
	 */
	public function getPositionDialer($defaultLabel = null)
	{
		$positions = $this->getPositions(new EntityFilter());
		$dialer = [];

		if ($defaultLabel === null)
		{
			$dialer[''] = '---Vyberte všechny---';
		}

		/*** @var Position $position */
		foreach ($positions as $position)
		{
			$dialer[$position->getId()] = $position->getName();
		}
		return $dialer;
	}

	/**
	 * Vytvori ciselnik funkci v SDH
	 *
	 * @param bool $defaultLabel
	 *
	 * @return array
	 */
	public function getSexDialer($defaultLabel = null)
	{
		if ($defaultLabel === null)
		{
			$sexDialer = array_merge(['' => '---Vyberte všechny---']);
			$temp = Dialer::getSexDialer();
			foreach ($temp as $key => $value)
			{
				$sexDialer[$key] = $value;
			}
		} else
		{
			$sexDialer = Dialer::getSexDialer();
		}

		return $sexDialer;
	}

	/**
	 * Prida noveho uzivatele
	 *
	 * @param $userData - uzivatelska data pro registraci/pridani nove entity uzivatele
	 *
	 * @return mixed - vyhodnoti a vrati login dle metadat z 1.parmetru
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function addUser($userData)
	{
		try
		{
			$newLoginUser = $this->userManager->addUser($userData);
		} catch (AuthenticationException $e)
		{
			$this->log($e->getMessage(), Logger::ERROR);
			throw new ModelException($e->getMessage());
		}
		return $newLoginUser;
	}

	/**
	 * Modifikuje property jiz existujiciho uzivatele
	 *
	 * @param $user - entita uzivatele, jehoz metadata maji byt upravena
	 * @param bool $onlyConfigData
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function modifyUser($user, $onlyConfigData = null)
	{
		try
		{
			$this->userManager->modifyUser($user, $onlyConfigData);
		} catch (AuthenticationException $e)
		{
			$this->log($e->getMessage(), Logger::ERROR);
			throw new ModelException($e->getMessage());
		}
		return $this;
	}

	/**
	 * Vrati primarni subjekt - druzstvo SDH
	 *
	 * @return Subject
	 * @throws ModelException
	 */
	public function getSDHSubject()
	{
		$subjectFilter = new EntityFilter(['type' => self::SUBJECT_SDH]);
		$subjectFilter->setEntity(Subject::class)->setLimit(1);
		$items = $this->getItems($subjectFilter);
		$sdhSubject = isset($items[0]) ? $items[0] : $items;
		if (!$sdhSubject instanceof Subject)
		{
			throw new ModelException('Došlo k systémové chybě, nepodařilo se načíst SDH subjekt.');
		}
		return $sdhSubject;
	}


	/**
	 * Provede ulozeni entity/clanku
	 *
	 * @param $values - metadata entity
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws ModelException
	 */
	public function saveArticle($values)
	{
		if (!trim($values['content']))
		{
			throw new ModelException(
				sprintf('Obsah článku: "%s" je prazdný. Článek nebyl uložen', $values['title'])
			);
		}
		$article = new Article();
		$article->setAuthor($this->entityManager->find(\App\Entity\User::class, $this->user->getId()))
			->setTitle(Strings::trim($values['title']))
			->setContent($values['content'])
			->setCreated();

		$this->entityManager->persist($article);
		$this->entityManager->flush();

		try
		{
			//todo vytvorit novou latte sablonu
			$subject = 'SDH oznámení - nový článek';
			$message = sprintf("Oznamujeme, že právě nyní byl nahrán na náš web nový článek: '%s'\nČlánek nahrál: %s",
				$article->getTitle(),
				$article->getAuthor()
					->getFullName()
			);
			$this->sendNotifications($subject, $message);
		} catch (\Exception $e)
		{
			Debugger::log($e, ILogger::EXCEPTION);
		}
		return $this;
	}


	/**
	 * Ověri validitu emailů a přiřadí jej modelu
	 *
	 * @param $emails
	 *
	 * @return $this
	 * @throws \Nette\Utils\AssertionException
	 */
	private function assignEmails($emails)
	{
		$emails = (array)$emails;
		foreach ($emails as $email)
		{
			Validators::assert($email, 'email');
		}
		$this->emails = $emails;
		return $this;
	}

	/**
	 * Vytvoří naviagční menu
	 *
	 * @param int $totalSections
	 *
	 * @return array
	 */
	public function createTabNavigator($totalSections = null)
	{
		$totalSections = $totalSections !== null ? $totalSections : 6;
		$section = new Section();
		$section->name = 'SDH';
		$section->action = 'SDH';
		$section->isModal = false;
		$navigation[] = $section;

		$section = new Section();
		$section->name = 'Požáry';
		$section->action = 'RSS';
		$section->isModal = false;
		$navigation[] = $section;

		$section = new Section();
		$section->name = 'Kontaktni formulář';
		$section->isModal = true;
		$navigation[] = $section;

		$navigation = array_slice($navigation, 0, $totalSections);
		return $navigation;
	}


	/**
	 * Vytvoří group menu na hlavní stránce
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getInformations(EntityFilter $filter)
	{
		$filter->setEntity(News::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['created' => 'DESC']);
		}

		if (!$filter->getLimit())
		{
			$filter->setLimit(10);
		}
		return $this->getItems($filter);
	}

	/**
	 * Ulozi entitu informace/novinky
	 *
	 * @param $values - metadata novinky
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\TransactionRequiredException
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function saveInformation($values)
	{
		$news = new News();
		$news->setAuthor($this->entityManager->find(\App\Entity\User::class, $this->user->getId()))
			->setTitle(Strings::trim($values['title']))
			->setText($values['text'])
			->setCreated();

		$this->entityManager->persist($news);
		$this->entityManager->flush();

		//todo latte sablona - zatim se s tim nijak ani nepracuje, neodesilat se to
		//$subject = 'SDH oznámení - novinky';
		/*$message = sprintf(
		"Oznamujeme, že právě nyní byla na náš web nahrána novinka: '%s'\nNovinku nahrál: %s",
				$news->getTitle(),
			$news->getAuthor()
					->getFullName()
		);*/

		return $this;
	}

	/**
	 * Prihlasi uzivatele, pokud je uzivatel existuje a je k tomu opravnen (aktivni ucet)
	 *
	 * @param $login - login uzivatele
	 * @param $pass - zakryptovane heslo uzivatele
	 *
	 * @return $this
	 * @throws ModelException
	 */
	public function loginUser($login, $pass)
	{
		try
		{
			$this->user->login($login, $pass);
			$this->user->setExpiration(0, true);  //odhlásit, pokud uživatel zavře prohlížeč
		} catch (AuthenticationException $e)
		{
			$this->log(sprintf('Nepodařilo se přihlásit uživatele: "%s"', $login), Logger::WARNING);
			$this->log($e, Logger::EXCEPTION);
			throw new ModelException($e->getMessage());
		}
		return $this;
	}


	/**
	 * Vraci hlavni titulek webu
	 *
	 * @return string
	 */
	public function getWebTitle()
	{
		return $this->mainTitle;
	}


	/**
	 * Vracti spravce pro manipulaci s udalostmi
	 *
	 * @return EventManager
	 */
	public function getEventManager()
	{
		return $this->event;
	}

	/**
	 * Vrati prvni aktivni/nadchazejici udalost
	 *
	 * @return array
	 * @throws \Exception
	 * @throws \App\Model\Exceptions\EventException
	 */
	public function getNextEvent()
	{
		return $this->getEventManager()->getEvent(true); //nacte nasledujici udalost3
	}


	/**
	 * Slouzi pro grid, callback pro manipulaci s entitami v girdu
	 * Grid callback.
	 * pktodo nepouziva se
	 *
	 * @param array $item
	 * @param \Nette\Utils\Html $el
	 *
	 * @return \Nette\Utils\Html
	 */
	public function gridHrefRender(array $item, Html $el)
	{
		Debugger::dump($item);
		return $el;
	}

	/**
	 * Vraci vygenerovana data pro GRID komponentu
	 *
	 * @param string $cacheKey
	 *
	 * @return array|mixed|NULL
	 * @throws \Exception
	 * @throws \Throwable
	 */

	/**
	 * Vytvori komponentu Grid - seznam clenu
	 *
	 * @return Grid - vraci sestaveny grid
	 * @throws \Grido\Exception
	 * @throws ModelException
	 */
	public function createUnityGrid()
	{
		$grid = new Grid();
		$model = null;
		try
		{
			$repository = $this->entityManager->getRepository(Unit::class);
			$model = new Doctrine(
				$repository->createQueryBuilder('sdh')
			);
		} catch (\Exception $e)
		{
			$this->log($e, Logger::EXCEPTION);
			$this->log($e->getMessage(), Logger::ERROR);
			throw new ModelException('Došlo k neočekávané chybě, nepodařilo se sestavit grid SDH jednotky.');
		}

		if ($model)
		{
			$grid->model = $model;
			$grid->filterRenderType = Filter::RENDER_OUTER;

			/*** @var \stdClass $grid ->translator */
			$grid->translator->lang = 'cs';
			$grid->defaultPerPage = 12;
			$grid
				->addColumnNumber('id', '#')
				->cellPrototype->class[] = 'center';
			$header = $grid->getColumn('id')->headerPrototype;
			$header->rowspan = '2';
			$header->style['width'] = '0.1%';

			$grid
				->addColumnText('surname', 'Příjmení')
				->setSortable()
				->setFilterText()
				->setSuggestion();
			$grid->getColumn('surname')->headerPrototype->style['width'] = '10%';

			$grid
				->addColumnText('firstName', 'Jméno')
				->setSortable()
				->setFilterText()
				->setSuggestion();
			$grid->getColumn('firstName')->headerPrototype->style['width'] = '10%';

			$grid
				->addColumnBoolean('emergencyUnit', 'Jednotka')
				->setColumn('emergencyUnit')
				->setSortable();

			$grid
				->addColumnText('position.name', 'Funkce')
				->setSortable();
			$grid->getColumn('position.name')->headerPrototype->style['width'] = '10%';

			try
			{
				$grid
					->addFilterSelect('sexDialer', 'Pohlaví', $this->getSexDialer(true))
					->setColumn('sex');

				$grid
					->addFilterSelect('positionDialer', 'Funkce', $this->getPositionDialer(true))
					->setColumn('position');
			} catch (\Exception $e)
			{
				Debugger::log($e);
			}


			if ($this->isAdminRole())
			{
				$grid->addActionHref('delete', 'Smazat')
					->setIcon('trash')
					->setConfirm(function ($item)
					{
						/*** @var Unit $item */
						return "Opravdu chcete smazat člena: {$item->getFullName()} ?";
					});
			}
			$grid->setExport('SDH_JEDNOTKA_' . date('d_M_Y'));
		}

		return $grid;
	}

	/**
	 * vypne/zapne notifikaci prihlasenemu uzivateli
	 *
	 * @param $status - flag, zda se ma provest vypnuti/zapnuti. Hodnoty: 0/1
	 *
	 * @return $this
	 * @throws \Doctrine\ORM\ORMInvalidArgumentException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 */
	public function changeNotification($status)
	{
		/*** @var \App\Entity\User $user */
		$user = $this->entityManager->getRepository(\App\Entity\User::class)->find($this->user->getId());
		$user->setNotification($status);
		$this->entityManager->persist($user);
		$this->entityManager->flush();
		return $this;
	}

	/**
	 * Pokud je uživatel přihlášený, načte jeho uživatelské údaje
	 *
	 * @return array $result
	 * @internal param ITemplate $template - predava do latte sablony promenne/variables
	 *
	 */
	public function buildUserConfiguration()
	{
		$result = [];
		if ($this->user->isLoggedIn())
		{  //pokud je uzivatel prihlasen, vyplnit vzdy jeho udaje
			$result = $this->getUserConfiguration();  //naplni uzivatelska nastaveni
		}
		return $result;
	}


	/**
	 * Ziska titulek sekce prostrednictvim presenteru
	 *
	 * @param PresenterComponent $presenter - presenter, pro ktery se tituek identifikuje z DI kontejneru
	 *
	 * @return mixed - vraci nazev titulku pro konkretni Presenter
	 */
	public static function getTitle(PresenterComponent $presenter)
	{
		$title = '';
		$name = $presenter->getPresenter()->getName();
		$action = $presenter->getPresenter()->getAction();
		if ($name && $action)
		{
			$title = self::$titles[$name][$action];
		} else
		{
			self::$logger->log(sprintf('Doslo k chybe, webový titulek neni definovan => [%s][%s]',
				$name,
				$action
			), Logger::ERROR
			);
		}
		return $title;
	}

	/**
	 * Nacte vsechny SDH sponzory
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getSponsors(EntityFilter $filter)
	{
		$filter->setEntity(Sponsor::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['name' => 'ASC']);
		}
		return $this->getItems($filter);
	}

	/**
	 *  Nacte vsechny prijemce
	 *
	 * @return array - vraci pole prijemcu, ktery maji povolenou notifikaci
	 */
	public function getRecipients()
	{
		$filter = new EntityFilter(['notification' => true]);
		$filter->setEntity(\App\Entity\User::class);
		return $this->getItems($filter);
	}

	/**
	 * Nacte vsechny udalosti dle aplikovaneho filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array - vraci pole  ORM entit/udalosti
	 */
	public function getEvents(EntityFilter $filter)
	{
		$filter->setEntity(Event::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['name' => 'DESC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Nacte veskere informace o ziskanych poharech/umistenich
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getCups(EntityFilter $filter)
	{
		$filter->setEntity(Cup::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['place' => 'ASC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Nacte veskere definovane SDH pozice
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getPositions(EntityFilter $filter)
	{
		$filter->setEntity(Position::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['name' => 'ASC']);
		}
		return $this->getItems($filter);
	}


	/**
	 * Vrati veskere entity pro chat, dle aplikovaneho filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getChat(EntityFilter $filter)
	{
		$filter->setEntity(Chat::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['created' => 'DESC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Nacte vsechny SDH technologie/entiy
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getTechnology(EntityFilter $filter)
	{
		$filter->setEntity(Technology::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['name' => 'ASC']);
		}
		return $this->getItems($filter);
	}


	/**
	 * Nacte veskere udalosti o evidovanych pozarech dle filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array - vrati seznam entit pozaru
	 */
	public function getFires(EntityFilter $filter)
	{
		$filter->setEntity(Fire::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['year' => 'ASC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Rozesle aviza na vsechny emaily definovane v 1.parametru
	 *
	 * @param array $emails - emaily, na ktere budou aviza rozeslana
	 * @param $subject - predmet zpravy/aviza
	 * @param $message - telo zpravy/aviza
	 *
	 * @return $this
	 * @throws ModelException
	 */
	public function sendEmails($emails, $subject, $message)
	{
		$mail = new Message();
		$mail->setFrom($this->emails['sdh']);
		foreach ($emails as $email)
		{
			$mail->addTo($email);
		}
		$mail->setSubject($subject)
			->setBody($message);

		try
		{
			$mailer = new SendmailMailer();
			$mailer->send($mail);    //odeslani Emailu

		} catch (SendException $e)
		{
			$this->log(sprintf(
				'Email se zpravou: "%s" se nepodarilo odeslat na e - maillovou adresu: %s',
				$message,
				$this->emails['admin']
			), Logger::ERROR
			);
			$this->log($e, Logger::EXCEPTION);
			throw new ModelException('Došlo k systémové chybě, e - mail se nepodařilo odeslat . ');
		}
		return $this;
	}

	/**
	 *  Vraci vsechny nalezene clanky dle aplikovaneho filtru z 1.parametru
	 *
	 * @param EntityFilter $filter - nastavuj filtr, limit, offset entity repozitare
	 *
	 * @return array
	 */
	public function getArticles(EntityFilter $filter)
	{
		$filter->setEntity(Article::class);
		if (!$filter->getOrder())
		{
			$filter->setOrder(['created' => 'DESC']);
		}
		return $this->getItems($filter);
	}

	/**
	 * Vrati celkovy pocet clenu SDH jednotky
	 *
	 * @return integer|null
	 * @throws \Exception
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getTotaltMembers()
	{
		return $this->getCountItems(Unit::class);
	}


	/**
	 * Sestavi zip uvedeneho adresare z fotogalerie
	 *
	 * @param $dir - cilovy adresar komprimovaneho zip souboru
	 *
	 * @return $this
	 * @throws ModelException
	 */
	public function zipDirectory($dir)
	{
		$albumDir = $this->params['loader']['photos']['dir'];

		/*** @var \SplFileInfo $file */
		$zip = new \ZipArchive;
		$zipname = $dir . '.zip';
		if ($zip->open($zipname, \ZipArchive::CREATE) === true)
		{
			foreach (Finder::findFiles('*')->from($albumDir . DIRECTORY_SEPARATOR . $dir) as $fileName => $file)
			{
				if (is_readable($file->getRealPath()) || chmod($file->getRealPath(), 0777))
				{
					$content = file_get_contents($file->getRealPath());
					if ($content !== false)
					{
						$zip->addFromString($file->getFilename(), $content);
					}
				}
			}
			$zip->close();
			$this->downloadZip($zipname);
		} else
		{
			throw new ModelException(
				sprintf('Došlo k systémové chybě, nepodařilo se vytvoriřt zip arhiv fotogalerie: "%s"', $dir)
			);
		}
		return $this;
	}


	/**
	 * Nacte pozadovany clanek, pokud neexistuje, tak nacte vychozi clanek
	 *
	 * @param bool $articleId - ID entity/clanku, ktere ma byt vraceno
	 *
	 * @return array|\Doctrine\ORM\EntityRepository
	 * @throws ModelException
	 */
	public function getArticle($articleId = null)
	{
		$articleRepository = $this->entityManager->getRepository(Article::class);
		if (!$articleId)
		{
			$article = $articleRepository->findBy([], ['created' => 'DESC'], 1);
			$article = $article[0];
		} else
		{
			$article = $articleRepository->find($articleId);
		}

		if (!$article)
		{
			throw new ModelException('Žádný článek není právě k dispozici', ErrorCode::CODE_NOT_FOUND);
		}
		return $article;
	}

	/**
	 * Vraci predchozi clanek dle ID aktulniho clanku z 1.parametru
	 *
	 * @param $articleId - ID aktualni entity/clanku
	 *
	 * @return mixed
	 */
	public function getPreviousArticle($articleId)
	{
		/**
		 * @var ArticleRepository $repository
		 */
		$repository = $this->entityManager->getRepository(Article::class);
		return $repository->getPreviousArticle($articleId);
	}

	/**
	 * Vraci nasledujici clanek dle ID aktulniho clanku z 1.parametru
	 *
	 * @param $articleId - ID aktualni entity/clanku
	 *
	 * @return mixed
	 */
	public function getNextArticle($articleId)
	{
		/**
		 * @var ArticleRepository $repository
		 */
		$repository = $this->entityManager->getRepository(Article::class);
		return $repository->getNextArticle($articleId);
	}


	/**
	 * Vrati celkovy pocet clanku
	 *
	 * @return int|null
	 * @throws \Exception
	 * @throws \Doctrine\ORM\NoResultException
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getTotalArticles()
	{
		return $this->getCountItems(Article::class);
	}


	/**
	 * Nacte uzivatelske udaje
	 *
	 * @return array
	 */
	private function getUserConfiguration()
	{
		/**
		 * @var \stdClass $identity
		 */
		$identity = $this->user->getIdentity();
		$params = [];
		if ($identity)
		{
			$temp = [];
			$temp[] = $identity->FIRSTNAME;
			$temp[] = $identity->SURNAME;
			$temp = array_filter($temp);
			$params['name'] = implode(' ', $temp);
			$params['role'] = $identity->ID_ROLE;
			$params['description'] = $identity->DESCRIPTION;
			$params['PHOTO'] = $identity->PHOTO;
		}
		return $params;
	}

	/**
	 * Odhlasi uzivatele, pokud e uzivatel prihlasen. Pokud se odlhlaseni nezdari,pak vyhodi vyjimku
	 *
	 * @return $this
	 * @throws ModelException
	 */
	public function logOutUser()
	{
		if ($this->user->isLoggedIn())
		{
			$this->user->logout(true);
			return $this;
		}
		throw new ModelException('Došlo k chybě při odhlašování: žádný uživatel není přihlášen!');
	}

	/**
	 * Nacte adresar dokumentu, pokud neexistuje, pak ho vytvori
	 *
	 * @throws DirectoryNotFoundException
	 * @return mixed
	 */
	protected function prepareDocumentDirectory()
	{
		$documentDir = $this->params['loader']['documents']['dir'];
		if (!mkdir($documentDir, 0777, true) && !is_dir($documentDir))
		{
			throw new DirectoryNotFoundException(
				sprintf('Došlo k systémové chybě, sekce pro dokumenty: "%s" nebude vytvořena',
					$documentDir)
			);

		}
		return $this;
	}


}