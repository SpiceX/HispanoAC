<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\player;

use pocketmine\event\player\PlayerItemConsumeEvent;
use hispano\ac\anticheat\check\AntiCheatCheck;

class FastEatCheck extends AntiCheatCheck
{
	public function check(PlayerItemConsumeEvent $event): void
	{
		$player = $event->getPlayer();
		if ($player->getItemUseDuration() < $player->getServer()->getTicksPerSecond()) {
			$player->setUsingItem(false);
			$event->setCancelled();
		}
	}

	public function getName(): string
	{
		return 'Fast Eat';
	}
}