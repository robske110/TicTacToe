<?php

namespace robske_110\TTT;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;

use robske_110\PlayerParticles\Game\Game;

class EventListener implements Listener{
	private $main;
	public $arenaCreationSessions;

	public function __construct(PlayerParticles $main){
		$this->main = $main;
	}
    
	public function onItemFrameBlockSet(PlayerInteractEvent $event){
		var_dump($event->getAction());
		var_dump($event->getBlock());
		return;
		if(($game = $this->main->getPlayerManager()->getGame($event->getPlayer()->getId())) instanceof Game){
			$game->onGameMove($event->getPlayer()->getId(), $itemFrame);
		}
	}
	
	public function onSignTap(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$block = $event->getBlock();
			$player = $event->getPlayer();
			$signTile = $player->getLevel()->getTile($block);
			if($signTile instanceof Sign){
				$sign = $signTile->getText();
				if($sign[0]=='[TTT]'){
					$this->main->getPlayerManager()->addPlayer($event->getPlayer()->getId());
				}
			}
		}	
	}
	
	public function onSignTap(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$player = $event->getPlayer();
			if(isset($this->arenaCreationSessions[$player->getId()])){
				$block = $event->getBlock();
				$this->arenaCreationSessions[$player->getId()][] = $block;
				if(count($this->arenaCreationSessions[$player->getId()] >= 2)){
					$this->main->getGameManager->addArena(
						new Arena(
							$this->arenaCreationSessions[$player->getId()][0],
							$this->arenaCreationSessions[$player->getId()][1]
						)
					);
					$this->main->saveArena($this->arenaCreationSessions[$player->getId()]);
					unset($this->arenaCreationSessions[$player->getId()]);
				}
			}
		}
	}
	
	public function onLeave(PlayerQuitEvent $event){
		$this->main->getPlayerManager()->removePlayer($event->getPlayer()->getId());
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!