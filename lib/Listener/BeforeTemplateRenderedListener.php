<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2021 Joas Schilling <coding@schilljs.com>
 *
 * @author Joas Schilling <coding@schilljs.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Notifications\Listener;

use OCA\Notifications\AppInfo\Application;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Util;

class BeforeTemplateRenderedListener implements IEventListener {
	protected IConfig $config;
	protected IUserSession $userSession;
	protected IInitialState $initialState;

	public function __construct(IConfig $config,
								IUserSession $userSession,
								IInitialState $initialState) {
		$this->config = $config;
		$this->userSession = $userSession;
		$this->initialState = $initialState;
	}

	public function handle(Event $event): void {
		if (!($event instanceof BeforeTemplateRenderedEvent)) {
			// Unrelated
			return;
		}

		if ($event->getResponse()->getRenderAs() !== TemplateResponse::RENDER_AS_USER) {
			return;
		}

		if (!$this->userSession->getUser() instanceof IUser) {
			return;
		}

		$this->initialState->provideInitialState(
			'sound_notification',
			$this->config->getUserValue(
				$this->userSession->getUser()->getUID(),
				Application::APP_ID,
				'sound_notification',
				'yes'
			) === 'yes'
		);

		$this->initialState->provideInitialState(
			'sound_talk',
			$this->config->getUserValue(
				$this->userSession->getUser()->getUID(),
				Application::APP_ID,
				'sound_talk',
				'yes'
			) === 'yes'
		);

		Util::addScript('notifications', 'notifications-main');
	}
}
