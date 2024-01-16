<?php

namespace badapple\video;

use badapple\Main;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class FrameProcessorTask extends AsyncTask
{

	private $framesToProcess;
	private $videoPlayerId;

	public function __construct(array $framesToProcess, int $videoPlayerId)
	{
		$this->framesToProcess = $framesToProcess;
		$this->videoPlayerId = $videoPlayerId;
	}

	public function onRun()
	{
		$result = [];

		foreach ($this->framesToProcess as $frame) {
			$result[] = $this->process($frame);
		}

		$this->setResult([true, $result]);
	}

	private function process(string $frame)
	{
		$lines = explode("\n", $frame);

		$height = count($lines);
		$width = strlen($lines[0]);

		$result = array_fill(0, $height, array_fill(0, $width, ItemIds::WOOL));

		for ($x = 0; $x < $width; $x++) {
			for ($y = 0; $y < $height; $y++) {
				$value = $lines[$y][$x];

				if ($value == " ")
					$result[$x][$y] = ItemIds::OBSIDIAN;
			}
		}

		return new VideoFrame($result);
	}

	public function onCompletion(Server $server)
	{
		$result = $this->getResult();

		$plugin = Main::get();
		$players = $plugin->getVideoPlayers();

		if (!$result[0]) {
			$plugin->getLogger()->warning("[FrameProcessorTask] an error occurred while processing: " . $result[1]);
			return;
		}

		if (!isset($players[$this->videoPlayerId])) {
			$plugin->getLogger()->warning("[FrameProcessorTask] the video player associated with this task doesn't exist");
			return;
		}

		$player = $players[$this->videoPlayerId];

		/** @var VideoFrame $frame */
		foreach ($result[1] as $frame) {
			$player->getFrameQueue()->putFrame($frame);
		}

		$player->getPlaybackTask()->setProcessingState(false);
	}

}
