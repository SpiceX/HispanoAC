<?php
declare(strict_types=1);

namespace hispano\ac\anticheat\metrics;


use InvalidArgumentException;
use pocketmine\level\Level;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\Player;

class Metrics
{
	public static function XZDistanceSquared(Vector3 $v1, Vector3 $v2): float
	{
		return (($v1->x - $v2->x) ** 2) + (($v1->z - $v2->z) ** 2);
	}

	public static function YDistanceSquared(Vector3 $v1, Vector3 $v2): float
	{
		return (($v1->y - $v2->y) ** 2);
	}

	public static function getNearBlocks(Player $player, Vector3 $newPos, $id = false): array
	{
		$blocks = [];

		if ($id) {
			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), (int)($newPos->getY() + 0.1), $newPos->getFloorZ()); // block at players feet (used to make sure player isn't in a transparent block (cobwebs, water, etc)
			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), (int)($newPos->getY() - 0.5), $newPos->getFloorZ()); // block the player is on (use this for checking slabs, stairs, etc)
			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ());

			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX() + 1, $newPos->getFloorY(), $newPos->getFloorZ());
			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX() - 1, $newPos->getFloorY(), $newPos->getFloorZ());
			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ() + 1);
			$blocks[] = $player->getLevel()->getBlockIdAt($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ() - 1);
		} else {
			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX(), (int)($newPos->getY() + 0.1), $newPos->getFloorZ()); // block at players feet (used to make sure player isn't in a transparent block (cobwebs, water, etc)
			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX(), (int)($newPos->getY() - 0.5), $newPos->getFloorZ()); // block the player is on (use this for checking slabs, stairs, etc)
			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ());

			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX() + 1, $newPos->getFloorY(), $newPos->getFloorZ());
			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX() - 1, $newPos->getFloorY(), $newPos->getFloorZ());
			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ() + 1);
			$blocks[] = $player->getLevel()->getBlockAt($newPos->getFloorX(), $newPos->getFloorY(), $newPos->getFloorZ() - 1);
		}


		return $blocks;
	}

	public static function getSurroundingBlocks(Player $player, Vector3 $pos): array
	{
		$blocks = [];

		$blocks[] = $player->getLevel()->getBlockAt($pos->getFloorX() + 1, $pos->getY(), $pos->getFloorZ());
		$blocks[] = $player->getLevel()->getBlockAt($pos->getFloorX() - 1, $pos->getY(), $pos->getFloorZ());
		$blocks[] = $player->getLevel()->getBlockAt($pos->getFloorX(), $pos->getFloorY() + 1, $pos->getFloorZ());
		$blocks[] = $player->getLevel()->getBlockAt($pos->getFloorX(), $pos->getFloorY() - 1, $pos->getFloorZ());
		$blocks[] = $player->getLevel()->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ() + 1);
		$blocks[] = $player->getLevel()->getBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ() - 1);

		return $blocks;
	}

	public static function raycast(Level $level, Vector3 $start, Vector3 $end, float $radius = 50): ?RayTraceResult
	{
		$currentBlock = $start->floor();

		$directionVector = $end->subtract($start)->normalize();
		if ($directionVector->lengthSquared() <= 0) {
			throw new InvalidArgumentException('Start and end points are the same, giving a zero direction vector');
		}

		$stepX = $directionVector->x <=> 0;
		$stepY = $directionVector->y <=> 0;
		$stepZ = $directionVector->z <=> 0;

		//Initialize the step accumulation variables depending how far into the current block the start position is. If
		//the start position is on the corner of the block, these will be zero.
		$tMaxX = self::rayTraceDistanceToBoundary($start->x, $directionVector->x);
		$tMaxY = self::rayTraceDistanceToBoundary($start->y, $directionVector->y);
		$tMaxZ = self::rayTraceDistanceToBoundary($start->z, $directionVector->z);

		//The change in t on each axis when taking a step on that axis (always positive).
		$tDeltaX = $directionVector->x === 0 ? 0 : $stepX / $directionVector->x;
		$tDeltaY = $directionVector->y === 0 ? 0 : $stepY / $directionVector->y;
		$tDeltaZ = $directionVector->z === 0 ? 0 : $stepZ / $directionVector->z;

		while (true) {
			$block = $level->getBlock($currentBlock);
			$hit = $block->calculateIntercept($start, $end);
			if ($hit !== null) {
				return $hit;
			}

			// tMaxX stores the t-value at which we cross a cube boundary along the
			// X axis, and similarly for Y and Z. Therefore, choosing the least tMax
			// chooses the closest cube boundary.
			if ($tMaxX < $tMaxY && $tMaxX < $tMaxZ) {
				if ($tMaxX > $radius) {
					break;
				}
				$currentBlock->x += $stepX;
				$tMaxX += $tDeltaX;
			} elseif ($tMaxY < $tMaxZ) {
				if ($tMaxY > $radius) {
					break;
				}
				$currentBlock->y += $stepY;
				$tMaxY += $tDeltaY;
			} else {
				if ($tMaxZ > $radius) {
					break;
				}
				$currentBlock->z += $stepZ;
				$tMaxZ += $tDeltaZ;
			}
		}

		return null;
	}

	private static function rayTraceDistanceToBoundary(float $s, float $ds): float
	{
		if ($ds === 0) {
			return INF;
		}

		if ($ds < 0) {
			$s = -$s;
			$ds = (float)abs($ds);

			if (floor($s) === $s) { //exactly at coordinate, will leave the coordinate immediately by moving negatively
				return 0;
			}
		}

		// problem is now s+t*ds = 1
		return (1 - ($s - floor($s))) / $ds;
	}
}