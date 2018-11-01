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
 * @name        RubberBand
 * @main        kim\present\singleton\RubberBand
 * @version     1.0.0
 * @api         4.0.0
 * @description Set max players to player count + 1
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

namespace kim\present\singleton {

	use pocketmine\event\Listener;
	use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent};
	use pocketmine\plugin\PluginBase;
	use pocketmine\Server;

	class RubberBand extends PluginBase implements Listener{
		/** @var \ReflectionProperty */
		private $reflectionProperty;

		/**
		 * Called when the plugin is loaded, before calling onEnable()
		 *
		 * @throws \ReflectionException
		 */
		public function onLoad() : void{
			$reflectionClass = new \ReflectionClass(Server::class);
			$this->reflectionProperty = $reflectionClass->getProperty("maxPlayers");
			$this->reflectionProperty->setAccessible(true);
		}

		/**
		 * Called when the plugin is enabled
		 */
		public function onEnable() : void{
			$this->recalculateMaxPlayers(1);

			$this->getServer()->getPluginManager()->registerEvents($this, $this);
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param PlayerJoinEvent $event
		 */
		public function onPlayerJoinEvent(PlayerJoinEvent $event) : void{
			$this->recalculateMaxPlayers(1);
		}

		/**
		 * @priority HIGHEST
		 *
		 * @param PlayerQuitEvent $event
		 */
		public function onPlayerQuitEvent(PlayerQuitEvent $event) : void{
			$this->recalculateMaxPlayers(0);
		}

		/**
		 * Set `Server::$maxPlayers` to `{PLAYER_COUNT} + $addition`
		 *
		 * @param int $addition
		 */
		public function recalculateMaxPlayers(int $addition) : void{
			$server = $this->getServer();
			$this->reflectionProperty->setValue($server, count($server->getOnlinePlayers()) + $addition);
		}
	}
}
