<?php

declare(strict_types=1);

namespace hispano\ac\anticheat\check;


use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

use function count;

abstract class KickCheck extends AntiCheatCheck
{
	public const POINTS = 0;

	public function removePoints(Player $player, int $points = 1): void
	{
		if (($this->data[$player->getName()][self::POINTS] - $points) > 0) {
			$this->data[$player->getName()][self::POINTS] -= $points;
		} else {
			$this->data[$player->getName()][self::POINTS] = 0;
		}
	}

	public function addPoints(Player $player, int $points = 1): void
	{
		$ess = $this->getEnforcer()->getPlugin();

		$this->data[$player->getName()][self::POINTS] += $points;
		if ($this->data[$player->getName()][self::POINTS] >= $this->getMaxChecks()) {

			$message = '§cYou have been kicked from game.' . ' §cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: ' . $this->getName() . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.';
			$ess->getScheduler()->scheduleDelayedTask(new class($player, $message) extends Task {
				private $player;
				private $message;

				public function __construct(Player $player, string $message)
				{
					$this->player = $player;
					$this->message = $message;
				}

				public function onRun(int $currentTick): void
				{
					if ($this->player !== null) {
						$this->player->sendMessage($this->message);
						if ($this->player->getServer()->getPort() !== Cluster::SERVER_LOBBY_PORT) {
							$this->player->sendMessage('§cYou have been kicked from game.' . ' §cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: Fly' . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.');
							Server::getInstance()->dispatchCommand($this->player, 'transfer Lobby');
						} else {
							$this->player->sendMessage('§cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: Fly' . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.');
						}
					}
				}
			}, 1);
			$this->unset($player);

			$this->data[$player->getName()][self::POINTS] = 0;
		} elseif ($this->data[$player->getName()][self::POINTS] >= ($this->getMaxChecks() * 0.8)) {
			if (count($this->getEnforcer()->getStaff()) > 0) {
				$ess->getServer()->broadcastMessage('§b' . $player->getName() . ' §6may be using §b' . $this->getName(), $this->getEnforcer()->getStaff());
			}
		}
	}

	public function getMaxChecks(): int
	{
		return 0;
	}

	public function setPoints(Player $player, int $points): void
	{
		$this->data[$player->getName()][self::POINTS] = $points;
	}

	public function setup(Player $player): void
	{
		$this->data[$player->getName()][self::POINTS] = 0;
	}
}