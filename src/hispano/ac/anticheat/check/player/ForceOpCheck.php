<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\player;

use pocketmine\Player;
use hispano\ac\anticheat\check\AntiCheatCheck;

class ForceOpCheck extends AntiCheatCheck
{
	public function check(Player $player): void
	{
		if ($player->isOp()) {
			$player->setOp(false);
		}
	}

	public function getMaxChecks(): int
	{
		return 1;
	}

	public function getName(): string
	{
		return 'Force OP';
	}
}