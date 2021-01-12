<?php


namespace Hebbinkpro\RandomEventsTDB;

use Hebbinkpro\RandomEventsTDB\commands\RandomEventsCommand;
use Hebbinkpro\RandomEventsTDB\tasks\CooldownTask;
use Hebbinkpro\RandomEventsTDB\tasks\StartTask;

use CortexPE\Commando\PacketHooker;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use twisted\multieconomy\MultiEconomy;

class Main extends PluginBase implements Listener {

	public static $instance;

	public $config;
	public $MultiEconomy;

	public $timerStartTime = 0;
	public $timerDuration = 0;
	public $timerUntil = 0;
	public $timer = false;

	public $cooldownStartTime = 0;
	public $cooldownDuration = 0;
	public $cooldownUntil = 0;
	public $cooldown = false;

	public $minOnlinePlayers = 0;

	public $startedEvent = null;
	public $event = null;


	public static function getInstance(){
		return self::$instance;
	}

	public function onEnable(){
		self::$instance = $this;

		if(!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		$this->saveResource("config.yml");
		$this->config = new Config($this->getDataFolder() . "config.yml");

		$this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

		//set max cooldown
		$cd = $this->getConfig()->get("cooldown");;
		$cdD = $cd["days"];
		$cdH = $cd["hours"];
		$cdSec = ($cdD*24*60*60) + ($cdH*60*60);
		$this->cooldownDuration = $cdSec;

		$tmr = $this->getConfig()->get("timer");;
		$tmrD = $tmr["days"];
		$tmrH = $tmr["hours"];
		$tmrSec = ($tmrD*24*60*60) + ($tmrH*60*60);
		$this->timerDuration = $tmrSec;

		$this->minOnlinePlayers = $this->getConfig()->get("minPlayersOnline");

		$this->getServer()->getCommandMap()->register("randomevents", new RandomEventsCommand($this, "randomevents", "Beheer de randomevents", ["re"]));

		$this->startTimer();
	}

	public function startCooldown(){
		$cooldownTask = new CooldownTask();
		$this->getScheduler()->scheduleRepeatingTask($cooldownTask, 1);
	}

	public function startTimer(){
		$startTask = new StartTask();
		$this->getScheduler()->scheduleRepeatingTask($startTask, 1);
	}

}