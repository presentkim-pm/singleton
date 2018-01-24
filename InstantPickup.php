<?php

/**
 * InstantPickup plugin for PocketMine-MP
 * Copyright (C) 2018 PresentKim <https://github.com/PMMPPlugin>
 *
 * The AGPL license differs from the other GNU licenses in that it was built for network software.
 * You can distribute modified versions if you keep track of the changes and the date you made them.
 * As per usual with GNU licenses, you must license derivatives under AGPL.
 * It provides the same restrictions and freedoms as the GPLv3 but with an additional clause which makes it so that source code must be distributed along with web publication.
 * Since web sites and services are never distributed in the traditional sense, the AGPL is the GPL of the web.
 *
 * @name        InstantPickup
 * @main        presentkim\singleton\InstantPickup
 * @version     1.0.0
 * @api         3.0.0-ALPHA10
 * @description Instant pickup
 * @author      PresentKim
 */

namespace presentkim\singleton {

    use pocketmine\event\{
      Listener, block\BlockBreakEvent
    };
    use pocketmine\{
      plugin\PluginBase
    };

    class InstantPickup extends PluginBase implements Listener{

        public function onEnable(){
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        /**
         * @priority HIGHEST
         *
         * @param BlockBreakEvent $event
         */
        public function onBlockBreakEvent(BlockBreakEvent $event){
            if (!$event->isCancelled()) {
                $player = $event->getPlayer();
                if ($player->isSurvival()) {
                    $inventory = $player->getInventory();
                    $drops = [];
                    foreach ($event->getDrops() as $i => $drop) {
                        if ($inventory->canAddItem($drop)) {
                            $inventory->addItem($drop);
                        } else {
                            $drops[] = $drop;
                        }
                    }
                    $event->setDrops($drops);
                }
            }
        }
    }
}