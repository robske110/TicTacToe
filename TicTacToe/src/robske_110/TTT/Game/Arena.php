<?php

namespace robske_110\TTT\Game;

use pocketmine\level\Position;

class Arena{
	/** @var Location */
	private $pos1;
	/** @var Location */
	private $pos2;
	/** @var Game */
	private $game;
	/** @var bool */
	private $occupied = false;
	
	public function __construct(Position $pos1, Position $pos2){
		$this->pos1 = $pos1;
		$this->pos2 = $pos2;
	}

    /**
     * @return Game
     */
	public function getGame(): Game{
		return $this->game;
	}

    /**
     * @return array
     */
	public function getArea(): array{
		return [$this->pos1, $this->pos2];
	}
	
	public function occupy(Game $game){
		$this->game = $game;
		$this->occupied = true;
	}
	
	public function deOccupy(Game $game){
		if($this->game === $game){
			$this->occupied = false;
		}
	}

    /**
     * @return bool isOccupied
     */
	public function isOccupied(): bool{
		return $this->occupied;
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!