<?php

namespace Abius\ClearLag;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use vennv\vapm\System;
use vennv\vapm\VapmPMMP;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\ItemEntity;

class Loader extends PluginBase {

    public static $time_alert = [60, 30, 15, 5, 3, 2, 1];
    public static $time = 0;
    public static $config;
    public static bool $flag = false;

    public function onEnable(): void{
        $this->saveResource("clearlag.yml");
        self::$config = new Config($this->getDataFolder() . "clearlag.yml", Config::YAML);
        VapmPMMP::init($this);
        System::setInterval(function(){
            self::onRun();
        }, 1000);
    }

    public static function getData(): Config{
        return self::$config;
    }

    public static function getDefaultTime(): ?int{
        return self::getData()->get("time");
    }

    public static function getTypeAlert(): ?string{
        return self::getData()->get("type_alert");
    }

    public static function onRun(){
        if(self::$flag == false){
            self::$time = self::getDefaultTime();
            self::$flag = true;
        }
        if(self::$time > 0){
            $times = self::$time;
            $times--;
            if(in_array($times, self::$time_alert)){
                $msg = self::getData()->get("alert_clearlag_running");
                $msg = str_replace("{time}", (string)$times, $msg);
                if(self::getTypeAlert() == "message"){
                    Server::getInstance()->broadcastMessage($msg);
                }
                if(self::getTypeAlert() == "popup"){
                    Server::getInstance()->broadcastPopup($msg);
                }
            }
            self::$time = $times;
        }
        else{
            self::$flag = false;
            $count = 0;
            foreach (Server::getInstance()->getWorldManager()->getWorlds() as $world) {
                foreach ($world->getEntities() as $entity) {
                    if (($entity instanceof ItemEntity) or ($entity instanceof ExperienceOrb)) {
                        $entity->flagForDespawn();
                        $count++;
                    }
                }
            }
            $msg = self::getData()->get("alert_clearlag_sucessful");
            $msg = str_replace("{count}", $count, $msg);
            if(self::getTypeAlert() == "message"){
                Server::getInstance()->broadcastMessage($msg);
            }
            if(self::getTypeAlert() == "popup"){
                Server::getInstance()->broadcastPopup($msg);
            }
        }
    }
}