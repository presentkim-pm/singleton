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
 * @name        ProtectItemFrame
 * @main        kim\present\singleton\ProtectItemFrame
 * @version     1.1.0
 * @api         3.0.0-ALPHA11
 * @description Protect item frame by stick
 *
 * @DEPRECATED  : Tile->namedtag was removed (Accoding to https://github.com/pmmp/PocketMine-MP/commit/fa21cd96c570a19dd4db3204779e24a163652f28)
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton {

	use pocketmine\event\Listener;
	use pocketmine\event\player\PlayerInteractEvent;
	use pocketmine\item\Item;
	use pocketmine\nbt\tag\ByteTag;
	use pocketmine\permission\Permission;
	use pocketmine\plugin\PluginBase;
	use pocketmine\tile\ItemFrame;
	use pocketmine\utils\TextFormat;

	class ProtectItemFrame extends PluginBase implements Listener{

		public const PROTECTED_NONE = 0;

		public const PROTECTED_DROP = 1;

		public const PROTECTED_ROTATE = 2;

		public function onEnable() : void{
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			try{
				Permission::loadPermission('itemframe.protect', [
					'description' => 'Itemframe protect',
					'children' => [
						'itemframe.protect.make' => ['description' => 'Make itemframe protect'],
						'itemframe.protect.drop' => ['description' => 'Drop itemframe item'],
						'itemframe.protect.rotate' => ['description' => 'Rotate itemframe item'],
					],
				]);
			}catch(\Exception $e){
			}
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param PlayerInteractEvent $event
		 */
		public function onPlayerInteractEvent(PlayerInteractEvent $event) : void{
			if(!$event->isCancelled()){
				$block = $event->getBlock();
				$tile = $block->getLevel()->getTile($block);
				if($tile instanceof ItemFrame && $tile->hasItem()){
					$mode = $tile->namedtag->getTagValue('itemframe-protect', ByteTag::class, 0, true);

					$player = $event->getPlayer();

					if($event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK){
						if($player->getInventory()->getItemInHand()->getId() === Item::STICK){
							if($player->hasPermission('itemframe.protect.make')){
								$tile->namedtag->setTagValue('itemframe-protect', ByteTag::class, $mode = ++$mode % 3, true);
								if($mode === self::PROTECTED_DROP){
									$player->sendMessage(TextFormat::GREEN . '[Protect] protected drop');
								}elseif($mode === self::PROTECTED_ROTATE){
									$player->sendMessage(TextFormat::GREEN . '[Protect] protected drop & rotate');
								}else{
									$player->sendMessage(TextFormat::DARK_GREEN . '[Protect] unprotected');
								}
							}else{
								$player->sendMessage(TextFormat::RED . '[Protect] You don\'t have permission');
							}
							$event->setCancelled(true);
						}elseif(($mode === self::PROTECTED_DROP || $mode === self::PROTECTED_ROTATE) && !$player->hasPermission('itemframe.protect.drop')){
							$event->setCancelled(true);
						}
					}elseif($mode === self::PROTECTED_ROTATE && !$player->hasPermission('itemframe.protect.rotate')){
						$event->setCancelled(true);
					}
				}
			}
		}
	}
}
