<?php

namespace badapple;

use badapple\video\VideoFrame;
use pocketmine\level\format\anvil\Chunk;
use pocketmine\level\format\anvil\ChunkSection;
use pocketmine\nbt\tag\{StringTag, IntTag, CompoundTag};
use pocketmine\network\protocol\FullChunkDataPacket;

class ChunkGenerator
{

	public static function createEmptySections(int $count)
	{
		$sections = [];

		for ($y = 0; $y < $count; $y++) {
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

	// public static function generateChunkFromColors(
	// 	array $colors,
	// 	int $slice,
	// 	int $w = 128,
	// 	int $h = 128
	// ) {
	// 	$sections = self::createEmptySections($w >> 4 /* divided by 16; the size of a chunk */);
	//
	// 	for ($dx = 0; $dx < 16; $dx++) {
	// 		$x = $dx + 16 * $slice;
	//
	// 		for ($y = 0; $y < $h; $y++) {
	// 			$section = $sections[$y >> 4] ?? null;
	//
	// 			if (!$section) {
	// 				continue;
	// 			}
	//
	// 			$section->setBlock($dx, $y & 0x0f, 0, $colors[$x][$h - $y], 0);
	// 		}
	// 	}
	//
	// 	return self::generateFullChunkData($sections);
	// }

	/** @return string[] */
	public static function generateChunksFromVideoFrame(VideoFrame $frame)
	{
		$buffers = [];

		$data = $frame->getData();
		$width = $frame->getWidth();
		$height = $frame->getHeight();
		$sliceCount = $frame->getSliceCount();

		for ($slice = 0; $slice < $sliceCount; $slice++) {
			$sections = self::createEmptySections($width >> 4 /* divided by 16; the size of a chunk */);

			for ($x = 0; $x < 16; $x++) {
				$dx = $x + (16 * $slice);

				for ($y = 0; $y < $height; $y++) {
					$section = $sections[$y >> 4] ?? null;

					if (!$section)
						continue;

					$dy = ($height - (1 + $y));

					$column = $data[$dx];

					if (!isset($column[$dy])) {
						continue;
					}

					$id = $column[$dy];

					$section->setBlockId($x, $y & 0x0f, 0, $id);
				}
			}

			$buffer = ChunkGenerator::generateFullChunkData($sections);

			$buffers[$slice] = $buffer;
		}

		return $buffers;
	}

	public static function generateFullChunkData(array $chunkSections)
	{
		$buffer = "";

		for ($y = 0; $y < Chunk::SECTION_COUNT; ++$y) {
			$buffer .= $chunkSections[$y]->getIdArray();
		}

		for ($y = 0; $y < Chunk::SECTION_COUNT; ++$y) {
			$buffer .= $chunkSections[$y]->getDataArray();
		}

		for ($y = 0; $y < Chunk::SECTION_COUNT; ++$y) {
			$buffer .= $chunkSections[$y]->getSkyLightArray();
		}

		for ($y = 0; $y < Chunk::SECTION_COUNT; ++$y) {
			$buffer .= $chunkSections[$y]->getLightArray();
		}

		return $buffer;
	}

	public static function generateChunkPacket(int $x, int $z, string $data)
	{
		$pk = new FullChunkDataPacket();

		$pk->chunkX = $x;
		$pk->chunkZ = $z;
		$pk->order = $pk::ORDER_LAYERED;
		$pk->data = $data;

		return $pk;
	}
}
