<?php


namespace Hebbinkpro\RandomEventsTDB;

use Hebbinkpro\RandomEventsTDB\events\BountyHunterEvent;
use Hebbinkpro\RandomEventsTDB\events\GoToLocationEvent;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class EventListener implements Listener
{

	private $main;

	public function __construct(){
		$this->main = Main::getInstance();

	}

	public function onChat(PlayerChatEvent $e){
	}

	public function onJoin(PlayerJoinEvent $e){
		$player = $e->getPlayer();

		if($this->main->startedEvent === "GoToLocation"){
			$event = $this->main->event;
			if($event instanceof GoToLocationEvent){
				$event->sendMessage($player);
			}
		}
		if($this->main->startedEvent === "HPCustomEvents"){
			$event = $this->main->event;
			if($event instanceof BountyHunterEvent){
				$event->sendStartMessage();
			}
		}
	}

	public function onQuit(PlayerQuitEvent $e){
		if($this->main->startedEvent === "HPCustomEvents"){
			$event = $this->main->event;
			if($event instanceof BountyHunterEvent){
				$player = $e->getPlayer();
				if($player === $event->target){
					$event->onTargetQuit();
				}
			}
		}
	}

	public function onMove(PlayerMoveEvent $e){

		if($this->main->startedEvent === "GoToLocation"){
			$event = $this->main->event;

			if($event instanceof GoToLocationEvent){
				$player = $e->getPlayer();

				$pos = $e->getTo();
				$endPos = $event->location;

				if($pos->getFloorX() === $endPos["x"] and $pos->getFloorZ() === $endPos["z"] and $player->getLevel()->getName() === $endPos["world"]){
					if(isset($endPos["y"])){
						if($pos->getFloorY() === $endPos["y"]){
							$event->finish($player);
							return;
						}
						return;
					}else{
						$event->finish($player);
						return;
					}
				}
				return;
			}
		}
	}

	public function onDamage(EntityDamageByEntityEvent $e){

		if($this->main->startedEvent === "HPCustomEvents"){

			$event = $this->main->event;
			if($event instanceof BountyHunterEvent){

				$player = $e->getEntity();
				$damager = $e->getDamager();

				if($player instanceof Player and $damager instanceof Player){

					//if target is hit, the player who hit the target wins
					if($player === $event->target){
						$event->hunterWins($damager);
						$e->setCancelled();
					}
				}
			}
		}

	}


}