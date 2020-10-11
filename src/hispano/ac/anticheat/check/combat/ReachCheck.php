<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use hispano\ac\anticheat\check\AntiCheatCheck;

class ReachCheck extends AntiCheatCheck
{
	public const ENTITY_MAX_DISTANCE = 5.9;
	public const ENTITY_MAX_DISTANCE_CREATIVE = 6.5;

	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
	{
		/** @var Player $damager */
		$damager = $event->getDamager();

		if ($damager->getGamemode() === Player::CREATIVE) {
			$maxDistance = self::ENTITY_MAX_DISTANCE_CREATIVE;
		} else {
			$maxDistance = self::ENTITY_MAX_DISTANCE;
		}

		if (!$damager->canInteract($event->getEntity(), $maxDistance)) {
			$event->setCancelled();
		}
	}

	public function getName(): string
	{
		return 'Reach';
	}
}