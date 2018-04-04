<?php

namespace robske_110\TTT;

use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\Config;
use pocketmine\level\Position;

use robske_110\TTT\Game\GameManager;
use robske_110\TTT\Game\Arena;

class TicTacToe extends PluginBase{
	const DEFAULT_CFG_VALUES = [
		"on-game-end" => [
			"teleport-default-level" => true,
			"teleport-level" => "",
			"teleport-position" => [null, null, null]
		],
		"time-limit" => [
			"max-time" => -1,
			"display" => [
				"chat-interval" => -1,
				"chat-towards-end" => true,
				"popup" => true
			]
		]
	];
	
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
	 * C.x.x Breaking changes, disable your plugin with an error message or disable any TTT API usage.
	 * x.C.x Feature additions, usually not breaking. (Use this if you require certain new features)
	 * x.x.C BugFixes on API related functions, not breaking.
	 */
	const API_VERSION = "0.1.0";

	public function onEnable(){
		@mkdir($this->getDataFolder());
		$this->db = new Config($this->getDataFolder()."TTTdb.yml", Config::YAML, []); //TODO:betterDB
		$this->saveDefaultConfig();
		$this->validateConfig();
		$this->getConfig()->save();
		
		$this->listener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($this->listener, $this);
		$this->playerManager = new PlayerManager($this);
		$this->gameManager = new GameManager($this);
		foreach($this->db->getAll() as $data){
			if(!$this->getServer()->isLevelLoaded($data[0][3])){
				if(!$this->getServer()->loadLevel($data[0][3])){
					$this->getLogger()->notice("Could not load Arena in level '".$data[0][3]."': Level not found!");
					continue;
				}
			}
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
		$this->processConfig();
	}
	
	/**
	 * @internal
	 * Sets missing keys in the config to their default value
	 */
	private function validateConfig(){
		$cfg = $this->getConfig();
		$this->populateVals(self::DEFAULT_CFG_VALUES, $cfg);
	}
	
	/**
	 * @internal
	 * Populates the given key with a key => val association
	 *
	 * @param array $vals
	 * @param Config $cfg
	 * @param string $oKey
	 */
	private function populateVals(array $vals, Config $cfg, string $oKey = ""){
		foreach($vals as $key => $val){
			if(is_array($val)){
				$this->populateVals($val, $cfg,$oKey.$key);
			}
			if(!$cfg->exists($key)){
				$cfg->set($oKey.$key, $val);
			}
		}
	}
	
	/**
	 * Sets the config options on the various Managers
	 */
	private function processConfig(){
		$onGameEnd = $this->getConfig()->get("on-game-end");
		$level = null;
		if($onGameEnd["teleport-default-level"]){
			$level = $this->getServer()->getDefaultLevel();
		}elseif($onGameEnd["teleport-level"] !== ""){
			$level = $this->getServer()->getLevelByName($onGameEnd["teleport-level"]);
			if(!$level instanceof Level){
				$this->getLogger()->warning(
					"Could not find the level ".$onGameEnd["teleport-level"].
					". Please ensure that that level exists and you have specified the folder name of the level!"
				);
				$level = null;
			}
		}
		if($level === null){
			$this->gameManager->setOnGameEndPosition(null);
		}else{
			$teleportPosition = $onGameEnd["teleport-position"];
			if($teleportPosition[0] === null || $teleportPosition[1] === null | $teleportPosition[2] === null){
				$this->gameManager->setOnGameEndPosition($level->getSafeSpawn());
			}else{
				$this->gameManager->setOnGameEndPosition(new Position(
					$teleportPosition[0], $teleportPosition[1], $teleportPosition[2],
					$level
				));
			}
		}
	}
	
	/**
	 * For extension plugins to test if they are compatible with the version
	 * of TTT installed.
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
	
	/**
	 * @param Position[] $positions
	 */
	public function saveArena(array $positions){
		$this->db->set(
			count($this->db->getAll()),
			[
				[$positions[0]->x, $positions[0]->y, $positions[0]->z, $positions[0]->level->getFolderName()],
				[$positions[1]->x, $positions[1]->y, $positions[1]->z, $positions[1]->level->getFolderName()]
			]
		);
		$this->db->save(true);
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if($command->getName() == "tictactoe arenacreate"){
			if($sender instanceof Player){
				$sender->sendMessage("Touch the lower left and then the upper right block of the game board!");
				$this->listener->addArenaCreationSession($sender->getId());
			}
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