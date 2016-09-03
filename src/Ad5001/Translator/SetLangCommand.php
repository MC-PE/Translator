<?php
namespace Ad5001\Translator;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\utils\Config;


class SetLangCommand extends Command {

    public function __construct(Main $main) {
        parent::__construct($main->getConfig()->get("SetLangCommand"), "Set your language so everythink will be translated into it !", "/" . $main->getConfig()->get("SetLangCommand") ." <lang>");
        $this->main = $main;
    }


    public function execute(CommandSender $sender, $label, array $args) {
        if(isset($args[0])) {
            if($sender instanceof \pocketmine\Player) {
                if($this->main->setLang($args[0])) {
                    $sender->sendMessage("Your lang has been set to " . $args[0] . ".");
                } else {
                    $sender->sendMessage("$args[0] is not a valid language ! Please use ISO 639-1 languages.");
                }
            } else {
                $sender->sendMessage("This command is only avaliable in game.");
            }
        } else {
            return false;
        }
    }
}