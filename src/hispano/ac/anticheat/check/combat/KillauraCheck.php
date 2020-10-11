<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\combat;

use Exception;
use hispano\ac\util\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\Skin;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\Player;
use pocketmine\utils\UUID;
use hispano\ac\anticheat\check\KickCheck;
use hispano\ac\Enforcement;
use function random_int;
use function round;
use function str_repeat;

class KillauraCheck extends KickCheck
{
	/** @var int */
	private $entityRuntimeId;
	/** @var UUID */
	private $uuid;
	/** @var string */
	private $username;
	/** @var Skin */
	private $skin;

	public function __construct(Enforcement $enforcer)
	{
		try {
			$this->entityRuntimeId = random_int(10000, 100000);
		} catch (Exception $e) {
		}
		$this->uuid = UUID::fromRandom();
		$this->username = "\u{2800} \u{2800}";
		$this->skin = new Skin('Standard_Custom', str_repeat("\x00", 64 * 64 * 4), '', 'geometry.dummy');
		parent::__construct($enforcer);
	}

	public function onDataPacketReceive(DataPacketReceiveEvent $event): void
	{
		$player = $event->getPlayer();
		/** @var InventoryTransactionPacket */
		$packet = $event->getPacket();

		if ($packet->trData->entityRuntimeId === $this->getEntityRuntimeId()) {
			$this->addPoints($player);
		} else {
			$this->removePoints($player);
		}

		$this->teleport($player);
	}

	public function getEntityRuntimeId(): int
	{
		return $this->entityRuntimeId;
	}

	public function teleport(Player $player): void
	{
		$x = round($player->getDirectionVector()->getX(), 2);
		$y = round($player->getDirectionVector()->getY(), 2);
		$z = round($player->getDirectionVector()->getZ(), 2);

		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->entityRuntimeId;
		$pk->position = new Vector3($player->getX() + ($x === 0 ? -3 : $x * -3), ($y >= 0.75 ? $player->getY() - 3 : $player->getY() + 5), $player->getZ() + ($z === 0 ? -3 : $z * -3));
		$pk->pitch = 0;
		$pk->headYaw = 0;
		$pk->yaw = 0;
		$pk->mode = MovePlayerPacket::MODE_TELEPORT;
		$player->dataPacket($pk);
	}

	public function setup(Player $player): void
	{
		$packets = [];

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->getEntityRuntimeId(), $this->username, SkinAdapterSingleton::get()->toSkinData($this->skin))];
		$packets[] = $pk;

		$x = round($player->getDirectionVector()->getX(), 2);
		$y = round($player->getDirectionVector()->getY(), 2);
		$z = round($player->getDirectionVector()->getZ(), 2);

		$pk = new AddPlayerPacket();
		$pk->uuid = $this->uuid;
		$pk->username = $this->username;
		$pk->entityRuntimeId = $this->getEntityRuntimeId();
		$pk->position = new Vector3($player->getX() + ($x === 0 ? -3 : $x * -3), $player->getY() + ($y === 0 ? -3 : $y * -3), $player->getZ() + ($z === 0 ? -3 : $z * -3));
		$pk->item = ItemFactory::get(Item::AIR);
		$pk->metadata = [
			Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->username],
			Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.85]
		];
		$packets[] = $pk;

		Utils::batchPackets($player, $packets);

		parent::setup($player);
	}

	public function getMaxChecks(): int
	{
		return 10;
	}

	public function getName(): string
	{
		return 'KillAura';
	}
}