<?php

namespace robske_110\TTT\Game;

use pocketmine\level\Position;
use pocketmine\Player;

use pocketmine\item\Item;
use pocketmine\item\ItemIds;

class Game{
	/** @var Arena */
	private $arena;
	/** @var bool */
	private $active = false;
	/** @var array */
	private $players;
	/** @var array */
	private $map = [
		0 => ["","",""],
		1 => ["","",""],
		2 => ["","",""]
	];
	
	public function __construct(Arena $arena){
		$this->arena = $arena;
		$this->arena->occupy($this);
	}

	public function onGameMove(int $playerID, $itemFrame){
		if($this->players[$playerID][1]){
			if(($pos = $this->getPositionOnMap($itemFrame)) !== null){ //check if valid turn
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

	public function getPositionOnMap(Position $pos): ?array{
		$mapPos = $this->arena->getArea();
		$posDownLeft = $mapPos[0];
		$posUpperRight = $mapPos[1];
		$verticalPos = $pos->y - $posDownLeft->y;
		if($verticalPos > 2 || $verticalPos < 0){
			return null;
		}
		if($posDownLeft->x === $posUpperRight->x){
			$horizontalPos = $posDownLeft->x - $pos->x;
		}elseif($posDownLeft->z === $posUpperRight->z){
			$horizontalPos = $posDownLeft->z - $pos->z;
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
		$isFull = true;
		foreach($this->map as $content){
			if($content[0] !== "" || $content[1] !== "" || $content[2] !== ""){
				$isFull = false;
				break;
			}
		}
		if($isFull){
			$this->end();
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
		foreach($this->players as $playID => $playerData){
			if($playID !== $playerID){
				return $playID;
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
		$players = $this->players;
		shuffle($players);
		var_dump($players);
		$firstPlayerId = $players[0][0]->getId();
		$this->players[$firstPlayerId][1] = true;
		$this->players[$firstPlayerId][2] = "X";
		$this->players[$firstPlayerId][0]->sendMessage("You start!");
		$this->players[$firstPlayerId][0]->getInventory()->addItem(new Item(ItemIds::IRON_INGOT));
		$this->players[$this->getOpponent($firstPlayerId)][2] = "O";
		$this->players[$this->getOpponent($firstPlayerId)][0]->sendMessage("Your opponent starts!");
		$this->players[$this->getOpponent($firstPlayerId)][0]->getInventory()->addItem(new Item(ItemIds::GOLD_INGOT));
		$this->active = true;
		return true;
	}
	
	public function end(?int $winnerPlayerID = null): bool{
		if(!$this->active){
			return false;
		}
		if($winnerPlayerID === null){ //draw
			foreach($this->players as $playerData){
				$playerData[0]->sendMessage("Draw!");
			}
		}else{
			$this->players[$winnerPlayerID][0]->sendMessage("You win!");
			$this->players[$this->getOpponnent($winnerPlayerID)][0]->sendMessage("You lost!");
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