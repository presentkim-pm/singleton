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
 * @api         4.0.0
 * @description Instant furnace
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton {

	use pocketmine\event\inventory\FurnaceBurnEvent;
	use pocketmine\event\inventory\FurnaceSmeltEvent;
	use pocketmine\event\Listener;
	use pocketmine\plugin\PluginBase;
	use pocketmine\tile\Furnace;

	class InstantFurnace extends PluginBase implements Listener{
		/** @var \ReflectionProperty */
		private $burnTimeProperty, $cookTimeProperty;

		/**
		 * Called when the plugin is loaded, before calling onEnable()
		 *
		 * @throws \ReflectionException
		 */
		public function onLoad() : void{
			$reflectionClass = new \ReflectionClass(Furnace::class);
			$this->cookTimeProperty = $reflectionClass->getProperty("cookTime");
			$this->cookTimeProperty->setAccessible(true);
			$this->burnTimeProperty = $reflectionClass->getProperty("burnTime");
			$this->burnTimeProperty->setAccessible(true);
		}

		/**
		 * Called when the plugin is enabled
		 */
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
					$this->cookTimeProperty->setValue($furnace, 399);
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
				if(($burnTime = $this->burnTimeProperty->getValue($furnace)) > 199){
					$this->burnTimeProperty->setValue($furnace, $burnTime - 199);
					$this->cookTimeProperty->setValue($furnace, 399);
				}elseif($furnace->getInventory()->getFuel()->getFuelTime() > 200){
					$this->burnTimeProperty->setValue($furnace, 1);
					$this->cookTimeProperty->setValue($furnace, $this->cookTimeProperty->getValue($furnace) + $burnTime);
				}
			}
		}
	}
}
