<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\movement;

use pocketmine\event\player\PlayerMoveEvent;
use hispano\ac\anticheat\check\AntiCheatCheck;

class NoMoveCheck extends AntiCheatCheck
{
	public function onPlayerMove(PlayerMoveEvent $event): void
	{
		$oldPos = $event->getFrom();

		if ($oldPos->distanceSquared($event->getTo()) > 0.5) {
			$event->setCancelled();
		}
	}

	public function getName(): string
	{
		return 'No Move';
	}
}