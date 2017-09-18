<?php

namespace robske_110\TTT;

use pocketmine\Player;

use robske_110\TTT\Game\Game;
use robske_110\TTT\Game\Arena;

class PlayerManager{
	/** @var TicTacToe  */
	private $main;

	/** @var array */
	private $players;
	/** @var null|Game */
	private $game;
	
	public function __construct(TicTacToe $main){
		$this->main = $main;
	}
	
	private function getPlayerById(int $playerID): ?Player{
		foreach($this->main->getServer()->getOnlinePlayers() as $player){
			if($playerID === $player->getId()){
				return $player;
			}
		}
		return null;
	}
	
	public function getGameForPlayer(int $playerID): ?Game{
		if(!isset($this->players[$playerID])){
			return null;
		}
		return $this->players[$playerID];
	}
	
	/**
	 * @param int $playerID
	 */
	public function addPlayer(int $playerID){
		if(isset($this->players[$playerID])){
			return;
		}
		if($this->game === null){
			if(($arena = $this->main->getGameManager()->getFreeArena()) !== null){
				$this->createGame($arena, $playerID);
			}else{
				$this->players[$playerID] = null;
			}
			$this->getPlayerById($playerID)->sendMessage("You have been successfully added to the queue!");
		}else{
			$this->startGame($playerID);
		}
	}
	
	private function createGame(Arena $arena, int $firstPlayerID){
		$this->game = new Game($arena);
		$this->players[$firstPlayerID] = $this->game;
		$this->game->addPlayer($this->getPlayerById($firstPlayerID));
	}
	
	private function startGame(int $secondPlayerID){
		$this->players[$secondPlayerID] = $this->game;
		$this->game->addPlayer($this->getPlayerById($secondPlayerID));
		$this->main->getGameManager()->startGame($this->game);
		$this->game = null;
	}
	
	public function onGameEnd(Game $game){
		foreach($game->getPlayers() as $playerID => $playerData){
			$this->removePlayer($playerID, false);
		}
		foreach($this->players as $playerID => $player){
			if($player === null){
				if($this->game === null){
					$this->createGame($game->getArena(), $playerID);
				}else{
					$thiss->startGame($playerID);
				}
			}
		}
	}
	
	/**
	 * @param int  $playerID
	 * @param bool $endGame
	 *
	 * @return bool
	 */
	public function removePlayer(int $playerID, bool $endGame = true): bool{
		if(isset($this->players[$playerID])){
			if($endGame){
				$this->players[$playerID]->endInverted($playerID);
			}
			unset($this->players[$playerID]);
			return true;
		}
		return false;
	}
}