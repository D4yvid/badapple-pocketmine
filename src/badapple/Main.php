<?php

namespace badapple;

use pocketmine\plugin\PluginBase;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\block\BlockIds;
use pocketmine\Player;

use badapple\packet\MapDataPacket;
use badapple\packet\MapRequestPacket;

class Main extends PluginBase implements Listener
{
	private static $chunks = [];
	private $frames = [];

	public function onEnable()
	{
		@mkdir($dir = $this->getDataFolder());
		$this->saveResource("frames/frames.txt");

		$frames = explode("\n", file_get_contents("{$dir}/frames/frames.txt"));

		$this->getLogger()->info("Loading frames... ");

		foreach ($frames as $filename) {
			$this->saveResource("frames/$filename");

			$this->frames[] = file_get_contents("{$dir}/frames/$filename");
		}

		$this->getLogger()->info("Loaded " . count($this->frames) . " frames");

		$this->getServer()
			->getPluginManager()
			->registerEvents($this, $this);
	}

	public function interactionHandler(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$item = $player->getItemInHand();

		if ($item->getId() != $item::BONE) {
			return;
		}

		$event->setCancelled(true);

		$face = $event->getFace();
		$chunk = $player->getLevel()->getChunk($block->x >> 4, $block->z >> 4);

		for ($i = 0; $i < 8; $i++) {
			self::$chunks[$i] = [$chunk->getX() - (4 - $i), $chunk->getZ() - 4];
		}
		
		$this->getServer()
			->getScheduler()->scheduleRepeatingTask(new PlaybackTask($player, $this->frames), 2);
	}

	public static function sendFrame(Player $player, array $frame)
	{
		$packets = [];
		$slice = 0;

		foreach (self::$chunks as $pos) {
			$x = $pos[0];
			$z = $pos[1];

			$pk = new FullChunkDataPacket();

			$pk->chunkX = $x;
			$pk->chunkZ = $z;
			$pk->order = $pk::ORDER_LAYERED;
			$pk->data = ChunkGenerator::generateChunkFromColors($frame, $slice);

			$packets[] = $pk;

			$slice++;
		}

		$player->getServer()->batchPackets([$player], $packets);
	}
}
