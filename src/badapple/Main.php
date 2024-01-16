<?php

namespace badapple;

use badapple\video\VideoPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class Main extends PluginBase
{
	private static $instance;

	/** @var array<int,VideoPlayer> */
	private $videoPlayers = [];

	private $frames = [];

	public function __construct()
	{
		self::$instance = $this;
	}

	public static function get(): self
	{
		return self::$instance;
	}

	/** @return array<int,VideoPlayer> */
	public function getVideoPlayers(): array
	{
		return $this->videoPlayers;
	}

	public function getNextVideoPlayerId(): int
	{
		return count($this->videoPlayers);
	}

	public function putVideoPlayer(int $id, VideoPlayer $player)
	{
		$this->videoPlayers[$id] = $player;
	}

	public function getFrames()
	{
		return $this->frames;
	}

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
			->registerEvents(new EventListener, $this);
	}
}
