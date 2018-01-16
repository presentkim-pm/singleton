<?php

/**
 * ProtectItemFrame plugin for PocketMine-MP
 * Copyright (C) 2018 PresentKim <https://github.com/PMMPPlugin>
 *
 * The AGPL license differs from the other GNU licenses in that it was built for network software.
 * You can distribute modified versions if you keep track of the changes and the date you made them.
 * As per usual with GNU licenses, you must license derivatives under AGPL.
 * It provides the same restrictions and freedoms as the GPLv3 but with an additional clause which makes it so that source code must be distributed along with web publication.
 * Since web sites and services are never distributed in the traditional sense, the AGPL is the GPL of the web.
 *
 * @name        ProtectItemFrame
 * @main        presentkim\singleton\ProtectItemFrame
 * @version     1.0.1
 * @api         3.0.0-ALPHA10
 * @description Protect item frame by stick
 * @author      PresentKim
 */


namespace presentkim\singleton {

    use pocketmine\event\{
      Listener, player\PlayerInteractEvent
    };
    use pocketmine\{
      item\Item, nbt\tag\ByteTag, permission\Permission, plugin\PluginBase, tile\ItemFrame, utils\TextFormat
    };

    class ProtectItemFrame extends PluginBase implements Listener{

        public const PROTECTED_NONE = 0;

        public const PROTECTED_DROP = 1;

        public const PROTECTED_ROTATE = 2;

        public function onEnable(){
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            try{
                Permission::loadPermission('itemframe.protect', [
                  'description' => 'Itemframe protect',
                  'children'    => [
                    'itemframe.protect.make'   => ['description' => 'Make itemframe protect'],
                    'itemframe.protect.drop'   => ['description' => 'Drop itemframe item'],
                    'itemframe.protect.rotate' => ['description' => 'Rotate itemframe item'],
                  ],
                ]);
            } catch (\Exception $e){
            }
        }

        /**
         * @priority HIGHEST
         *
         * @param PlayerInteractEvent $event
         */
        public function onPlayerInteractEvent(PlayerInteractEvent $event){
            if (!$event->isCancelled()) {
                $block = $event->getBlock();
                $tile = $block->getLevel()->getTile($block);
                if ($tile instanceof ItemFrame && $tile->hasItem()) {
                    $protected = $tile->namedtag->getTagValue('itemframe-protect', ByteTag::class, 0, true);

                    $player = $event->getPlayer();

                    if ($player->getInventory()->getItemInHand()->getId() === Item::STICK) {
                        if ($player->hasPermission('itemframe.protect.make')) {
                            $tile->namedtag->setTagValue('itemframe-protect', ByteTag::class, $protected = ++$protected % 3, true);
                            if ($protected === self::PROTECTED_DROP) {
                                $player->sendMessage(TextFormat::GREEN . '[ProtectItemFrame] protected drop');
                            } elseif ($protected === self::PROTECTED_ROTATE) {
                                $player->sendMessage(TextFormat::GREEN . '[ProtectItemFrame] protected drop & rotate');
                            } else {
                                $player->sendMessage(TextFormat::DARK_GREEN . '[ProtectItemFrame] unprotected');
                            }
                        } else {
                            $player->sendMessage(TextFormat::RED . '[ProtectItemFrame] You don\'t have permission');
                        }
                        $event->setCancelled(true);
                    } elseif ($protected === self::PROTECTED_DROP && !$player->hasPermission('itemframe.protect.drop') && $event->getAction() === PlayerInteractEvent::LEFT_CLICK_BLOCK || $protected === self::PROTECTED_ROTATE && !$player->hasPermission('itemframe.protect.rotate')) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
    }
}