<?php

namespace hispano\ac\util;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\Player;
use pocketmine\Server;

class Utils
{
    public static function batchPackets($player, $packet): void
    {
        if (is_array($player)) {
            if (is_array($packet)) {
                if (count($packet) > 5) {
                    foreach (array_chunk($packet, 499) as $packets) {
                        Server::getInstance()->batchPackets($player, $packets, false);
                    }
                } else {
                    foreach ($packet as $p) {
                        self::batchPackets($player, $p);
                    }
                }
            } elseif ($packet instanceof DataPacket) {
                foreach ($player as $p) {
                    self::batchPackets($p, $packet);
                }
            }
        } elseif ($player instanceof Player) {
            if (is_array($packet)) {
                foreach ($packet as $pk) {
                    $player->batchDataPacket($pk);
                }
            } else {
                $player->batchDataPacket($packet);
            }
        }
    }
}