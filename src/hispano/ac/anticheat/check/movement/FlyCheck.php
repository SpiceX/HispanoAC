<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\movement;

use pocketmine\block\BlockIds;
use pocketmine\entity\Effect;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use hispano\ac\anticheat\check\KickCheck;
use hispano\ac\anticheat\metrics\Metrics;
use pocketmine\Player;
use function exp;
use function microtime;
use function round;

class FlyCheck extends KickCheck
{
	public function onPlayerMove(PlayerMoveEvent $event): void
	{
		/** @var Player $player */
		$player = $event->getPlayer();
		$newPos = $event->getTo();
		$oldPos = $event->getFrom();

		$yDistance = round($newPos->getY() - $oldPos->getY(), 3);

		if (microtime(true) - $player->getLastDamagedTime() >= 5) { // player hasn't taken damage for five seconds
			if (($yDistance >= 0.07 || ($player->getInAirTicks() >= 75 && $yDistance >= 0)) && $player->getLastMoveTime() - $player->getLastJumpTime() >= 2) { // if the movement wasn't downwards and the player hasn't jumped for 2 seconds
				foreach (Metrics::getNearBlocks($player, $newPos, true) as $block) {
					if ($block !== BlockIds::AIR) {
						$this->removePoints($player);
						return;
					}
				}

				$secondBlockBelowId = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), $newPos->getFloorY() - 2, $newPos->getFloorZ());
				if ($secondBlockBelowId === BlockIds::AIR) { // if two blocks directly below them is air
					$thirdBlockBelowId = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), $newPos->getFloorY() - 3, $newPos->getFloorZ());
					if ($thirdBlockBelowId === BlockIds::AIR) { // if three blocks directly below them is air
						$this->addPoints($player, 3);
					} else {
						$this->addPoints($player, 2);
					}
				} else {
					$this->addPoints($player);
				}

				if ($yDistance >= 0.6) {
					$this->addPoints($player, 4);
				} elseif ($yDistance >= 0.45) {
					$this->addPoints($player, 3);
				} elseif ($yDistance >= 0.38) {
					$this->addPoints($player, 2);
				}
			} else {
				$tickDiff = $this->getEnforcer()->getAntiCheat()->getTickDifference($player);
				$speed = $newPos->subtract($player->getLocation())->divide($tickDiff);
				if ($player->getInAirTicks() > 50) {
					$expectedVelocity = -0.08 / 0.02 - (-0.08 / 0.02) * exp(-0.02 * ($player->getInAirTicks() - $player->getStartAirTicks()));
					$jumpVelocity = (0.42 + ($player->hasEffect(Effect::JUMP) ? ($player->getEffect(Effect::JUMP)->getEffectLevel() / 10) : 0)) / 0.42;
					$diff = (($speed->getY() - $expectedVelocity) ** 2) / $jumpVelocity;
					if ($diff > 0.6 && $expectedVelocity < $speed->getY() && $newPos->getY() >= ($oldPos->getY() - 0.2)) {
						if ($player->getInAirTicks() < 100) {
							$player->setMotion(new Vector3(0, $expectedVelocity, 0));
						} else {
							$this->addPoints($player);
						}
					} else {
						$this->removePoints($player);
					}
				} else {
					$this->removePoints($player, 2);
				}
			}
		}
	}

	public function getMaxChecks(): int
	{
		return 15;
	}

	public function getName(): string
	{
		return 'Fly';
	}
}