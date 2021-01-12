<?php


namespace Hebbinkpro\RandomEventsTDB\commands\subcommands;

use Hebbinkpro\RandomEventsTDB\events\BountyHunterEvent;
use Hebbinkpro\RandomEventsTDB\events\GoToLocationEvent;
use Hebbinkpro\RandomEventsTDB\Main;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\args\RawStringArgument;
use Hebbinkpro\RandomEventsTDB\tasks\CooldownTask;
use pocketmine\command\CommandSender;

class StartEventCommand extends BaseSubCommand
{

	private $main;

	protected function prepare(): void
	{
		$this->setPermission("re.cmd.start");
		$this->registerArgument(0, new RawStringArgument("eventType", true));

		$this->main = Main::getInstance();
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
	{

		if($this->main->event instanceof BountyHunterEvent or $this->main->event instanceof GoToLocationEvent){
			$sender->sendMessage("§l§7[§9BountyHunter§7]§r §cEr is al een event bezig");
			return;
		}

		if(isset($args["eventType"])){
			$type = strtolower($args["eventType"]);
		}

		$events = [
			"bountyhunter", "bh",
			"gotolocation", "gtl"
		];

		if(!isset($type)){
			$sender->sendMessage("§l§7[§9BountyHunter§7]§r Geen event opgegeven. Kies uit:");
			$sender->sendMessage(" - HPCustomEvents");
			$sender->sendMessage(" - GoToLocation");
			return;
		}

		if(!in_array($type, $events)){
			$sender->sendMessage("§l§7[§9BountyHunter§7]§r Geen geldig event opgegeven. Kies uit:");
			$sender->sendMessage(" - HPCustomEvents");
			$sender->sendMessage(" - GoToLocation");
			return;
		}

		if($this->main->cooldown instanceof CooldownTask){
			$this->main->cooldown->startTimer();
		}

		$this->main->timer->forceStart = true;


		if($type === "bountyhunter" or $type === "bh"){
			//start bountyhunter event
			$this->main->timer->eventToStart = "HPCustomEvents";
			$sender->sendMessage("§l§7[§9BountyHunter§7]§r §aJe hebt een bounty hunter event gestart.");

		}
		if($type === "gotolocation" or $type=== "gtl"){
			//start gotolocation event
			$this->main->timer->eventToStart = "GoToLocation";
			$sender->sendMessage("§l§7[§9BountyHunter§7]§r §aJe hebt een go to location event gestart");
		}

		if(count($this->main->getServer()->getOnlinePlayers()) < 2) {
			$sender->sendMessage(" - §cEr zijn minder dan 2 spelers online. Wanneer er twee spelers online zijn wordt het event pas gestart!");
		}
	}
}