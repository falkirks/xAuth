<?php
/*
                            _     _     
            /\             | |   | |    
 __  __    /  \     _   _  | |_  | |__  
 \ \/ /   / /\ \   | | | | | __| | '_ \ 
  >  <   / ____ \  | |_| | | |_  | | | |
 /_/\_\ /_/    \_\  \__,_|  \__| |_| |_|
                                        
                                        */


#Loader for xAuth, loads up everything.
namespace xFlare\xAuth;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Server;

class Loader extends PluginBase implements Listener{
  public $loginmanager=array(); //Idividual player login statuses using arrays (sessions).
  public $chatprotection=array();
  public $proccessmanager=array();
  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->getServer()->getLogger()->info("§7> §3Starting up §ax§dAuth§7...§6Loading §edata§7.");
    $this->saveDefaultConfig();
    $this->provider = strtolower($this->getConfig()->get("autentication-type"));
    $this->status = null; //Keeps track of auth status.
    $this->memorymanagerdata = 0;
    $this->debug = true; //$this->getConfig()->get("debug-mode");
    $this->version = "1.0.0"
    $this->safemode = $this->getConfig()->get("safe-mode");
    $this->checkForConfigErrors();
  }
  public function checkForConfigErrors(){ //Will try to fix errors, and repair config to prevent erros further down.
    $errors = 0;
    if($this->getConfig()->get("version") !== $this->version){
      $this->status = "failed";
      $this->getServer()->getLogger()->info("§7[§eException§7] §3Updating config...xAuth will be enabled soon...§7.");
      $myoptions=array();
      array_push($myoptions, $this->provider); //Push old data so it can be inserted in new config.
      $this->updateConfig($myoptions
      return;
    }
    if($this->provider !== "mysql" && $this->provider !== "yml"){
      $this->status = "failed";
      $this->getServer()->getLogger()->info("§7[§cError§7] §3Invaild §ax§dAuth §3provider§7!");
      $this->getServer()->shutdown();
    }
    if($this->getConfig()->get("database-checks") === true && $this->provider !== "mysql"){
      $this->getConfig()->set("data-checks", false);
      $this->getConfig()->save();
      $errors++;
    }
    if($errors !== 0){
        $this->getConfig()->reload();
        $this->getServer()->getLogger()->info("§7[§ax§dAuth§7] " . $errors . " §cerrors have been found§7.\n§3We tried to fix it§7, §3but just in case review your config settings§7!");
    }
    $this->status = "enabled"; //Assuming errors have been fixed.
    $this->getServer()->getPluginManager()->registerEvents(new LoginTasks($this), $this);
    $this->getServer()->getPluginManager()->registerEvents(new LoginAndRegister($this), $this);
    $this->getServer()->getScheduler()->scheduleRepeatingTask(new MemoryStatus($this), 60*20);
    if($this->getConfig()->get("database-checks") === true){
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new ErrorChecks($this), 30*20);
    }
    if($this->provider === "yml"){
      $this->registered = new Config($this->getDataFolder() . "registered.txt", Config::ENUM, array());
    }
    if($this->getConfig()->get("hotbar-message") === true){
      $this->getServer()->getScheduler()->scheduleRepeatingTask(new AuthMessage($this), 20);
    }
    $this->registerConfigOptions();
    $this->getServer()->getLogger()->info("§7> §ax§dAuth §3has been §aenabled§7.");
  }
  public function updateConfig(){
    $this->getServer()->getLogger()->info("§7[§axAuth§7] §3Updating xAuth config to $this->version...");
    $this->getConfig()->set("version", $this->version);
    $this->getConfig()->save();
    $this->checkForConfigErrors($this->getConfig()); //Recheck for errors since the proccess was stoped to update it.
  }
  public function registerConfigOptions(){ //Config -> Object for less lag.
    $this->allowMoving = $this->getConfig()->get("allow-moving");
    $this->allowPlace = $this->getConfig()->get("allow-block-placing");
    $this->allowBreak = $this->getConfig()->get("allow-block-breaking");
    $this->allowCommand = $this->getConfig()->get("allow-commands");
    $this->simplepassword = $this->getConfig()->get("simple-passcode-blocker");
  }
}
    
