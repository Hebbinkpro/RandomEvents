<?php


namespace Hebbinkpro\RandomEventsTDB\events;

use Hebbinkpro\RandomEventsTDB\Main;

use twisted\multieconomy\MultiEconomy;
use pocketmine\Player;

class GoToLocationEvent
{
	public const name = "GoToLocation";

	public $location;
	public $reward = 0;
	public $winner = null;

	private $main;
	private $config;

	public function __construct(){
		$this->main = Main::getInstance();
		$this->config = $this->main->getConfig()->get("events")["GoToLocation"];

		//check if there is no timer, cooldown or event started
		if($this->main->timer or $this->main->cooldown or !is_null($this->main->startedEvent)){
			$this->main->getLogger()->alert("Can't start a new EVENT! There is an other TIMER, COOLDOWN or EVENT running!");
			return;
		}

		$this->main->startedEvent = self::name;
		$this->main->event = $this;

		$locations = $this->config["locations"];
		$loc = array_rand($locations, 1);
		$this->location = $locations[$loc];

		$this->reward = $this->config["reward"];
		var_dump($this->reward);

		$this->sendMessage();

	}

	public function finish(Player $player){
		//set winner
		$this->winner = $player->getName();
		//send message
		$this->main->getServer()->broadcastMessage("§l§7[§9RandomEvents§7]§r §6" . $this->winner . " §aheeft de eind locatie als eerste gevonden en heeft gewonnen!");

		//give money to the winner
		$me = MultiEconomy::getInstance();
		$currency = $me->getCurrency("Euros");
		$currency->addToBalance($player->getName(), $this->reward);
		$currency->save();

		//set event to stopping and start a new cooldown
		$this->stopEvent();
		$this->main->startCooldown();
	}

	private function stopEvent(){
		$this->main->event = null;
		$this->main->startedEvent = null;
	}

	public function sendMessage(Player $player = null){

		$l = $this->location;
		if(!isset($l["y"])){
			$l["y"] = "~";
		}

		$msg =
			"§l§7[§9RandomEvents§7]§r Er is een go to location event gestart!\n".
			" -	§r§eVind de locatie als eerste en win €". $this->reward."§r\n".
			" -	§bLocatie:§r " . $l["x"] . ", " . $l["y"] . ", " . $l["z"];

		if(!is_null($player)){
			$player->sendMessage($msg);
			return;
		}

		$this->main->getServer()->broadcastMessage($msg);
	}

}