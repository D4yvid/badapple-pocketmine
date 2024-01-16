<?php

namespace badapple\video;

use pocketmine\math\Vector2;
use pocketmine\Player;
use pocketmine\scheduler\TaskHandler;
use RuntimeException;

class VideoPlayer
{

	const FRAME_PROCESS_THRESHOLD = 8;

	/** @var FrameQueue */
	private $frameQueue;

	/** @var PlaybackTask */
	private $playbackTask;

	/** @var Player */
	private $target;

	/** @var array */
	private $frames;

	/** @var int */
	private $frameCount;

	/** @var Vector2 */
	private $baseChunk;

	/** @var int */
	private $id;

	/** @var bool */
	private $running;

	/** @var TaskHandler */
	private $taskHandle;

	public function __construct(int $id, Player $player, Vector2 $baseChunk, array $frames)
	{
		$this->id = $id;

		$this->target = $player;
		$this->frames = $frames;
		$this->frameCount = count($frames);
		$this->running = false;
		$this->taskHandle = null;

		$this->frameQueue = new FrameQueue;
		$this->playbackTask = new PlaybackTask($this);

		$this->baseChunk = $baseChunk;
	}

	public function play()
	{
		$this->running = true;

		$this->taskHandle = $this
			->getTarget()
			->getServer()
			->getScheduler()
			->scheduleRepeatingTask($this->playbackTask, 4);
	}

	public function stop()
	{
		if (!$this->running)
			throw new RuntimeException("the video playback is already stopped");

		$this->getTarget()
			->getServer()
			->getScheduler()
			->cancelTask($this->taskHandle->getTaskId());

		$this->taskHandle = null;
		$this->running = false;

		$this->frameQueue->clear();
	}

	public function getTarget(): Player
	{
		return $this->target;
	}

	public function getPlaybackTask(): PlaybackTask
	{
		return $this->playbackTask;
	}

	public function getFrameQueue(): FrameQueue
	{
		return $this->frameQueue;
	}

	public function getFrames(): array
	{
		return $this->frames;
	}

	public function getBaseChunk(): Vector2
	{
		return $this->baseChunk;
	}

	public function getFrameCount(): int
	{
		return $this->frameCount;
	}

	public function getId(): int
	{
		return $this->id;
	}
}
