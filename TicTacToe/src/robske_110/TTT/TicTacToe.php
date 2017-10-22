<?php

namespace robske_110\TTT;

use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\level\Position;

use robske_110\TTT\Game\GameManager;
use robske_110\TTT\Game\Arena;

class TicTacToe extends PluginBase{
	/** @var Config */
	private $db;
	
	/** @var EventListener */
	private $listener;
	/** @var GameManager */
	private $gameManager;
	/** @var PlayerManager */
	private $playerManager;
	
	/**
	 * You should check this against your version either with your own implementation
	 * or with @link{$this->isCompatible}
	 * (This only tracks changes to non @internal marked stuff)
	 * If C changes:
	 * C.x.x Breaking changes, disable your plugin with an error message or disable any PP API usage.
	 * x.C.x Feature additions, usually not breaking. (Use this if you require certain new features)
	 * x.x.C BugFixes on API related functions, not breaking.
	 */
	const API_VERSION = "0.1.0";

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->db = new Config($this->getDataFolder()."TTTdb.yml", Config::YAML, []); //TODO:betterDB
		
		$this->listener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($this->listener, $this);
		$this->playerManager = new PlayerManager($this);
		$this->gameManager = new GameManager($this);
		foreach($this->db->getAll() as $data){
			$this->gameManager->addArena(
				new Arena(
					new Position(
						$data[0][0], $data[0][1], $data[0][2],
						$this->getServer()->getLevelByName($data[0][3])
					),
					new Position(
						$data[1][0], $data[1][1], $data[1][2],
						$this->getServer()->getLevelByName($data[1][3])
					),
					$this
				)
			);
		}
	}
    
	/**
	 * For extension plugins to test if they are compatible with the version
	 * of PP installed.
	 *
	 * @param string $apiVersion The API version your plugin was last tested on.
	 *
	 * @return bool Indicates whether your plugin is compatible.
	 */
	public function isCompatible(string $apiVersion): bool{
		$extensionApiVersion = explode(".", $apiVersion);
		$myApiVersion = explode(".", self::API_VERSION);
		if($extensionApiVersion[0] !== $myApiVersion[0]){
			return false;
		}
		if($extensionApiVersion[1] > $myApiVersion[1]){
			return false;
		}
		return true;
	}
	
	public function saveArena(array $positions){
		$this->db->set(
			count($this->db->getAll()),
			[
				[$positions[0]->x, $positions[0]->y, $positions[0]->z, $positions[0]->level->getName()],
				[$positions[1]->x, $positions[1]->y, $positions[1]->z, $positions[1]->level->getName()]
			]
		);
		$this->db->save(true);
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "tictactoe arenacreate"){
			$sender->sendMessage("Touch the lower left and then the upper right block of the game board!");
			$this->listener->addArenaCreationSessiom($sender->getId());
			return true;
		}
		return false;
	}
	
	/**
	 * @return GameManager
	 */
	public function getGameManager(): GameManager{
		return $this->gameManager;
	}
	
	/**
	 * @return PlayerManager
	 */
	public function getPlayerManager(): PlayerManager{
		return $this->playerManager;
	}
}