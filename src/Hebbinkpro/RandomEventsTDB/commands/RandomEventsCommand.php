<?php


namespace Hebbinkpro\RandomEventsTDB\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use Hebbinkpro\RandomEventsTDB\commands\subcommands\StartEventCommand;
use pocketmine\command\CommandSender;

class RandomEventsCommand extends BaseCommand
{
    protected function prepare(): void {

    	$this->setPermission("re.cmd");
    	$this->registerSubCommand(new StartEventCommand("startevent", "Start direct een event", ["start"]));

    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {

    	$sender->sendMessage("§l§7[§9BountyHunter§7]§r §6Commands:");
    	$sender->sendMessage(" - /re start <event> | Start an new event");

    }
}