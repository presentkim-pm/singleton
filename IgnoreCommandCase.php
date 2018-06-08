<?php

/**
 * IgnoreCommandCase plugin for PocketMine-MP
 * Copyright (C) 2018 PresentKim <https://github.com/PMMPPlugin>
 *
 * The AGPL license differs from the other GNU licenses in that it was built for network software.
 * You can distribute modified versions if you keep track of the changes and the date you made them.
 * As per usual with GNU licenses, you must license derivatives under AGPL.
 * It provides the same restrictions and freedoms as the GPLv3 but with an additional clause which makes it so that source code must be distributed along with web publication.
 * Since web sites and services are never distributed in the traditional sense, the AGPL is the GPL of the web.
 *
 * @name        IgnoreCommandCase
 * @main        kim\present\singleton\IgnoreCommandCase
 * @version     1.0.0
 * @api         3.0.0-ALPHA11
 * @description ignore command case
 * @author      PresentKim
 */


namespace kim\present\singleton {

    use pocketmine\{
      event\Listener, event\player\PlayerCommandPreprocessEvent, event\server\RemoteServerCommandEvent, event\server\ServerCommandEvent, plugin\PluginBase
    };

    class IgnoreCommandCase extends PluginBase implements Listener{

        public function onEnable() : void{
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        /**
         * @priority LOWEST
         *
         * @param PlayerCommandPreprocessEvent $event
         */
        public function onPlayerCommandPreprocessEvent(PlayerCommandPreprocessEvent $event) : void{
            if (strpos($message = $event->getMessage(), "/") === 0) {
                $event->setMessage("/{$this->getCommand($this->getCommand(substr($message, 1)))}");
            }
        }

        /**
         * @priority LOWEST
         *
         * @param ServerCommandEvent $event
         */
        public function onServerCommandEvent(ServerCommandEvent $event) : void{
            $event->setCommand($this->getCommand($event->getCommand()));
        }

        /**
         * @priority LOWEST
         *
         * @param RemoteServerCommandEvent $event
         */
        public function onRemoteServerCommandEvent(RemoteServerCommandEvent $event) : void{
            $event->setCommand($this->getCommand($event->getCommand()));
        }


        /**
         * @param string $command
         *
         * @return string
         */
        public function getCommand(string $command) : string{
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