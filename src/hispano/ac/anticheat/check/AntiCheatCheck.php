<?php

declare(strict_types=1);

namespace hispano\ac\anticheat\check;

use pocketmine\Player;
use hispano\ac\Enforcement;

abstract class AntiCheatCheck
{
	/** @var array */
	protected $data;
	/** @var Enforcement */
	private $enforcer;

	public function __construct(Enforcement $enforcer)
	{
		$this->enforcer = $enforcer;
	}

	public function getEnforcer(): Enforcement
	{
		return $this->enforcer;
	}

	abstract public function getName(): string;

	public function unset(Player $player): void
	{
		unset($this->data[$player->getName()]);
	}

	public function setup(Player $player): void
	{

	}
}