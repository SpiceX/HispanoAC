<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\movement;

use pocketmine\block\Cobweb;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Math;
use hispano\ac\anticheat\check\KickCheck;
use hispano\ac\anticheat\metrics\Metrics;

class NoWebCheck extends KickCheck
{
	public const WEB_MOVE_SPEED = 0.645;

	public function onPlayerMove(PlayerMoveEvent $event): void
	{
		$player = $event->getPlayer();

		if ($player->getLevel()->getBlockAt($player->getFloorX(), $player->getFloorY(), $player->getFloorZ()) instanceof Cobweb || $player->getLevel()->getBlockAt(Math::floorFloat($player->getX()), Math::floorFloat($y = ($player->getY() + $player->getEyeHeight())), Math::floorFloat($player->getZ())) instanceof Cobweb) {
			$tickDiff = $this->getEnforcer()->getAntiCheat()->getTickDifference($player);
			$speed = Metrics::XZDistanceSquared($event->getTo(), $event->getTo());
			$seconds = $tickDiff / 20;

			$diffX = $player->getX() - $event->getTo()->getX();
			$diffY = $player->getY() - $event->getTo()->getY();
			$diffZ = $player->getZ() - $event->getTo()->getZ();
			$diff = ($diffX ** 2 + $diffY ** 2 + $diffZ ** 2) / ($tickDiff ** 2);

			if (($speed > (self::WEB_MOVE_SPEED * $seconds) || $diff > 0.2)) {
				$this->addPoints($player);
				return;
			}
			$this->removePoints($player);
		}
	}

	public function getMaxChecks(): int
	{
		return 10;
	}

	public function getName(): string
	{
		return 'No Web';
	}
}