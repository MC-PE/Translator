<?php
namespace Ad5001\Translator;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\network\protocol\TextPacket;
use pocketmine\utils\Utils;


class Main extends PluginBase implements Listener{


   public function onEnable(){
        $this->reloadConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getCommandMap()->register($this->getConfig()->get("SetLangCommand"), new SetLangCommand($this));
    }


    public function onLoad(){
        $this->saveDefaultConfig();
    }


    public function onPlayerJoin(\pocketmine\event\player\PlayerJoinEvent $event) {
        $event->getPlayer()->sendMessage("Welcome to this server ! This server is using Translator_v1.0 by Ad5001 which means every message you will see on this server will be translated to your's ! Your language has been set by default to " . $this->getConfig()->get('DefaultLang'). ". You can set your language by doing /" . $this->getConfig()->get("SetLangCommand") . " <lang>. Have fun on this server !");
    }


    public function setLang(Player $player, string $lang) {
        if(preg_match('/^([a-z]{2})(-[A-Z]{2})?$/', $lang)) {
            $players=  json_decode(file_get_contents("players.json"), true);
            $players[$player->getName()] = $lang;
            file_put_contents("players.json", json_encode($players));
            return true;
        } else {
            return false;
        }
    }


    public function getLang(Player $player) {
        return (new Config("players.json"))->get($player->getName());
    }


    public function onPlayerPrelogin(\pocketmine\event\player\PlayerPreLoginEvent $event) {
        if(strlen($this->getLang($event->getPlayer())) !== 2) {
            $this->setLang($event->getPlayer(), $this->getConfig()->get("DefaultLang"));
            $this->isNew[$event->getPlayer()->getName()] = true;
        }
    }


    public function onDataPacketSend(\pocketmine\event\server\DataPacketSendEvent $event) {
        if($event->getPacket() instanceof TextPacket) {
            $pak = $event->getPacket();
            $lang = $this->getLang($event->getPlayer());
            if($pak->source instanceof Player && $pak->source !== $event->getPlayer()) {
                $baselang = $this->getLang($p->source);
            } else {
                $baselang = strlen($this->getConfig()->get("DefaultLang")) !== 2 ? "en" : $this->getConfig()->get("DefaultLang");
            }
            if($pak->type !==TextPacket::TYPE_TRANSLATION and $baselang !== $lang) {
                if(!strpos("<" . is_null($pak->source) ? "" : $pak->source->getName()  . ">" , $pak->message)) { // Not Chat
                    $pak->message = $this->translate($baselang, $lang, $pak->message);
                    $pak->encode();
                } else {
                    $pak->message = "<" . $pak->source->getName() . ">" . $this->translate($baselang, $lang, str_ireplace("<" . $pak->source->getName() . ">", "", $pak->message, 1));
                    $pak->encode();
                }
            }
        }
    }


    private function translate($base, $target, $text) {
        if(strpos($text, "NoTrans") !== false) {
            return $text;
        }
        $tr =  Utils::postURL("http://mc-pe.ga/translate/translate.php", ["from"=>$base,"to"=>$target,"text"=>$text], 5000);
        if(strpos($tr, "TranslateEExeption") !== false) {
            if(!isset($this->limit)) {
                $this->getLogger()->info("It seems like @Ad5001's char limit has been reached. Next mounth, it will be reseted next mounth. Please be patient :).");
                $this->limit = true;
            }
            return $text;
        } else {
            if($tr !== "") {
                return $tr;
            } else {
                return $text;
            }
        }
    }


    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        switch($cmd->getName()){
            case "translate":
            $sender->sendMessage($this->translate($args[0], $args[1], $args[2]));
            break;
        }
     return false;
    }
}