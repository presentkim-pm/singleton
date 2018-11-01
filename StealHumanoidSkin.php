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
 * @name        StealHumanoidSkin
 * @main        kim\present\singleton\humanoid\StealHumanoidSkin
 * @version     1.0.0
 * @api         3.0.0-ALPHA11
 * @description Stil humanoid's skin
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton\humanoid {

	use kim\present\humanoid\event\PlayerClickHumanoidEvent;
	use pocketmine\entity\Skin;
	use pocketmine\event\Listener;
	use pocketmine\plugin\PluginBase;

	class StealHumanoidSkin extends PluginBase implements Listener{

		public function onEnable() : void{
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param PlayerClickHumanoidEvent $event
		 */
		public function onPlayerClickHumanoidEvent(PlayerClickHumanoidEvent $event){
			if(!$event->isCancelled()){
				$player = $event->getPlayer();
				$humanoid = $event->getHumanoid();
				$skin = $player->getSkin();
				if($event->getAction() === PlayerClickHumanoidEvent::LEFT_CLICK){
					$skin = new Skin('humanoid', $humanoid->getSkin()->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData());
				}else{
					$skin = new Skin('humanoid', $skin->getSkinData(), $skin->getCapeData(), $humanoid->getSkin()->getGeometryName(), $humanoid->getSkin()->getGeometryData());
				}
				$player->setSkin($skin);
				$player->sendSkin();
			}
		}
	}
}
