<?php

/**
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the MIT License. see <https://opensource.org/licenses/MIT>.
 *
 *
 * @author      PresentKim (debe3721@gmail.com)
 * @link        https://github.com/PresentKim
 * @license     https://opensource.org/licenses/MIT MIT License
 *
 * @name        InstantFurnace
 * @main        kim\present\singleton\InstantFurnace
 * @version     1.0.0
 * @api         3.0.0-ALPHA11
 * @description Instant furnace
 *
 * @DEPRECATED  : Tile->namedtag was removed (Accoding to https://github.com/pmmp/PocketMine-MP/commit/fa21cd96c570a19dd4db3204779e24a163652f28)
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton {

	use pocketmine\event\inventory\{FurnaceBurnEvent, FurnaceSmeltEvent};
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
