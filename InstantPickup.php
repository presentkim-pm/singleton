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
 * @name        InstantPickup
 * @main        kim\present\singleton\InstantPickup
 * @version     1.0.0
 * @api         4.0.0
 * @description Instant pickup
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton {

	use pocketmine\event\block\BlockBreakEvent;
	use pocketmine\event\Listener;
	use pocketmine\inventory\InventoryHolder;
	use pocketmine\plugin\PluginBase;

	class InstantPickup extends PluginBase implements Listener{

		public function onEnable() : void{
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param BlockBreakEvent $event
		 */
		public function onBlockBreakEvent(BlockBreakEvent $event) : void{
			if(!$event->isCancelled()){
				$player = $event->getPlayer();
				if($player->isSurvival()){
					$inventory = $player->getInventory();
					$drops = [];
					foreach($event->getDrops() as $i => $drop){
						foreach($inventory->addItem($drop) as $i){
							$drops[] = $i;
						}
					}
					$event->setDrops($drops);

					$tile = $player->level->getTile($event->getBlock());
					if($tile instanceof InventoryHolder){
						$tileInventory = $tile->getInventory();
						$items = [];
						foreach($tileInventory->getContents() as $i => $item){
							foreach($inventory->addItem($item) as $i){
								$items[] = $i;
							}
						}
						$tileInventory->setContents($items);
					}
				}
			}
		}
	}
}
