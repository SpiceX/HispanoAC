<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\movement;

use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\level\Location;
use hispano\ac\anticheat\check\AntiCheatCheck;
use function floor;

class NoClipCheck extends AntiCheatCheck
{
	public function onPlayerMove(PlayerMoveEvent $event): void
	{
		$player = $event->getPlayer();
		$to = $event->getTo();
		$from = $event->getFrom();

		$block = $player->getLevel()->getBlockAt((int)floor($to->getX()), (int)floor($to->getY() + $player->getEyeHeight()), (int)floor($to->getZ()));

		if ($block->isSolid() && !$block->isTransparent()) {
			$event->setTo(new Location($from->getX(), $from->getY() + 0.5, $from->getZ(), $from->getYaw(), $from->getPitch(), $from->getLevel()));
		}
	}

	public function getName(): string
	{
		return 'No Clip';
	}
}