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
 * @name        HandSoundproof
 * @main        kim\present\singleton\HandSoundproof
 * @version     1.0.0
 * @api         4.0.0
 * @description Mute the hand sounds
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton {

	use pocketmine\event\Listener;
	use pocketmine\event\server\DataPacketReceiveEvent;
	use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
	use pocketmine\plugin\PluginBase;

	class HandSoundproof extends PluginBase implements Listener{
		/**
		 * Called when the plugin is enabled
		 */
		public function onEnable() : void{
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param DataPacketReceiveEvent $event
		 */
		public function onDataPacketReceiveEvent(DataPacketReceiveEvent $event) : void{
			$pk = $event->getPacket();
			if($pk instanceof LevelSoundEventPacket){
				if($pk->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE || $pk->sound === LevelSoundEventPacket::SOUND_ATTACK_STRONG){
					$event->setCancelled(true);
				}
			}
		}
	}
}
