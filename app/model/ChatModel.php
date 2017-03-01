<?php
/**
 * Created by PhpStorm.
 * User: Ing. Vít Sádecký
 * Date: 6. 2. 2017
 * Time: 6:11
 */

namespace App\Model;

use App\Entity\User;
use App\Entity\Chat;

/**
 * Class ChatModel
 * Slouzi k pridavani a odebirani komentaru v chatu
 *
 * @package App\Model
 */
class ChatModel extends BaseModel
{
	/**
	 * Pridej komentar do chatu
	 *
	 * @param $text - text/komentar, ktery bude pridan
	 * @param $userID -id uzivatele, ktery komentar pridal
	 *
	 * @throws \Exception
	 * @return $this
	 */
	public function addComment($text, $userID)
	{
		$chat = new Chat();
		$chat->setAuthor($this->entityManager->find(User::class, $userID))
			->setComment($text)
			->setCreated();

		$this->entityManager->persist($chat);
		$this->entityManager->flush();
		return $this;
	}


	/**
	 * Provede odstraneni komentare
	 *
	 * @param $id - id komentare, ktery ma byt odstranen
	 *
	 * @throws \Exception
	 * @return $this
	 */
	public function deleteComment($id)
	{
		/**
		 * @var Chat $post
		 */
		$post = $this->getItem(Chat::class, $id);
		$post->setAsDeleted();
		$this->entityManager->persist($post);
		$this->entityManager->flush();
		return $this;
	}


}