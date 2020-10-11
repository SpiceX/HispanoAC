<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\check\player;

use hispano\ac\anticheat\check\AntiCheatCheck;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class PacketCheck extends AntiCheatCheck
{
	public function onDataPacketReceive(DataPacketReceiveEvent $event): void
	{
        $player = $event->getPlayer();
		/** @var AdventureSettingsPacket $packet */
		$packet = $event->getPacket();

		if (!$player->getAllowFlight() && ($packet->getFlag(AdventureSettingsPacket::FLYING) || $packet->getFlag(AdventureSettingsPacket::ALLOW_FLIGHT))) {
			if ($player->getServer()->getPort() !== Cluster::SERVER_LOBBY_PORT) {
				$player->sendMessage('§cYou have been kicked from game.' . ' §cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: Fly' . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.');
				Server::getInstance()->dispatchCommand($player, 'transfer Lobby');
			} else {
				$player->sendMessage('§cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: Fly' . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.');
			}
		} elseif ($packet->getFlag(AdventureSettingsPacket::NO_CLIP) && !$player->isSpectator()) {
			if ($player->getServer()->getPort() !== Cluster::SERVER_LOBBY_PORT) {
				$player->sendMessage('§cYou have been kicked from game.' . ' §cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: Fly' . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.');
				Server::getInstance()->dispatchCommand($player, 'transfer Lobby');
			} else {
				$player->sendMessage('§cThe ac has detected that you are using a prohibited mod or addon. Please remove them before reconnecting. Activity detected: Fly' . TextFormat::EOL . '§7Our ac frequently makes mistakes. If you were not using prohibited mods or addons, we apologise in advance.');
			}
		} else {
			$player->sendSettings();
		}
	}

	public function getName(): string
	{
		return 'Packet';
	}
}