<?php

namespace badapple;

use pocketmine\level\format\anvil\ChunkSection;
use pocketmine\nbt\tag\{StringTag, IntTag, ByteTag, FloatTag, CompoundTag};

class ChunkGenerator
{
	public static function getSections(bool $recompute)
	{
		$sections = [];

		for ($y = 0; $y < 8; $y++) {
			$nbt = new CompoundTag("", [
				new IntTag("Y", $y),
				new StringTag("Blocks", str_repeat("\x00", 4096)),
				new StringTag("Data", str_repeat("\x00", 2048)),
				new StringTag("SkyLight", str_repeat("\xff", 2048)),
				new StringTag("BlockLight", str_repeat("\x00", 2048))
			]);

			$sections[$y] = new ChunkSection($nbt);
		}

		return $sections;
	}

	public static function generateChunkFromColors(
		array $colors,
		int $slice,
		int $w = 128,
		int $h = 128,
		bool $recompute = false
	) {
		$sections = self::getSections($recompute);

		for ($dx = 0; $dx < 16; $dx++) {
			$x = $dx + 16 * $slice;
			for ($y = 0; $y < $h; $y++) {
				$section = $sections[$y >> 4] ?? null;

				if (!$section) {
					continue;
				}

				$section->setBlock(
					$dx,
					$y & 0x0f,
					0,
					$colors[$x][$h - ($y + 1)],
					0
				);
			}
		}

		$buffer = "";

		for ($y = 0; $y < 8; ++$y) {
			$buffer .= $sections[$y]->getIdArray();
		}

		for ($y = 0; $y < 8; ++$y) {
			$buffer .= $sections[$y]->getDataArray();
		}

		for ($y = 0; $y < 8; ++$y) {
			$buffer .= $sections[$y]->getSkyLightArray();
		}

		for ($y = 0; $y < 8; ++$y) {
			$buffer .= $sections[$y]->getLightArray();
		}

		return $buffer;
	}
}
