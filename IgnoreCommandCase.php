<?php

/**
 * @name        IgnoreCommandCase
 * @main        presentkim\singleton\IgnoreCommandCase
 * @version     1.0.0
 * @api         3.0.0-ALPHA10
 * @description ignore command case
 * @author      PresentKim
 */


namespace presentkim\singleton {

    use pocketmine\{
      event\Listener, event\player\PlayerCommandPreprocessEvent, event\server\RemoteServerCommandEvent, event\server\ServerCommandEvent, plugin\PluginBase
    };

    class IgnoreCommandCase extends PluginBase implements Listener{

        public function onEnable(){
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        /**
         * @priority LOWEST
         *
         * @param PlayerCommandPreprocessEvent $event
         */
        public function onPlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event){
            if (strpos($message = $event->getMessage(), "/") === 0) {
                $event->setMessage("/{$this->getCommand($this->getCommand(substr($message, 1)))}");
            }
        }

        /**
         * @priority LOWEST
         *
         * @param ServerCommandEvent $event
         */
        public function onServerCommandEvent(ServerCommandEvent $event){
            $event->setCommand($this->getCommand($event->getCommand()));
        }

        /**
         * @priority LOWEST
         *
         * @param RemoteServerCommandEvent $event
         */
        public function onRemoteServerCommandEvent(RemoteServerCommandEvent $event){
            $event->setCommand($this->getCommand($event->getCommand()));
        }


        /**
         * @param string $command
         *
         * @return string
         */
        public function getCommand(string $command){
            $explode = explode(" ", $command);
            $commands = $this->getServer()->getCommandMap()->getCommands();
            if (isset($commands[$explode[0]])) {
                return $command;
            } else {
                foreach ($this->getServer()->getCommandMap()->getCommands() as $key => $value) {
                    if (strcasecmp($explode[0], $key) === 0) {
                        $explode[0] = $key;
                        break;
                    }
                }
            }
            return implode(" ", $explode);
        }
    }
}