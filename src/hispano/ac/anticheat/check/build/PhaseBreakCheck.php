<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\build;

use pocketmine\block\Bed;
use pocketmine\block\Chest;
use pocketmine\block\Glass;
use pocketmine\block\Transparent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Event;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Chest as ChestTile;
use hispano\ac\anticheat\check\AntiCheatCheck;
use hispano\ac\anticheat\metrics\Metrics;

class PhaseBreakCheck extends AntiCheatCheck
{

	public function onPhase(Event $event): void
	{
		if ($event instanceof BlockBreakEvent || $event instanceof PlayerInteractEvent) {
			$player = $event->getPlayer();
			$block = $event->getBlock();

			/*if (($inputMode = $this->getEnforcer()->getPlugin()->getPlayerData()->getInt($player, PlayerData::INPUT_MODE)) === InputMode::MOUSE_KEYBOARD || $inputMode === InputMode::GAME_PAD) {
				if (in_array($block->getId(), [BlockIds::BED_BLOCK, BlockIds::CHEST], true)) {
					$start = $event->getPlayer()->asVector3()->add(0, $event->getPlayer()->getEyeHeight(), 0);
					$rad = 10;
					$direction = $event->getPlayer()->getDirectionVector();
					$end = $start->add($direction->multiply($rad));

					$res = Metrics::raycast($event->getPlayer()->getLevel(), $start, $end, $rad);
					if ($res !== null && $res->getHitVector()->equals($block->asVector3())) {
						$event->setCancelled();
					}
				}
			} else {*/
			$count = 0;
			foreach (Metrics::getSurroundingBlocks($player, $block) as $block) {
				if ($block instanceof Glass || !$block instanceof Transparent) {
					$count++;
				}
			}

			/** @var ChestTile $tile */
			if ($block instanceof Bed || ($block instanceof Chest && ($tile = $block->getLevel()->getTileAt($block->getFloorX(), $block->getFloorY(), $block->getFloorZ())) instanceof ChestTile && $tile->isPaired())) {
				$extra = null;
				if ($block instanceof Bed) {
					$extra = $block->getOtherHalf();
				} elseif ($block instanceof Chest) {
					$extra = $tile->getPair();
				}

				$extraCount = -1;
				if ($extra !== null) {
					$extraCount = 0;
					foreach (Metrics::getSurroundingBlocks($player, $extra) as $block) {
						if ($block instanceof Glass || !$block instanceof Transparent) {
							$extraCount++;
						}
					}
				}

				if ($count >= 5) {
					if ($extraCount !== -1 && $extraCount >= 5) {
						$event->setCancelled();
					} else {
						return;
					}
					$event->setCancelled();
				}
			} elseif ($count === 6) {
				$event->setCancelled();
			}
			//}
		}
	}

	public function getName(): string
	{
		return 'Phase Break';
	}
}