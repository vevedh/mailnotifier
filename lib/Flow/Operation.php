<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @author Arthur Schiwon <blizzz@arthur-schiwon.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\MailNotifier\Flow;

use DateTime;
use OCA\MailNotifier\AppInfo\Application;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\GenericEvent;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Mail\IMailer;
use OCP\Notification\IManager;
use OCP\WorkflowEngine\EntityContext\IContextPortation;
use OCP\WorkflowEngine\EntityContext\IUrl;
use OCP\WorkflowEngine\IManager as FlowManager;
use OCP\WorkflowEngine\IOperation;
use OCP\WorkflowEngine\IRuleMatcher;
use Psr\Log\LoggerInterface;
use function OCP\Log\logger;
use UnexpectedValueException;
use function json_decode;
use function json_encode;

class Operation implements IOperation {

	/** @var IL10N */
	private $l;
	/** @var IURLGenerator */
	private $urlGenerator;

	/** @var IMailer */
	private $mailer;
	/** @var IUserSession */
	private $userSession;
	/** @var LoggerInterface */
	private $logger;

	public function __construct(
		IL10N $l,
		IURLGenerator $urlGenerator,
		IMailer $mailer,
		IUserSession $userSession,
		LoggerInterface $logger
	) {
		$this->l = $l;
		$this->urlGenerator = $urlGenerator;
		$this->mailer = $mailer;
		$this->userSession = $userSession;
		$this->logger = $logger;
	}

	/**
	 * @inheritDoc
	 */
	public function getDisplayName(): string {
		return $this->l->t('Envoyer un e-mail de notification');
	}

	/**
	 * @inheritDoc
	 */
	public function getDescription(): string {
		return $this->l->t('Déclenche un e-mail de notification');
	}

	/**
	 * @inheritDoc
	 */
	public function getIcon(): string {
		return $this->urlGenerator->imagePath('mailnotifier', 'mailnotifier.svg');
	}

	/**
	 * @inheritDoc
	 */
	public function isAvailableForScope(int $scope): bool {
		return true; //$scope === FlowManager::SCOPE_USER;
	}

	/**
	 * @inheritDoc
	 */
	public function validateOperation(string $name, array $checks, string $operation): void {
		// pass
	}

	/**
	 * @inheritDoc
	 */
	public function onEvent(string $eventName, Event $event, IRuleMatcher $ruleMatcher): void {
		if (!$event instanceof GenericEvent) {
			return;
		}
		
		$entity = $ruleMatcher->getEntity();

		$type = $entity->getName();

		if ($eventName === '\OCP\Files::postRename' || $eventName === '\OCP\Files::postCopy') {
			/** @var Node $oldNode */
			[, $node] = $event->getSubject();
		} else {
			$node = $event->getSubject();
		}

		[,, $folder,] = explode('/', $node->getPath(), 4);
		if ($folder !== 'files' || $node instanceof Folder) {
			return;
		}

		$flows = $ruleMatcher->getFlows(false);
		foreach ($flows as $flow) {
			try {
				$uid = $flow['scope_actor_id'];
				$sessionUser = $this->userSession->getUser();
				$flowOptions = json_decode($flow['operation'], true);
				if (!is_array($flowOptions) || empty($flowOptions)) {
					throw new UnexpectedValueException('Impossible de récupérer le détail de l\opération');
				}

				//$sessionUser = $this->userSession->getUser();
				//$sessionUser->getAdressEmail();
				if (str_contains(trim($flowOptions['to'] ?? ''),';')) {
					$sendmails = explode(';',trim($flowOptions['to'] ?? ''));
				} else {
					$sendmails = array(trim($flowOptions['to'] ?? ''));
				}

				$subject = trim($flowOptions['subject'] ?? '');
				$mailcontent = trim($flowOptions['mailcontent'] ?? '');
				$from = trim($flowOptions['from'] ?? '');
				if (($from === '') || (!str_contains($from,',')) || (!str_contains($from,'@'))) {
					throw new UnexpectedValueException('Impossible de récupérer l\'adresse source');
				}
				$fromSplit = explode(',',$from);
				$fromArray = array($fromSplit[0]=>$fromSplit[1]);
				
			
				if ($uid) {
					$message =  $this->mailer->createMessage();
					$message->setSubject($subject);
					$message->setFrom($fromArray);
					$message->setTo(array($sessionUser->getEMailAddress()));
					$message->setBody($mailcontent, 'text/html');
					$this->mailer->send($message);
				} else {
					
					array_push($sendmails,$sessionUser->getEMailAddress());
					$message =  $this->mailer->createMessage();
					$message->setSubject($subject);
					$message->setFrom($fromArray);
					$message->setTo($sendmails);
					$message->setBody($mailcontent, 'text/html');
					$this->mailer->send($message);
				}
				

			} catch (UnexpectedValueException $e) {
				continue;
			}
		}

		
		
	}
}
