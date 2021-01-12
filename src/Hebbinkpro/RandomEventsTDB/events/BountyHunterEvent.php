<?php


namespace Hebbinkpro\RandomEventsTDB\events;

use Hebbinkpro\RandomEventsTDB\Main;
use Hebbinkpro\RandomEventsTDB\forms\BountyHunterForms;
use Hebbinkpro\RandomEventsTDB\tasks\BountyHunter\DelayTask;
use Hebbinkpro\RandomEventsTDB\tasks\BountyHunter\PingTask;

use Hebbinkpro\RandomEventsTDB\tasks\CooldownTask;
use pocketmine\level\Position;
use pocketmine\Player;
use specter\network\SpecterPlayer;
use twisted\multieconomy\MultiEconomy;

class BountyHunterEvent
{

	public const name = "HPCustomEvents";

	private $main;
	private $config;

	private $winner;

	public $delay = false;
	public $finished = false;

	public $noMinPlayers = false;

	/**
	 * @var int
	 */
	public $reward;

	/**
	 * @var Player
	 */
	public $target;

	/**
	 * @var Position
	 */
	public $location;
	public $lastLocation;

	/**
	 * @var array
	 */
	public $declinedPlayers = [];

	public function __construct(){
		$this->main = Main::getInstance();
		$this->config = $this->main->getConfig()->get("events")["HPCustomEvents"];

		//check if there is no timer, cooldown or event started
		if($this->main->timer or $this->main->cooldown or !is_null($this->main->startedEvent)){
			$this->main->getLogger()->alert("Can't start a new EVENT! There is an other TIMER, COOLDOWN or EVENT running!");
			return;
		}

		$this->main->startedEvent = self::name;
		$this->main->event = $this;

		$this->reward = $this->config["reward"];

		$this->getRandomTarget();

	}

	public function getRandomTarget(){

		$players = $this->main->getServer()->getOnlinePlayers();
		$targets = [];
		foreach($players as $player){
			$name = $player->getName();
			if(!in_array($name, $this->declinedPlayers)){
				$targets[] = $name;
			}
		}
		if($targets === []){
			return;
		}
		$tarKey = array_rand($targets);

		$target = $this->main->getServer()->getPlayer($targets[$tarKey]);
		//$target = $this->main->getServer()->getPlayer("Hebbinkpro");

		$forms = new BountyHunterForms();
		//$forms->sendAcceptForm($target); //DISABELD ivm specter players

		$this->target = $target;
		$target->sendMessage("§l§7[§9RandomEvents§7]§r §aJe bent gekozen als target, je hebt vanaf nu " . $this->config["startDelay"] ." minuten om te vluchten");
		$this->start();

	}

	public function start(){
		$this->delay = true;

		$this->sendStartMessage();
		$delayTask = new DelayTask();
		$this->main->getScheduler()->scheduleRepeatingTask($delayTask, 1);
	}

	public function startGame(){
		if($this->delay === true){
			return;
		}

		$this->sendStartMessage();

		$this->lastLocation = $this->target->getPosition();
		$pingTask = new PingTask();
		$this->main->getScheduler()->scheduleRepeatingTask($pingTask, 1);
	}

	public function targetWins(){
		$this->winner = $this->target->getName();
		$this->endGame();
		$msg = "§l§7[§9BountyHunter§7]§r De bounty is afgelopen.\n" .
			" - §e" . $this->winner . "§r§a is " . $this->config["endTime"] . " minuten uit handen gebleven van de hunters en heeft gewonnen.";
		$this->main->getServer()->broadcastMessage($msg);

		//give money to the winner
		$me = MultiEconomy::getInstance();
		$currency = $me->getCurrency("euros");
		$currency->addToBalance($this->winner, $this->reward);
		$currency->save();

		$this->main->startCooldown();
	}

	public function hunterWins(Player $player){
		$this->winner = $player->getName();
		$this->endGame();
		$msg = "§l§7[§9BountyHunter§7]§r De bounty is afgelopen.\n" .
			" - §e" . $this->winner . "§r§a heeft " . $this->target->getName() . " gevonden en heeft gewonnen.";
		$this->main->getServer()->broadcastMessage($msg);

		//give money to the winner
		$me = MultiEconomy::getInstance();
		$currency = $me->getCurrency("Euros");
		$currency->addToBalance($this->winner, $this->reward);
		$currency->save();

		$this->main->startCooldown();
	}

	public function onTargetQuit(){
		if($this->config["restartWhenOffline"] === true){
			$this->winner = null;
			$msg = "§l§7[§9BountyHunter§7]§r De bounty is afgelopen.\n" .
				" - Het target §e".$this->target->getName()."§r is geleaved, de bounty wordt gerestart.";
			$this->main->getServer()->broadcastMessage($msg);
			$this->restartGame();
		}else{
			$this->winner = null;
			$msg = "§l§7[§9BountyHunter§7]§r De bounty is afgelopen.\n" .
				" - Het target §e".$this->target->getName()."§r is geleaved, de bounty wordt gestopt.";
			$this->main->getServer()->broadcastMessage($msg);
			$this->endGame();
		}
	}

	private function endGame(){
		$this->finished = true;
		$this->main->startedEvent = null;
		$this->main->event = null;
	}

	public function restartGame(){
		$this->endGame();

		$totalPlayers = count($this->main->getServer()->getOnlinePlayers())-1;
		if($this->noMinPlayers === true){
			//check if total players
			if($totalPlayers < 2){
				$this->main->getServer()->broadcastMessage("§l§7[§9BountyHunter§7]§r Er zijn te weinig spelers online, de game wordt niet gerestart.");
				$this->main->startCooldown();
				return;
			}
			$newGame = new BountyHunterEvent();
			return;
		}

		if($totalPlayers < $this->main->minOnlinePlayers and $totalPlayers < 2){
			$this->main->getServer()->broadcastMessage("§l§7[§9BountyHunter§7]§r Er zijn te weinig spelers online, de game wordt niet gerestart.");
			$this->main->startCooldown();
			return;
		}
		$newGame = new BountyHunterEvent();
	}

	public function sendStartMessage(Player $player = null){

		if(!$this->delay){
			$msg = "§l§7[§9BountyHunter§7]§r De bounty is begonnen!\n".
				" - §eTarget: §r" . $this->target->getName() . "§r\n".
				" - §bLocatie: §cBeschikbaar over " . $this->config["pingTime"] . " minuten";
		}else{
			$msg = "§l§7[§9RandomEvents§7]§r Er is een HPCustomEvents event gestart!\n".
				" - §3Doel: §rVind het target voor deze weet te ontsnappen na " . $this->config["endTime"] . " minuten\n".
				" - §6Reward: §r€" . $this->config["reward"] . "\n" .
				" - §bBegint over: §r" . $this->config["startDelay"] . " minuten\n" .
				" - §eTarget: §cOnbekend§r";
		}

		if(!is_null($player)){
			$player->sendMessage($msg);
			return;
		}

		$this->main->getServer()->broadcastMessage($msg);
	}
}