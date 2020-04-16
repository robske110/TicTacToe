<?php

namespace robske_110\TTT;

use pocketmine\scheduler\Task;
use robske_110\TTT\Game\Arena;

class FreeArenaTask extends Task{
	/** @var Arena */
	private $arena;
	/** @var PlayerManager */
	private $playerManager;
	
	public function __construct(Arena $arena, PlayerManager $playerManager){
		$this->arena = $arena;
		$this->playerManager = $playerManager;
	}
	
	
	public function onRun(int $currentTick){
		$this->playerManager->useFreedArena($this->arena);
	}
}