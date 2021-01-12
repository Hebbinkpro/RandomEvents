<?php


namespace Hebbinkpro\RandomEventsTDB\tasks\BountyHunter;

use Hebbinkpro\RandomEventsTDB\Main;
use Hebbinkpro\RandomEventsTDB\events\BountyHunterEvent;

use pocketmine\scheduler\Task;

class DelayTask extends Task
{

	private $main;
	private $event;
	private $config;
	private $delayUntil;
	private $cdMsg30 = false;
	private $cdMsg10 = false;

    public function __construct(){
    	$this->main = Main::getInstance();
    	$this->config = $this->main->getConfig()->get("events")["HPCustomEvents"];
    	$this->event = $this->main->event;
    	$delayTime = $this->config["startDelay"] * 60;
    	$this->delayUntil = time()+$delayTime;
	}

    public function onRun(int $currentTick)
    {


        if(!$this->event instanceof BountyHunterEvent){
        	$this->getHandler()->cancel();
        	return;
		}

		if($this->event->finished === true){
			$this->getHandler()->cancel();
			return;
		}

		if(time() >= $this->delayUntil){
			$this->event->delay = false;
			$this->event->startGame();
			$this->getHandler()->cancel();
			return;
		}

        if(time() >= $this->delayUntil-10 and !$this->cdMsg10){
        	$this->cdMsg10 = true;
        	$this->main->getServer()->broadcastMessage("§l§7[§9BountyHunter§7]§r De bounty begint over 10 seconden");
		}

        if(time() >= $this->delayUntil-30 and !$this->cdMsg30){
			$this->cdMsg30 = true;
			$this->main->getServer()->broadcastMessage("§l§7[§9BountyHunter§7]§r De bounty begint over 30 seconden");
		}


    }
}