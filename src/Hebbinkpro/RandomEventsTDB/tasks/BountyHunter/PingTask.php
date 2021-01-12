<?php


namespace Hebbinkpro\RandomEventsTDB\tasks\BountyHunter;

use Hebbinkpro\RandomEventsTDB\events\BountyHunterEvent;
use Hebbinkpro\RandomEventsTDB\Main;

use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;

class PingTask extends Task {

	private $main;
	private $config;

	/**
	 * @var float|int
	 */
	private $pingTime;
	private $endTime;
	private $newPing;

	/**
	 * @var Location
	 */
	private $lastPos;

	/**
	 * @var BountyHunterEvent
	 */
	private $event;


	public function __construct(){
		$this->main = Main::getInstance();
		$this->config = $this->main->getConfig()->get("events")["HPCustomEvents"];
		$this->event = $this->main->event;
		$this->pingTime = $this->config["pingTime"]*60;
		$this->endTime = time() + $this->config["endTime"]*60;
		$this->newPing = time() + $this->pingTime;

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

		$now = time();
		if($now >= $this->endTime){
			$this->event->targetWins();
			$this->getHandler()->cancel();
			return;
		}

		if($now >= $this->newPing){

			$hasMoved = $this->checkPostion();
			if($hasMoved === true){
				$this->sendPositionMessage();
				$this->lastPos = $this->event->target->getLocation();
			}else{
				$this->main->getServer()->broadcastMessage("§l§7[§9BountyHunter§7]§r §e" . $this->event->target->getName() .
					"§r was niet vergenoeg van zijn vorige positie verwijderd. De game wordt gerestart en er wordt een nieuw target gekozen.");
				$this->event->restartGame();
				$this->getHandler()->cancel();
			}

		}else{
			return;
		}

		$this->newPing = $now + $this->pingTime;
    }

    private function sendPositionMessage(){
		$target = $this->event->target;
		$this->main->getServer()->broadcastMessage("§l§7[§9BountyHunter§7]§r De locatie van §e" . $target->getName() . "§r is:\n".
			" - §6" . $target->getFloorX() . ", " . $target->getFloorY() . ", " . $target->getFloorZ());
	}

	private function checkPostion(){
		if(is_null($this->lastPos)){
			return true;
		}
		$dist = $this->event->target->distance(new Vector3($this->lastPos->getFloorX(), $this->lastPos->getFloorY(), $this->lastPos->getFloorZ()));
		if($dist <= $this->config["minDistance"]){
			return false;
		}else{
			return true;
		}
	}
}