<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\combat;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\Player;
use hispano\ac\anticheat\check\KickCheck;
use function microtime;
use function round;

class AutoClickerCheck extends KickCheck
{
	private const CLICK_TIME = 1;
	private const TIME = 2;

	public function onDataPacketReceive(DataPacketReceiveEvent $event): void
	{
		$player = $event->getPlayer();

		if (($time = round(microtime(true) - $this->data[$player->getName()][self::TIME], 4)) === $this->data[$player->getName()][self::CLICK_TIME]) {
			$this->addPoints($player);
		} else {
			$this->removePoints($player);
		}

		$this->data[$player->getName()][self::CLICK_TIME] = $time;
		$this->data[$player->getName()][self::TIME] = microtime(true);
	}

	public function getMaxChecks(): int
	{
		return 100;
	}

	public function getName(): string
	{
		return 'Auto Clicker';
	}

	public function setup(Player $player): void
	{
		$this->data[$player->getName()][self::CLICK_TIME] = 0;
		$this->data[$player->getName()][self::TIME] = microtime(true);

		parent::setup($player);
	}
}