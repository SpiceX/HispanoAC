<?php

declare(strict_types=1);

namespace hispano\ac\anticheat;

use pocketmine\Player;
use hispano\ac\anticheat\check\AntiCheatCheck;
use hispano\ac\anticheat\check\build\FastBreakCheck;
use hispano\ac\anticheat\check\build\PhaseBreakCheck;
use hispano\ac\anticheat\check\combat\AutoClickerCheck;
use hispano\ac\anticheat\check\combat\KillauraCheck;
use hispano\ac\anticheat\check\combat\ReachCheck;
use hispano\ac\anticheat\check\movement\FlyCheck;
use hispano\ac\anticheat\check\movement\NoClipCheck;
use hispano\ac\anticheat\check\movement\NoMoveCheck;
use hispano\ac\anticheat\check\movement\NoWebCheck;
use hispano\ac\anticheat\check\player\FastEatCheck;
use hispano\ac\Enforcement;

class AntiCheat
{
	/** @var array */
	public $hasJoined = [];
	/** @var AutoClickerCheck */
	private $clicker;
	/** @var KillauraCheck */
	private $killaura;
	/** @var ReachCheck */
	private $reach;
	/** @var FlyCheck */
	private $fly;
	/** @var NoClipCheck */
	private $noclip;
	/** @var NoMoveCheck */
	private $nomove;
	/** @var FastBreakCheck */
	private $fastbreak;
	/** @var PhaseBreakCheck */
	private $phasebreak;
	/** @var FastEatCheck */
	private $fasteat;
	/** @var NoWebCheck */
	private $noweb;
	/** @var array */
	private $lastTick = [];

	/** @var AntiCheatCheck[] */
	private $checks;

	public function __construct(Enforcement $enforcer)
	{
		$this->clicker = new AutoClickerCheck($enforcer);
		$this->fastbreak = new FastBreakCheck($enforcer);
		$this->fasteat = new FastEatCheck($enforcer);
		$this->fly = new FlyCheck($enforcer);
		$this->killaura = new KillauraCheck($enforcer);
		$this->noclip = new NoClipCheck($enforcer);
		$this->nomove = new NoMoveCheck($enforcer);
		$this->noweb = new NoWebCheck($enforcer);
		$this->phasebreak = new PhaseBreakCheck($enforcer);
		$this->reach = new ReachCheck($enforcer);

		$this->checks = [
			$this->clicker,
			$this->fastbreak,
			$this->fasteat,
			$this->fly,
			$this->killaura,
			$this->noclip,
			$this->nomove,
			$this->noweb,
			$this->phasebreak,
			$this->reach,
		];
	}

	/**
	 * @return AutoClickerCheck
	 */
	public function getAutoClickerCheck(): AutoClickerCheck
	{
		return $this->clicker;
	}

	/**
	 * @return KillauraCheck
	 */
	public function getKillAuraCheck(): KillauraCheck
	{
		return $this->killaura;
	}

	public function hasJoined(Player $player): bool
	{
		return isset($this->hasJoined[$player->getName()]);
	}

	/**
	 * @return ReachCheck
	 */
	public function getReachCheck(): ReachCheck
	{
		return $this->reach;
	}

	/**
	 * @return FlyCheck
	 */
	public function getFlyCheck(): FlyCheck
	{
		return $this->fly;
	}

	/**
	 * @return NoClipCheck
	 */
	public function getNoClipCheck(): NoClipCheck
	{
		return $this->noclip;
	}

	/**
	 * @return NoMoveCheck
	 */
	public function getNoMoveCheck(): NoMoveCheck
	{
		return $this->nomove;
	}


	/**
	 * @return FastBreakCheck
	 */
	public function getFastBreakCheck(): FastBreakCheck
	{
		return $this->fastbreak;
	}


	/**
	 * @return PhaseBreakCheck
	 */
	public function getPhaseBreakCheck(): PhaseBreakCheck
	{
		return $this->phasebreak;
	}

	/**
	 * @return FastEatCheck
	 */
	public function getFastEatCheck(): FastEatCheck
	{
		return $this->fasteat;
	}

	/**
	 * @return NoWebCheck
	 */
	public function getNoWebCheck(): NoWebCheck
	{
		return $this->noweb;
	}

	/**
	 * @return AntiCheatCheck[]
	 */
	public function getChecks(): array
	{
		return $this->checks;
	}

	public function getTickDifference(Player $player): int
	{
		return $player->getServer()->getTick() - $this->getLastTick($player);
	}

	public function getLastTick(Player $player): int
	{
		return $this->lastTick[$player->getName()];
	}

	public function setLastTick(Player $player, int $tick = -1): void
	{
		if ($tick === -1) {
			$this->lastTick[$player->getName()] = $player->getServer()->getTick();
		} else {
			$this->lastTick[$player->getName()] = $tick;
		}
	}
}
