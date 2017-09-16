<?php

namespace robske_110\TTT;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;

use robske_110\TTT\Game\Game;
use robske_110\TTT\Game\Arena;

class EventListener implements Listener{
	private $main;
	public $arenaCreationSessions;

	public function __construct(TicTacToe $main){
		$this->main = $main;
	}
    
	public function onItemFrameBlockSet(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			if(($game = $this->main->getPlayerManager()->getGameForPlayer($event->getPlayer()->getId())) instanceof Game){
				$game->onGameMove($event->getPlayer()->getId(), $event->getBlock());
			}
		}
	}
	
	public function onSignTap(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$block = $event->getBlock();
			$player = $event->getPlayer();
			$signTile = $player->getLevel()->getTile($block);
			if($signTile instanceof Sign){
				$sign = $signTile->getText();
				if($sign[0] == '[TTT]'){
					$this->main->getPlayerManager()->addPlayer($event->getPlayer()->getId());
				}
			}
		}	
	}
	
	public function onBlockTap(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_BLOCK){
			$player = $event->getPlayer();
			if(isset($this->arenaCreationSessions[$player->getId()])){
				$block = $event->getBlock();
				$this->arenaCreationSessions[$player->getId()][] = $block;
				if(count($this->arenaCreationSessions[$player->getId()]) >= 2){
					$this->main->getGameManager()->addArena(
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