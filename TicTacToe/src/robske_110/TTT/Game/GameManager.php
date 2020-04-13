<?php

namespace robske_110\TTT\Game;

use pocketmine\level\Position;
use robske_110\TTT\TicTacToe;
use pocketmine\Player;

class GameManager{
	/** @var TicTacToe  */
	private $main;
	/** @var Game[]  */
	private $games = [];
	/** @var Arena[]  */
	private $arenas = [];
	/** @var array [$playerID => [$pos, $taskHandler]] */
	private $playerTeleportJobs = [];
	
	/** @var Position|null */
	private $onGameEndPos = null;
	/** @var int|null */
	private $onGameEndTeleportDelay = null;
	
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
		if($game->getArena()->getArea()[0]->getLevel() === null){
			$this->main->getLogger()->emergency("A level for an Arena got unloaded at a very bad time! TicTacToe will be disabled!");
			$this->main->getServer()->getPluginManager()->disablePlugin($this->main);
			return;
		}
		$this->games[] = $game;
		foreach($game->getPlayers() as $playerData){
			$playerData[0]->teleport($game->getArena()->getArea()[0]);
		}
		$game->start();
	}
	
	/**
	 * @param Position|null $pos Sets the onGameEnd Position. If null is supplied, will not teleport after a game ends.
	 */
	public function setOnGameEndPosition(?Position $pos){
		$this->onGameEndPos = $pos;
	}
	
	/**
	 * @param int|null $ticks Sets the onGameEnd Position. If null is supplied, will not teleport after a game ends.
	 */
	public function setOnGameEndTeleportDelay(?int $ticks){
		$this->onGameEndTeleportDelay = $ticks;
	}
	
	/**
	 * @param Player $player The player for which the teleport should be executed immediately.
	 */
	public function clearPlayerTeleport(Player $player){
		if(isset($this->playerTeleportJobs[$player->getId()])){
			$player->teleport($this->playerTeleportJobs[$player->getId()]);
			$this->abortPlayerTeleport($player->getId());
		}
	}
	
	/**
	 * @param int $playerId The player for which the playerTeleport should be aborted
	 */
	public function abortPlayerTeleport(int $playerId){
		if(isset($this->playerTeleportJobs[$playerId])){
			$this->playerTeleportJobs[$playerId]->cancel();
			unset($this->playerTeleportJobs[$playerId]);
		}
	}
	
	/**
	 * @internal
	 *
	 * @param Game $game
	 */
	public function endGame(Game $game){
		if($this->onGameEndPos !== null){
			if($this->onGameEndTeleportDelay == 0){
				foreach($game->getPlayers() as $playerData){
					$playerData[0]->teleport($this->onGameEndPos);
				}
			}else{
				foreach($game->getPlayers() as $playerData){
					$taskHandler = $this->main->getScheduler()->scheduleDelayedTask(
						new TeleportTask($playerData[0], $this->onGameEndPos, $this),
						$this->onGameEndTeleportDelay
					);
					$this->playerTeleportJobs[$playerData[0]->getId()] = [$this->onGameEndPos, $taskHandler];
				}
			}
		}
	}
}
//Theory is when you know something, but it doesn't work. Practice is when something works, but you don't know why. Programmers combine theory and practice: Nothing works and they don't know why!