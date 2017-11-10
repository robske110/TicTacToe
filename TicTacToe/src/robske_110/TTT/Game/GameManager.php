<?php

namespace robske_110\TTT\Game;

use robske_110\TTT\TicTacToe;

class GameManager{
	/** @var TicTacToe  */
	private $main;
	/** @var Game[]  */
	private $games = [];
	/** @var Arena[]  */
	private $arenas = [];
	
	public function __construct(TicTacToe $main){
		$this->main = $main;
	}
	
	/**
	 * Adds an Arena.
	 *
	 * @param Arena $arena
	 */
	public function addArena(Arena $arena){
		$this->arenas[] = $arena;
	}
	
	/**
	 * Gets a Arena, which is available for a new Game.
	 *
	 * @return null|Arena
	 */
	public function getFreeArena(): ?Arena{
		foreach($this->arenas as $arena){
			if(!$arena->isOccupied()){
				return $arena;
			}
		}
		return null;
	}
	
	/**
	 * @param Game $game
	 */
	public function startGame(Game $game){
		$this->games[] = $game;
		foreach($game->getPlayers() as $playerId => $playerData){
			$playerData[0]->teleport($game->getArena()->getArea()[0]);
		}
		$game->start();
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!