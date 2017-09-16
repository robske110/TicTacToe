<?php

namespace robske_110\TTT;

use robske_110\TTT\Game\Game;

class PlayerManager{
	/** @var TicTacToe  */
	private $main;
	/** @var array */
	private $playerIndex;
	/** @var array */
	private $players;
	
	
	public function __construct(TicTacToe $main){
		$this->main = $main;
	}
	
	public function getPlayerById(int $playerId): Player{
		foreach($this->main->getServer()->getOnlinePlayers() as $player){
			if($playerId === $player->getId()){
				return $player;
			}
		}
	}
	
	public function getGameForPlayer(int $playerId): ?Game{
		return $this->players[$playerID];
	}
	
	/**
	 * @param int $playerID
	 */
	public function addPlayer(int $playerID){
		if($this->game === null){
			if(($arena = $this->main->getGameManager()->getFreeArena()) !== null){
				$this->players[] = new Game($arena);
				$this->playerIndex[$playerID] = count($this->players[]) - 1;
				$this->players[$this->playerIndex[$playerID]]->addPlayer($this->getPlayerById($playerID));
				$this->game = $this->players[$this->playerIndex[$playerID]];
			}else{
				$this->players[] = null;
				$this->playerIndex[$playerID] = count($this->players[]) - 1;
			}
		}else{
			$this->players[] = $this->game;
			$this->playerIndex[$playerID] = count($this->players[]) - 1;
			$this->players[$this->playerIndex[$playerID]]->addPlayer($this->getPlayerById($playerID));
			$this->main->getGameManager()->startGame($this->game);
			$this->game = null;
		}
		
	}
	
	public function onGameEnd(Arena $freedArena){
		/*
		if(!empty($this->players)){
			foreach($this->players)aaaa
		}*/
	}
	
	/**
	 * @param int $playerID
	 *
	 * @return bool
	 */
	public function removePlayer(int $playerID): bool{
		if(isset($this->playerIndex[$playerID])){
			$this->players[$this->playerIndex[$playerID]]->endInverted($playerID);
			unset($this->players[$playerID]);
			unset($this->playerIndex[$playerID]);
			return true;
		}
		return false;
	}
}