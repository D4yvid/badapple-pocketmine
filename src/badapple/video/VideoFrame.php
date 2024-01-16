<?php

namespace badapple\video;

use RuntimeException;

class VideoFrame
{

	private $width;
	private $height;
	private $data;

	public function __construct(array $data)
	{
		if (empty($data)) {
			throw new RuntimeException("cannot create a empty video frame");
		}

		// Discard all keys, we don't need them
		$this->data = array_values($data);

		$this->width = count($this->data[0]);
		$this->height = count($this->data);
	}

	public function getWidth(): int
	{
		return $this->width;
	}

	public function getHeight(): int
	{
		return $this->height;
	}

	public function getData(): array
	{
		return $this->data;
	}

	public function getSliceCount(): int
	{
		return $this->width >> 4;
	}

}
