<?php

namespace badapple\video;

class FrameQueue
{
	/** @var int */
	private $processedFrames = 0;

	/** @var VideoFrame[] */
	private $frames = [];

	public function getProcessedFrames(): int
	{
		return $this->processedFrames;
	}

	public function hasFrame(): bool
	{
		return !empty($this->frames);
	}

	public function nextFrame(): VideoFrame
	{
		return array_shift($this->frames);
	}

	public function putFrame(VideoFrame $frame)
	{
		$this->frames[] = $frame;

		$this->processedFrames++;
	}

	public function clear()
	{
		$this->frames = [];
	}

	public function size()
	{
		return count($this->frames);
	}
}
