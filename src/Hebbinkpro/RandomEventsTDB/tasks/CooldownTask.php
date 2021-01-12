<?php


namespace Hebbinkpro\RandomEventsTDB\tasks;

use Hebbinkpro\RandomEventsTDB\Main;
use pocketmine\scheduler\Task;

class CooldownTask extends Task
{
	private $main;
	private $enabeld = false;

	public function __construct(){
		$this->main = Main::getInstance();

		$this->main->cooldownStartTime = time();
		$this->main->cooldownUntil = $this->main->cooldownStartTime + $this->main->cooldownDuration;

		//check if there is no timer, cooldown or event started
		if($this->main->timer === false and is_null($this->main->startedEvent)){
			$this->main->cooldown = $this;
			$this->enabeld = true;
		}
	}

	public function onRun(int $currentTick)
    {
		if(!$this->enabeld){
			$this->main->getLogger()->alert("Can't start a COOLDOWN! There is a other TIMER, COOLDOWN or EVENT running!");
			$this->getHandler()->cancel();
			return;
		}

		$now = time();
		$difference = $this->main->cooldownUntil - $now;
		if($difference <= 0){
			$this->startTimer();
		}
    }

    public function startTimer(){
		$this->main->cooldown = false;
		$this->main->startTimer();
		$this->getHandler()->cancel();
	}
}