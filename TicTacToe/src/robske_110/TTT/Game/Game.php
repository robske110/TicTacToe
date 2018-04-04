<?php

namespace robske_110\TTT\Game;

use pocketmine\Player;
use pocketmine\math\Vector3;

use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\block\BlockIds;

use pocketmine\item\Item;
use pocketmine\block\Block;

class Game{
	/** @var Arena */
	private $arena;
	/** @var bool */
	private $active = false;
	/** @var Player[][] */
	private $players;
	/** @var array */
	private $map = [
		0 => ["","",""],
		1 => ["","",""],
		2 => ["","",""]
	];
	
	public function __construct(Arena $arena){
		$this->arena = $arena;
		if(!$this->arena->occupy($this)){
			throw new \InvalidStateException("A Game has been attempted to be constructed with an Arena which is either occupied or (more likely) its level got unloaded!");
		}
	}
	
	/**
	 * @internal
	 * @param int $playerID
	 * @param Block $itemFrame
	 * @param Item $item
	 *
	 * @return bool
	 */
	public function onGameMove(int $playerID, Block $itemFrame, Item $item): bool{
		if(!$this->active){
			return true;
		}
		if(($pos = $this->getPositionOnMap($itemFrame)) !== null){
			#var_dump($pos);
			if($this->players[$playerID][1]){
				if($this->map[$pos[0]][$pos[1]] === ""){
					if($item->getId() === BlockIds::AIR){
						return false;
					}
					$this->map[$pos[0]][$pos[1]] = $this->players[$playerID][2];
					#var_dump($this->map);
					$this->checkForWin();
					if(!$this->active){
						return true;
					}
					$this->players[$playerID][1] = false;
					$opID = $this->getOpponent($playerID);
					$this->players[$opID][1] = true;
					$this->players[$opID][0]->sendMessage("It's your turn now!");
					return true;
				}else{
					$this->players[$playerID][0]->sendMessage("That field is already full!");
					return false;
				}
			}else{
				$this->players[$playerID][0]->sendMessage("It's not your turn!");
				return false;
			}
		}
		return true;
	}
	
	/**
	 * @internal
	 * @param Vector3 $pos
	 *
	 * @return array|null
	 */
	public function getPositionOnMap(Vector3 $pos): ?array{
		$mapPos = $this->arena->getArea();
		$posDownLeft = $mapPos[0];
		$posUpperRight = $mapPos[1];
		$verticalPos = $pos->y - $posDownLeft->y;
		if($verticalPos > 2 || $verticalPos < 0){
			return null;
		}
		$horizontalPos = 3;
		if($posDownLeft->x === $posUpperRight->x){
			if($posDownLeft->x !== $pos->x){
				return null;
			}
			if($posDownLeft->z > $posUpperRight->z){
				$horizontalPos = $posDownLeft->z - $pos->z;
			}elseif($posDownLeft->z < $posUpperRight->z){
				$horizontalPos = $pos->z - $posDownLeft->z;
			}
		}elseif($posDownLeft->z === $posUpperRight->z){
			if($posDownLeft->z !== $pos->z){
				return null;
			} 
			if($posDownLeft->x > $posUpperRight->x){
				$horizontalPos = $posDownLeft->x - $pos->x;
			}elseif($posDownLeft->x < $posUpperRight->x){
				$horizontalPos = $pos->x - $posDownLeft->x;
			}
		}else{
			return null;
		}
		if($horizontalPos > 2 || $horizontalPos < 0){
			return null;
		}
		return [$verticalPos, $horizontalPos];
	}
	
	private function checkForWin(){
		foreach($this->map as $content){
			if($content[0] !== "" && $content[0] === $content[1] && $content[0] === $content[2]){ //horizontal row
				$this->end($this->getPlayerWithSymbol($content[0]));
			}
		}
		$isFull = true;
		foreach($this->map as $content){
			if(!($content[0] !== "" && $content[1] !== "" && $content[2] !== "")){
				$isFull = false;
				break;
			}
		}
		if($isFull){
			$this->end();
		}
		for($i = 0; $i < 3; $i++){
			if($this->map[0][$i] !== "" && $this->map[0][$i] === $this->map[1][$i] && $this->map[0][$i] === $this->map[2][$i]){ //vertical row
				$this->end($this->getPlayerWithSymbol($this->map[0][$i]));
			}
		}
		if($this->map[0][0] !== "" && $this->map[0][0] === $this->map[1][1] && $this->map[0][0] === $this->map[2][2]){ #/
			$this->end($this->getPlayerWithSymbol($this->map[0][0]));
		}
		if($this->map[2][0] !== "" && $this->map[2][0] === $this->map[1][1] && $this->map[2][0] === $this->map[0][2]){ #\
			$this->end($this->getPlayerWithSymbol($this->map[0][0]));
		}
	}
	
	/**
	 * @param string $symbol Gets the playerID for the player with the specified symbol ('X' / 'O')
	 *
	 * @return int PlayerID
	 */
	public function getPlayerWithSymbol(string $symbol): int{
		foreach($this->players as $playerID => $playerData){
			if($playerData[2] === $symbol){
				return $playerID;
			}
		}
		return -1;
	}
	
	/**
	 * @param int $playerID Gets the opponent (basically the other player) in this game for the player with this playerID.
 	 *
	 * @return int PlayerID
	 */
	public function getOpponent(int $playerID): int{
		foreach($this->players as $oppID => $playerData){
			if($oppID !== $playerID){
				return $oppID;
			}
		}
		return -1;
	}

    /**
     * @return Arena
     */
	public function getArena(): Arena{
		return $this->arena;
	}
	
	/**
	 * @param Player $player
	 */
	public function addPlayer(Player $player){
		$this->players[$player->getId()] = [$player, false];
	}
	
	/**
	 * @return array [playerID => [Player, hasTurn, symbol ('X' or 'O')]]
	 */
	public function getPlayers(): array{
		return $this->players;
	}
	
    /**
     * @return bool isActive
     */
	public function isActive(): bool{
		return $this->active;
	}
	
	/**
	 * @return bool
	 */
	public function start(): bool{
		if(count($this->players) !== 2){
			return false;
		}
		$players = $this->players;
		shuffle($players);
		$p1ID = $players[0][0]->getId();
		$player1 = $this->players[$p1ID];
		$p2ID = $this->getOpponent($p1ID);
		$player2 = $this->players[$p2ID];
		
		$this->players[$p1ID][1] = true;
		$this->players[$p1ID][2] = "X";
		$this->players[$p2ID][1] = false;
		$this->players[$p2ID][2] = "O";
		
		$player1[0]->sendMessage("Your start!");
		$inv = $player1[0]->getInventory();
		$inv->clearAll();
		$inv->addItem(ItemFactory::get(ItemIds::GOLD_INGOT, 0, 5));
		$inv->setItem(0, ItemFactory::get(ItemIds::GOLD_INGOT, 0, 5));
		
		$player2[0]->sendMessage("Your opponent starts!");
		$inv = $player2[0]->getInventory();
		$inv->clearAll();
		$inv->addItem(ItemFactory::get(ItemIds::IRON_INGOT, 0, 4));
		$inv->setItem(0, ItemFactory::get(ItemIds::IRON_INGOT, 0, 4));
		
		$this->active = true;
		return true;
	}
	
	/**
	 * @param int $looserPlayerID The player who lost.
	 *
	 * @return bool If the player could be found, the opposite player could be found and the game could be ended.
	 */
	public function endInverted(int $looserPlayerID): bool{
		return $this->end($this->getOpponent($looserPlayerID));
	}
	
	/**
	 * @param int|null $winnerPlayerID
	 *
	 * @return bool If the player could be found, the game was active and the game could be ended.
	 */
	public function end(?int $winnerPlayerID = null): bool{
		if(!$this->active){
			return false;
		}
		foreach($this->players as $playerData){
			$playerData[0]->getInventory()->clearAll();
		}
		if($winnerPlayerID === null){ //draw
			foreach($this->players as $playerData){
				$playerData[0]->sendMessage("Draw!");
			}
		}else{
			$this->players[$winnerPlayerID][0]->sendMessage("You won!");
			$this->players[$this->getOpponent($winnerPlayerID)][0]->sendMessage("You lost!");
		}
		$this->active = false;
		$this->arena->deOccupy($this);
		$this->arena->getMain()->getPlayerManager()->onGameEnd($this);
		$this->arena = null;
		$this->players = null;
		return true;
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!