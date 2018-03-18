<?php

namespace robske_110\TTT\Game;

use pocketmine\level\Position;
use pocketmine\math\Vector3;

use pocketmine\tile\ItemFrame;
use robske_110\TTT\TicTacToe;

class Arena{
	/** @var Position */
	private $pos1;
	/** @var Position */
	private $pos2;
	/** @var TicTacToe */
	private $main;
	
	/** @var null|Game */
	private $game;
	/** @var bool */
	private $occupied = false;
	
	public function __construct(Position $pos1, Position $pos2, TicTacToe $main){
		$this->pos1 = $pos1;
		$this->pos2 = $pos2;
		$this->main = $main;
	}

    /**
     * @return Game
     */
	public function getGame(): ?Game{
		return $this->game;
	}

    /**
     * @return Position[]
     */
	public function getArea(): array{
		return [$this->pos1, $this->pos2];
	}
	
	public function reset(){
		if($this->pos1->x === $this->pos2->x){
			$hStart = min($this->pos1->z, $this->pos2->z);
			$hStop = max($this->pos1->z, $this->pos2->z);
			$z = null;
			$x = $this->pos1->x;
		}elseif($this->pos1->z === $this->pos2->z){
			$hStart = min($this->pos1->x, $this->pos2->x);
			$hStop = max($this->pos1->x, $this->pos2->x);
			$x = null;
			$z = $this->pos1->z;
		}else{
			$this->occupied = true; //Prevent any further usages of this arena
			$this->main->getLogger()->critical("An Arena got permanently disabled due to: ARENA_NOT_2D");
			$this->game = null;
			return;
		}
		$level = $this->pos1->getLevel();
		$yStart = min($this->pos1->y, $this->pos2->y);
		$yStop = max($this->pos1->y, $this->pos2->y);
		for($hi = $hStart; $hi <= $hStop; $hi++){
			for($yi = $yStart; $yi <= $yStop; $yi++){
				$vec3 = null;
				if($x === null){
					$vec3 = new Vector3($hi, $yi, $z);
				}
				if($z === null){
					$vec3 = new Vector3($x, $yi, $hi);
				}
				if($vec3 === null){
					continue;
				}
				$itemFrame = $level->getTile($vec3);
				if($itemFrame instanceof ItemFrame){
					$itemFrame->setItem();
				}
			}
		}
	}
	
	/**
	 * Occupies/Associates an active Game with this arena.
	 * @param Game $game
	 *
	 * @return bool
	 */
	public function occupy(Game $game): bool{
		if($this->occupied){
			return false;
		}
		if($this->pos1->getLevel() === null){
			$this->occupied = true;
			$this->main->getLogger()->critical("An Arena got permanently disabled due to: ARENA_LEVEL_NOT_LOADED");
			return false;
		}
		$this->game = $game;
		$this->occupied = true;
		$this->reset();
		return true;
	}
	
	/**
	 * De-Occupies/Removes an ended Game from this arena.
	 * @param Game $game
	 */
	public function deOccupy(Game $game){
		if($this->game === $game){
			$this->occupied = false;
			$this->game = null;
		}
	}

    /**
     * @return TicTacToe
     */
	public function getMain(): TicTacToe{
		return $this->main;
	}

    /**
     * @return bool isOccupied
     */
	public function isOccupied(): bool{
		return $this->occupied;
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!