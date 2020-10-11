<?php /** @noinspection NullPointerExceptionInspection */
declare(strict_types=1);

namespace hispano\ac\anticheat\check\build;

use pocketmine\entity\Effect;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use hispano\ac\anticheat\check\AntiCheatCheck;
use function ceil;
use function floor;
use function microtime;

class FastBreakCheck extends AntiCheatCheck
{
	public const TIME = 1;

	public function onPlayerInteract(PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer();

		if (($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK) && !$player->isCreative()) {
			$this->data[$player->getName()][self::TIME] = floor(microtime(true) * 20);
		}
	}

	public function onBlockBreak(BlockBreakEvent $event): void
	{
		$player = $event->getPlayer();

		if (!$event->getInstaBreak() && !$player->hasEffect(Effect::HASTE)) {
			if ($this->data[$player->getName()][self::TIME] === null) {
				$event->setCancelled();
				return;
			}

			$target = $event->getBlock();
			$item = $event->getItem();
			$expectedTime = ceil($target->getBreakTime($item) * 20);

			if ($player->hasEffect(Effect::MINING_FATIGUE)) {
				$expectedTime *= 1 + (0.3 * $player->getEffect(Effect::MINING_FATIGUE)->getEffectLevel());
			}

			--$expectedTime; //1 tick compensation
			$actualTime = ceil(microtime(true) * 20) - $this->data[$player->getName()][self::TIME];

			if ($actualTime < $expectedTime) {
				$event->setCancelled();
				return;
			}
			$this->data[$player->getName()][self::TIME] = null;
		}
	}

	public function setup(Player $player): void
	{
		$this->data[$player->getName()][self::TIME] = null;
	}

	public function getName(): string
	{
		return 'Fast Break';
	}
}