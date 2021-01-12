<?php


namespace Hebbinkpro\RandomEventsTDB\tasks;

use Hebbinkpro\RandomEventsTDB\events\BountyHunterEvent;
use Hebbinkpro\RandomEventsTDB\events\GoToLocationEvent;
use Hebbinkpro\RandomEventsTDB\Main;

use pocketmine\scheduler\Task;

class StartTask extends Task
{
	private $main;
	private $config;
	private $enabeld = false;

	public $eventToStart = null;
	public $forceStart = false;

    public function __construct(){
    	$this->main = Main::getInstance();
    	$this->config = $this->main->getConfig();

		$this->main->timerStartTime = time();
		$this->main->timerUntil = $this->main->timerStartTime + $this->main->timerDuration;

		//check if there is no timer, cooldown or event started
		if(!$this->main->timer and !$this->main->cooldown and is_null($this->main->startedEvent)){
			$this->main->timer = $this;
			$this->enabeld = true;
		}
	}

	public function onRun(int $currentTick)
    {
		if(!$this->enabeld){

			$this->main->getLogger()->alert("Can't start a TIMER! There is an other TIMER, COOLDOWN or EVENT running!");
			$this->stopTimer();
			return;
		}

    	$now = time();
    	$difference = $this->main->timerUntil - $now;

    	if($this->forceStart === true){
    		//set minimal players to 2 with a force start, because you can't play multiplayer games solo
    		if(count($this->main->getServer()->getOnlinePlayers()) >= 2) {
				$this->startEvent();
			}
    		return;
		}
    	elseif(count($this->main->getServer()->getOnlinePlayers()) >= $this->main->minOnlinePlayers){
			if($difference > 0){
				$int = mt_rand(0, $difference*20);
				if($int === 0){
					$this->startEvent();
				}
			}
			else{

				$this->startEvent();
			}
			return;
		}
    }

    private function getRandomEvent(){
    	$events = [
    		//"GoToLocation",
			"HPCustomEvents"
		];

    	$event = array_rand($events, 1);

    	return $events[$event];
	}

	private function startEvent(){
    	if(!is_null($this->eventToStart)){
    		if($this->eventToStart === "GoToLocation"){
				$this->stopTimer();
				$startEvent = new GoToLocationEvent();
				return;
			}
    		elseif($this->eventToStart === "HPCustomEvents"){
				$this->stopTimer();
				$startEvent = new BountyHunterEvent();

				return;
			}
		}

    	$event = $this->getRandomEvent();
		$this->stopTimer();

    	if($event === "GoToLocation"){
    		$startEvent = new GoToLocationEvent();
		}
    	elseif($event === "HPCustomEvents"){
			$startEvent = new BountyHunterEvent();
		}else{
    		$this->main->startedEvent = null;
		}
	}

	private function stopTimer(){
		$this->main->timer = false;
		$this->getHandler()->cancel();
	}
}