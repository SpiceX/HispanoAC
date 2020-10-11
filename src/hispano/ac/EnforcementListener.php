<?php

declare(strict_types=1);

namespace hispano\ac;

use pocketmine\entity\Effect;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;

class EnforcementListener implements Listener
{
	/** @var Enforcement */
	private $enforcer;

	public function __construct(Enforcement $enforcer)
	{
		$this->enforcer = $enforcer;
	}

	public function onPlayerJoin(PlayerJoinEvent $event): void
	{
		$player = $event->getPlayer();

		$this->enforcer->getAntiCheat()->setLastTick($event->getPlayer(), 0);

		foreach ($this->enforcer->getAntiCheat()->getChecks() as $check) {
			$check->setup($player);
		}

		$this->enforcer->getAntiCheat()->hasJoined[$player->getName()] = true;

		if ($player->hasPermission('urbodus.owner') || $player->hasPermission('urbodus.mod') ||
			$player->hasPermission('urbodus.helper') || $player->hasPermission('urbodus.supervisor') ||
			$player->hasPermission('urbodus.builder') || $player->hasPermission('urbodus.developer')) {
			$this->enforcer->addStaff($player);
		}
	}


	public function onPlayerIllegalMove(PlayerIllegalMoveEvent $event): void
	{
		$event->setCancelled();
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 *
	 * @priority NORMAL
	 *
	 * @ignoreCancelled
	 */
	public function onDataPacketReceive(DataPacketReceiveEvent $event): void
	{
		$player = $event->getPlayer();
		$packet = $event->getPacket();

		if (($packet instanceof InventoryTransactionPacket) && ($packet->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY) && $packet->trData->actionType === InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK && $this->enforcer->getAntiCheat()->hasJoined($player)) {
			$this->enforcer->getAntiCheat()->getKillAuraCheck()->onDataPacketReceive($event);
			$this->enforcer->getAntiCheat()->getAutoClickerCheck()->onDataPacketReceive($event);
		}
	}

	/**
	 * @param PlayerMoveEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onPlayerMove(PlayerMoveEvent $event): void
	{
		$player = $event->getPlayer();

		$newPos = $event->getTo();

		if (!$player->isClosed() && $player->isAlive() && !$player->isSleeping() && $newPos->getY() > 0 && !$player->isUnderwater() && ($player->getGamemode() === Player::SURVIVAL || $player->getGamemode() === Player::ADVENTURE) && $this->enforcer->getAntiCheat()->hasJoined($player)) {
			if ($player->isImmobile()) {
				$this->enforcer->getAntiCheat()->getNoMoveCheck()->onPlayerMove($event);
			} else {
				$this->enforcer->getAntiCheat()->getNoClipCheck()->onPlayerMove($event);

				if (!$player->getAllowFlight()) {
					$this->enforcer->getAntiCheat()->getNoWebCheck()->onPlayerMove($event);
					if (!$player->hasEffect(Effect::LEVITATION)) {
						$this->enforcer->getAntiCheat()->getFlyCheck()->onPlayerMove($event);
					}
				}
			}

			$this->enforcer->getAntiCheat()->setLastTick($player);
		}
	}


	public function onPlayerItemConsume(PlayerItemConsumeEvent $event): void
	{
		$player = $event->getPlayer();

		if (!$player->isClosed() && $player->isAlive() && $this->enforcer->getAntiCheat()->hasJoined($player)) {
			$this->enforcer->getAntiCheat()->getFastEatCheck()->check($event);
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 *
	 * @priority LOW
	 *
	 * @ignoreCancelled
	 */
	public function onBlockBreak(BlockBreakEvent $event): void
	{
		$player = $event->getPlayer();

		if ($player->getGamemode() !== Player::CREATIVE && $this->enforcer->getAntiCheat()->hasJoined($player)) {
			$this->enforcer->getAntiCheat()->getFastBreakCheck()->onBlockBreak($event);
			$this->enforcer->getAntiCheat()->getPhaseBreakCheck()->onPhase($event);
		}
	}


	/**
	 * @param PlayerInteractEvent $event
	 *
	 * @priority LOWEST
	 *
	 * @ignoreCancelled
	 */
	public function onPlayerInteract(PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer();

		if ($this->enforcer->getAntiCheat()->hasJoined($player)) {
			if ($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) {
				$this->enforcer->getAntiCheat()->getFastBreakCheck()->onPlayerInteract($event);
			} elseif ($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
				$this->enforcer->getAntiCheat()->getPhaseBreakCheck()->onPhase($event);
			}
		}

		if ($player->getLevel() === $player->getServer()->getDefaultLevel()) {
			$item = $event->getItem();

		}

	}

	/**
	 * @param EntityDamageByEntityEvent $event
	 *
	 * @priority LOWEST
	 */
	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event): void
	{
		$entity = $event->getEntity();
		$damager = $event->getDamager();

		if ($damager instanceof Player && $entity instanceof Player && !$event instanceof EntityDamageByChildEntityEvent) {
			$this->enforcer->getAntiCheat()->getReachCheck()->onEntityDamageByEntity($event);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();

		if ($this->enforcer->getAntiCheat()->hasJoined($player)) {
			foreach ($this->enforcer->getAntiCheat()->getChecks() as $check) {
				$check->unset($player);
			}

			if ($player->hasPermission('urbodus.mod')) {
				$this->enforcer->removeStaff($player);
			}

			if ($player->hasPermission('urbodus.helper')) {
				$this->enforcer->removeStaff($player);
			}

			unset($this->enforcer->getAntiCheat()->hasJoined[$player->getName()]);
		}
	}
}