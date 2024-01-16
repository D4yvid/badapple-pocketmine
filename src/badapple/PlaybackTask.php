<?php

namespace badapple;

use pocketmine\scheduler\Task;
use pocketmine\item\ItemIds;
use pocketmine\Player;

class PlaybackTask extends Task
{
	private $player;
	private $frames;
	private $currentFrame;
	private $frameCount;

	public function __construct(Player $player, array $frames)
	{
		$this->player = $player;
		$this->currentFrame = 0;
		$this->frameCount = count($frames);
		$this->frames = [];

		for ($i = 0; $i < $this->frameCount; $i++) {
			$frame = str_replace("\n", "", $frames[$i]);

			if (strlen($frame) < 128 * 128) {
				$player->sendPopup("WARN: frame $i is incomplete");

				continue;
			}

			$data = array_fill(0, 128, array_fill(0, 128, ItemIds::WOOL));

			for ($y = 0; $y < 128; $y++) {
				for ($x = 0; $x < 128; $x++) {
					$chr = $frame[$y * 128 + $x];

					if ($chr == " ") {
						$data[$x][$y] = ItemIds::OBSIDIAN;
					}
				}
			}

			$this->frames[] = $data;

			$player->sendTip("Processed frame $i/" . $this->frameCount);
		}

		$player->sendPopup(
			"Processed " . count($this->frames) . ", starting playback"
		);

		$this->frameCount = count($this->frames);
	}

	public function onRun($currentTick)
	{
		if ($this->currentFrame >= $this->frameCount) {
			$this->player
				->getServer()
				->getScheduler()
				->cancelTask($this->getTaskId());
			
			return;
		}
		
		$frame = $this->frames[$this->currentFrame];

		$this->currentFrame++;

		if (!$frame) {
			return;
		}

		Main::sendFrame($this->player, $frame);
		$this->player->sendTip(
			"Playback: " . $this->currentFrame . "/" . $this->frameCount
		);
	}
}
