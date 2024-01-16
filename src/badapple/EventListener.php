<?php

namespace badapple;

use badapple\video\VideoPlayer;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector2;

class EventListener implements Listener
{

	public function interactionHandler(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$item = $player->getItemInHand();

		if ($item->getId() != $item::BONE) {
			return;
		}

		$event->setCancelled(true);

		$chunk = new Vector2($block->x >> 4, $block->z >> 4);
		$plugin = Main::get();

		$player = new VideoPlayer(
			$plugin->getNextVideoPlayerId(),
			$player,
			$chunk,
			$plugin->getFrames());

		$plugin->putVideoPlayer($player->getId(), $player);

		$player->play();
	}
}
