<?php

namespace badapple\video;

use badapple\ChunkGenerator;
use badapple\video\FrameProcessorTask;
use badapple\video\VideoPlayer;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class PlaybackTask extends Task
{

	/** @var VideoPlayer */
	private $player;

	/** @var bool */
	private $processing;

	public function __construct(VideoPlayer $player)
	{
		$this->player = $player;
	}

	public function onRun($currentTick)
	{
		$server = Server::getInstance();
		$queue = $this->player->getFrameQueue();

		if ($queue->getProcessedFrames() >= $this->player->getFrameCount()) {
			$this->player->stop();

			return;
		}

		if ($queue->size() < VideoPlayer::FRAME_PROCESS_THRESHOLD && !$this->processing) {
			$framesToProcess = array_slice(
				$this->player->getFrames(),
				$queue->getProcessedFrames(),
				VideoPlayer::FRAME_PROCESS_THRESHOLD * 2
			);

			$task = new FrameProcessorTask($framesToProcess, $this->player->getId());

			$server->getLogger()->debug(
				"[FrameProcessorTask] starting new processor task; processing " .
					VideoPlayer::FRAME_PROCESS_THRESHOLD .
					" frames"
			);

			$server->getScheduler()->scheduleAsyncTask($task);

			$this->processing = true;
		}

		if ($queue->hasFrame()) {
			$server->getLogger()->debug(
				"There is " . $queue->size() . " frames available\nProcessing state: " .
				($this->processing ? "yes" : "no"));

			$frame = $queue->nextFrame();

			$this->render($frame);
		}
	}

	public function setProcessingState(bool $value)
	{
		$this->processing = $value;
	}

	private function render(VideoFrame $frame)
	{
		$chunkPosition = $this->player->getBaseChunk();
		$packets = [];

		$buffers = ChunkGenerator::generateChunksFromVideoFrame($frame);

		foreach ($buffers as $sliceIndex => $buffer) {
			$cx = $chunkPosition->x - (($frame->getSliceCount() / 2) - $sliceIndex);
			$cz = $chunkPosition->y - 2;

			$packets[] = ChunkGenerator::generateChunkPacket($cx, $cz, $buffer);
		}

		Server::getInstance()->batchPackets([$this->player->getTarget()], $packets);
	}
}
