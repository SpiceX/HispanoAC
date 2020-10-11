<?php
declare(strict_types=1);


namespace hispano\ac;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use hispano\ac\anticheat\AntiCheat;

class Enforcement extends PluginBase
{

	/** @var AntiCheat */
	private $anticheat;

	/** @var Player[] */
	private $staff = [];

	/** @var Enforcement */
	private static $instance;

	public function onEnable(): void
	{
		self::$instance = $this;
		$this->anticheat = new AntiCheat($this);
		$this->getServer()->getPluginManager()->registerEvents(new EnforcementListener($this), $this);
	}

	/**
	 * @return Player[]
	 */
	public function getStaff(): array
	{
		return $this->staff;
	}

	public function addStaff(Player $player): void
	{
		$this->staff[$player->getName()] = $player;
	}

	public function removeStaff(Player $player): void
	{
		unset($this->staff[$player->getName()]);
	}

	public function getAntiCheat(): AntiCheat
	{
		return $this->anticheat;
	}

	public function getPlugin(): Enforcement {
		return $this;
	}

	public static function getInstance(): Enforcement {
		return self::$instance;
	}
}