<?php

namespace robske_110\TTT\Game;

use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class TeleportTask extends Task{
	/** @var Player */
	private $player;
	/** @var Position */
	private $pos;
	/** @var GameManager */
	private $gameManager;
	
	public function __construct(Player $player, Position $pos, GameManager $gameManager){
		$this->player = $player;
		$this->pos = $pos;
		$this->gameManager = $gameManager;
	}
	
	
	public function onRun(int $currentTick){
		if(!$this->player->isClosed()){
			$this->player->teleport($this->pos);
		}
		$this->gameManager->abortPlayerTeleport($this->player->getId());
	}
}