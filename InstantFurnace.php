<?php

/**
 * InstantFurnace plugin for PocketMine-MP
 * Copyright (C) 2018 PresentKim <https://github.com/PMMPPlugin>
 *
 * The AGPL license differs from the other GNU licenses in that it was built for network software.
 * You can distribute modified versions if you keep track of the changes and the date you made them.
 * As per usual with GNU licenses, you must license derivatives under AGPL.
 * It provides the same restrictions and freedoms as the GPLv3 but with an additional clause which makes it so that source code must be distributed along with web publication.
 * Since web sites and services are never distributed in the traditional sense, the AGPL is the GPL of the web.
 *
 * @name        InstantFurnace
 * @main        presentkim\singleton\InstantFurnace
 * @version     1.0.0
 * @api         3.0.0-ALPHA10
 * @description Instant furnace
 * @author      PresentKim
 */

namespace presentkim\singleton {

    use pocketmine\event\{
      inventory\FurnaceBurnEvent, Listener, inventory\FurnaceSmeltEvent
    };
    use pocketmine\{
      item\Item, nbt\tag\ByteTag, plugin\PluginBase, tile\Furnace, tile\ItemFrame, utils\TextFormat
    };

    class InstantFurnace extends PluginBase implements Listener{

        public function onEnable(){
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        /**
         * @priority HIGHEST
         *
         * @param FurnaceBurnEvent $event
         */
        public function onFurnaceBurnEvent(FurnaceBurnEvent $event){
            if (!$event->isCancelled()) {
                if (($burnTime = $event->getBurnTime()) > 200) {
                    $furnace = $event->getFurnace();
                    $event->setBurnTime($burnTime - 200);
                    $furnace->namedtag->setShort(Furnace::TAG_COOK_TIME, 400);
                }
            }
        }

        /**
         * @priority HIGHEST
         *
         * @param FurnaceSmeltEvent $event
         */
        public function onFurnaceSmeltEvent(FurnaceSmeltEvent $event){
            if (!$event->isCancelled()) {
                $furnace = $event->getFurnace();
                if (($burnTime = $furnace->namedtag->getShort(Furnace::TAG_BURN_TIME)) > 200) {
                    $furnace->namedtag->setShort(Furnace::TAG_BURN_TIME, $burnTime - 200);
                    $furnace->namedtag->setShort(Furnace::TAG_COOK_TIME, 400);
                }
            }
        }
    }
}