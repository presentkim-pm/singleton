<?php

/**
 * @name        StealHumanoidSkin
 * @main        presentkim\singleton\humanoid\StealHumanoidSkin
 * @version     1.0.0
 * @api         3.0.0-ALPHA11
 * @author      PresentKim
 */

namespace presentkim\singleton\humanoid {

    use pocketmine\entity\Skin;
    use pocketmine\event\Listener;
    use pocketmine\plugin\PluginBase;
    use presentkim\humanoid\event\PlayerClickHumanoidEvent;

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
            if (!$event->isCancelled()) {
                $player = $event->getPlayer();
                $humanoid = $event->getHumanoid();
                $skin = $player->getSkin();
                if ($event->getAction() === PlayerClickHumanoidEvent::LEFT_CLICK) {
                    $skin = new Skin('humanoid', $humanoid->getSkin()->getSkinData(), $skin->getCapeData(), $skin->getGeometryName(), $skin->getGeometryData());
                } else {
                    $skin = new Skin('humanoid', $skin->getSkinData(), $skin->getCapeData(), $humanoid->getSkin()->getGeometryName(), $humanoid->getSkin()->getGeometryData());
                }
                $player->setSkin($skin);
                $player->sendSkin();
            }
        }
    }
}