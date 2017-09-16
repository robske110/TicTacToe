<?php

namespace robske_110\TTT\Game;

use pocketmine\utils\Vector3;

class Game{
	/** @var Arena */
	private $arena;
	/** @var bool */
	private $active = false;
	/** @var array */
	private $players;
	/** @var array */
	private $map;
	
	public function __construct(Arena $arena){
		$this->arena = $arena;
		$this->arena->occupy($this);
	}

	public function onGameMove(int $playerId, $itemFrame){
		if($this->players[$playerID][1]){
			if(($pos = $this->getPositionOnMap($itemFrame) !== null){ //check if valid turn
				if($this->map[$pos[0]][$pos[1]] === ""){
					$this->map[$pos[0]][$pos[1]] = $this->players[$playerID][2];
					$this->checkForWin();
				}else{
					$this->players[$playerID][0]->sendMessage("That field is already full!");
				}
			}
		}else{
			$this->players[$playerID][0]->sendMessage("It's not your turn!");
		}
	}

	public function getPositionOnMap(Vector3 $vec3): ?array{
		$mapPos = $this->arena->getPositon();
		$posDownLeft = $mapPos[0];
		$posUpperRight = $mapPos[1];
		$verticalPos = $vec3 - $posDownLeft->y;
		if($verticalPos > 2 || $verticalPos < 0){
			return null;
		}
		if($posDownLeft->x === $posUpperRight->x){
			$horizontalPos = $posDownLeft->x - $vec3->x;
		}elseif($posDownLeft->z === $posUpperRight->z){
			$horizontalPos = $posDownLeft->z - $vec3->z;
		}else{
			return null;
		}
		$horizontalPos = abs($horizontalPos);
		if($horizontalPos > 2){
			return null;
		}
		return [$verticalPos, $horizontalPos];
	}

	public function checkForWin(){
		foreach($this->map as $content){
			if($content[0] == $content[1] && $content[0] == $content[2]){
				$this->end($this->getPlayerWithSymbol($content[0]));
			}
		}
		for($i = 0; $i < 3; $i++){
			if($this->map[0][$i] == $this->map[1][1] && $this->map[0][$i] == $this->map[2][$i]){
				$this->end($this->getPlayerWithSymbol($this->map[0][$i]));
			}
		}
		if($this->map[0][0] == $this->map[1][1] && $this->map[0][0] == $this->map[2][2]){ #/
			$this->end($this->getPlayerWithSymbol($this->map[0][$i]));
		}
		if($this->map[2][0] == $this->map[1][1] && $this->map[2][0] == $this->map[0][2]){ #\
			$this->end($this->getPlayerWithSymbol($this->map[0][$i]));
		}
	}

	public function getPlayerWithSymbol(string $symbol): int{
		foreach($this->players as $playerID => $playerData){
			if($playerData[2] === $symbol){
				return $playerID;
			}
		}
		return -1;
	}

    /**
     * @return Arena
     */
	public function getArena(){
		return $this->arena;
	}
	
	public function endInverted(int $looserPlayerID): bool{
		$this->end($this->getOpponent($playerID));
	}
	
	public function getOpponent(int $playerID): int{
		foreach($this->players as $playerID => $playerData){
			if($playerID !== $looserPlayerID){
				return $playerID;
			}
		}
		return -1;
	}
	
	public function addPlayer(Player $player){
		$this->players[$player->getId()] = [$player, false];
	}
	
	public function getPlayers(){
		return $this->players;
	}
	
	public function start(): bool{
		if(count($this->players) !== 2){
			return false;
		}
		$players = shuffle($this->players);
		$firstPlayerId = $players[0][0]->getId();
		$this->players[$firstPlayerId][1] = true;
		$this->players[$firstPlayerId][2] = "X";
		$this->players[$firstPlayerId][0]->sendMessage("You start!");
		$this->players[$this->getOpponent($firstPlayerId)][2] = "O";
		$this->players[$this->getOpponent($firstPlayerId)][0]->sendMessage("Your opponent starts!");
		$this->active = true;
		return true;
	}
	
	public function end(?int $winnerPlayerID): bool{
		if(!$this->active){
			return false;
		}
		if($winnerPlayerID === null){ //draw
			
		}else{
			$this->players[]
		}
		$this->active = false;
		$this->arena->deOccupy();
		$this->arena = null;
		return true;
	}

    /**
     * @return bool isActive
     */
	public function isActive(): bool{
		return $this->active;
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!