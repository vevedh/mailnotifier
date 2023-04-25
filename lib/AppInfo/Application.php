<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Hervé de CHAVIGNY <vevedh@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\MailNotifier\AppInfo;

use OCA\FlowNotifications\Flow\Operation;
use OCA\FlowNotifications\Notification\MailNotifier;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\Notification\IManager;
use OCP\Util;
use OCP\WorkflowEngine\Events\RegisterOperationsEvent;



class Application extends App implements IBootstrap {
	public const APP_ID = 'mailnotifier';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
	public function register(IRegistrationContext $context): void {
	}

	public function boot(IBootContext $context): void {
		$container = $context->getServerContainer();
		$container->get(IManager::class)->registerNotifierService(MailNotifier::class);

		$dispatcher = $container->get(IEventDispatcher::class);
		$dispatcher->addListener(RegisterOperationsEvent::class,
				function (RegisterOperationsEvent $event) use ($container) {
					$operation = $container->get(Operation::class);
					$event->registerOperation($operation);
					Util::addScript(self::APP_ID, 'mailnotifier-main');
				}
		);
	}
}
