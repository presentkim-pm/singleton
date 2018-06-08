<?php

/**
 * InstantFurnace plugin for PocketMine-MP
 * Copyright (C) 2018 PresentKim <https://github.com/PMMPPlugin>
 *
 * @DEPRECATED  : Tile->namedtag was removed (Accoding to https://github.com/pmmp/PocketMine-MP/commit/fa21cd96c570a19dd4db3204779e24a163652f28)
 *
 * The AGPL license differs from the other GNU licenses in that it was built for network software.
 * You can distribute modified versions if you keep track of the changes and the date you made them.
 * As per usual with GNU licenses, you must license derivatives under AGPL.
 * It provides the same restrictions and freedoms as the GPLv3 but with an additional clause which makes it so that source code must be distributed along with web publication.
 * Since web sites and services are never distributed in the traditional sense, the AGPL is the GPL of the web.
 *
 * @name        InstantFurnace
 * @main        kim\present\singleton\InstantFurnace
 * @version     1.0.0
 * @api         3.0.0-ALPHA11
 * @description Instant furnace
 * @author      PresentKim
 */

namespace kim\present\singleton {

	use pocketmine\event\inventory\{
		FurnaceBurnEvent, FurnaceSmeltEvent
	};
	use pocketmine\event\Listener;
	use pocketmine\plugin\PluginBase;
	use pocketmine\tile\Furnace;

	class InstantFurnace extends PluginBase implements Listener{

		public function onEnable() : void{
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param FurnaceBurnEvent $event
		 */
		public function onFurnaceBurnEvent(FurnaceBurnEvent $event) : void{
			if(!$event->isCancelled()){
				if(($burnTime = $event->getBurnTime()) > 200){
					$furnace = $event->getFurnace();
					$event->setBurnTime($burnTime - 199);
					$furnace->namedtag->setShort(Furnace::TAG_COOK_TIME, 399);
				}
			}
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param FurnaceSmeltEvent $event
		 */
		public function onFurnaceSmeltEvent(FurnaceSmeltEvent $event) : void{
			if(!$event->isCancelled()){
				$furnace = $event->getFurnace();
				if(($burnTime = $furnace->namedtag->getShort(Furnace::TAG_BURN_TIME)) > 199){
					$furnace->namedtag->setShort(Furnace::TAG_BURN_TIME, $burnTime - 199);
					$furnace->namedtag->setShort(Furnace::TAG_COOK_TIME, 399);
				}elseif($furnace->getInventory()->getFuel()->getFuelTime() > 200){
					$furnace->namedtag->setShort(Furnace::TAG_BURN_TIME, 1);
					$furnace->namedtag->setShort(Furnace::TAG_COOK_TIME, $furnace->namedtag->getShort(Furnace::TAG_COOK_TIME) + $burnTime);
				}
			}
		}
	}
}