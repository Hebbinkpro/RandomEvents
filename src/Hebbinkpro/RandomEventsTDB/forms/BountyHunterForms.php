<?php


namespace Hebbinkpro\RandomEventsTDB\forms;

use Hebbinkpro\RandomEventsTDB\Main;
use Hebbinkpro\RandomEventsTDB\events\BountyHunterEvent;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\player;

class BountyHunterForms
{

	private $main;
	private $event;
	private $config;

	public function __construct(){

		$this->main = Main::getInstance();
		$this->event = $this->main->event;
		$this->config = $this->main->getConfig()->get("events")["HPCustomEvents"];
	}

	public function sendAcceptForm(Player $player){

		$form = new ModalForm(function(Player $player, $data){
			if(isset($data)){
				switch ($data){
					case true:
						//player accepted
						if($this->event instanceof BountyHunterEvent){
							$this->event->target = $player;
							$player->sendMessage("§l§7[§9RandomEvents§7]§r §aJe bent gekozen als target, je hebt vanaf nu " . $this->config["startDelay"] ." minuten om te vluchten");
							$this->event->start();
						}
						break;
					case false:
						//player declined
						if($this->event instanceof BountyHunterEvent){
							$this->event->declinedPlayers[] = $player->getName();
							$player->sendMessage("§l§7[§9RandomEvents§7]§r §aJe hebt gekozen niet mee te doen als target.");
							$this->event->getRandomTarget();
						}
						break;
				}
			}
		});

		$form->setTitle("§6BountyHunter §7- §9Target selector");
		$form->setContent("Je bent gekozen als target. " .
			"Jouw doel is om 30 minuten lang uit handen te blijven van de andere spelers. ".
			"Wanneer een speler je heeft gepakt heb je verloren, na de 30 minuten heb je gewonnen.".
			"\nKlik op §aIk doe mee§r om het target te zijn" .
			"\nof klik op §cIk doe niet mee§r als je geen target wilt zijn, er wordt dan een nieuw target gekozen");
		$form->setButton1("Ik doe mee");
		$form->setButton2("§cIk doe niet mee");

		$player->sendForm($form);
	}

}