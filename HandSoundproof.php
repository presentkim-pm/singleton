<?php

/**
 * HandSoundproof plugin for PocketMine-MP
 * Copyright (C) 2018 PresentKim <https://github.com/PMMPPlugin>
 *
 * The AGPL license differs from the other GNU licenses in that it was built for network software.
 * You can distribute modified versions if you keep track of the changes and the date you made them.
 * As per usual with GNU licenses, you must license derivatives under AGPL.
 * It provides the same restrictions and freedoms as the GPLv3 but with an additional clause which makes it so that source code must be distributed along with web publication.
 * Since web sites and services are never distributed in the traditional sense, the AGPL is the GPL of the web.
 *
 * @name        HandSoundproof
 * @main        presentkim\singleton\HandSoundproof
 * @version     1.0.0
 * @api         3.0.0-ALPHA10
 * @description Mute the hand sounds
 * @author      PresentKim
 */

namespace presentkim\singleton {

    use pocketmine\event\Listener;
    use pocketmine\event\server\DataPacketReceiveEvent;
    use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
    use pocketmine\plugin\PluginBase;

    class HandSoundproof extends PluginBase implements Listener{

        public function onEnable(){
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        /**
         * @priority HIGHEST
         *
         * @param DataPacketReceiveEvent $event
         */
        public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) : void{
            $pk = $event->getPacket();
            if ($pk instanceof LevelSoundEventPacket) {
                if ($pk->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE || $pk->sound === LevelSoundEventPacket::SOUND_ATTACK_STRONG) {
                    $event->setCancelled(true);
                }
            }
        }
    }
}