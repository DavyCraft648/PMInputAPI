<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\UpdateClientInputLocksPacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\ObjectSet;

final class InputManager{

	/** @phpstan-var ObjectSet<\Closure(Player $player, InputPermissionCategory $category, bool $enabled) : void> */
	public readonly ObjectSet $permissionChangeListeners;
	/** @phpstan-var ObjectSet<\Closure(Player $player, int $previousInputModeUsed, int $newInputModeUsed) : void> */
	public readonly ObjectSet $inputModeChangeListeners;
	/** @phpstan-var ObjectSet<\Closure(Player $player, InputButton $button, ButtonState $newButtonState) : void> */
	public readonly ObjectSet $buttonInputListeners;

	private \WeakMap $players;

	public function __construct(Plugin $plugin){
		$this->permissionChangeListeners = new ObjectSet();
		$this->inputModeChangeListeners = new ObjectSet();
		$this->buttonInputListeners = new ObjectSet();
		$this->players = new \WeakMap();
		Server::getInstance()->getPluginManager()->registerEvent(PlayerLoginEvent::class, function(PlayerLoginEvent $event): void{
			$player = $event->getPlayer();
			$session = $this->getPlayer($player);
			$extraData = $player->getPlayerInfo()->getExtraData();
			$session->inputInfo->__setLastInputModeUsed($extraData["CurrentInputMode"]);
		}, EventPriority::MONITOR, $plugin);
		Server::getInstance()->getPluginManager()->registerEvent(DataPacketReceiveEvent::class, function(DataPacketReceiveEvent $event): void{
			$packet = $event->getPacket();
			if($packet instanceof PlayerAuthInputPacket){
				$session = $this->getPlayer($player = $event->getOrigin()->getPlayer());
				if($session->inputInfo->getLastInputModeUsed() !== $packet->getInputMode()){
					$this->__onInputModeChange($player, $session->inputInfo->getLastInputModeUsed(), $packet->getInputMode());
					$session->inputInfo->__setLastInputModeUsed($packet->getInputMode());
				}
				$inputFlags = $packet->getInputFlags();
				$session->inputInfo->__setTouchOnlyAffectsHotbar($inputFlags->get(PlayerAuthInputFlags::IS_HOTBAR_ONLY_TOUCH));
				foreach([PlayerAuthInputFlags::JUMP_CURRENT_RAW => InputButton::Jump, PlayerAuthInputFlags::SNEAK_CURRENT_RAW => InputButton::Sneak] as $flagIndex => $button){
					$flag = $inputFlags->get($flagIndex);
					if($flag !== ($session->inputInfo->getButtonState($button) === ButtonState::Pressed)){
						$this->__onButtonInput($player, $button, $flag ? ButtonState::Pressed : ButtonState::Released);
					}
					$session->inputInfo->__setPressedState($button, $flag);
				}
				$session->inputInfo->__setMovementVector($packet->getRawMove());
			}
		}, EventPriority::MONITOR, $plugin);
		Server::getInstance()->getPluginManager()->registerEvent(DataPacketSendEvent::class, function(DataPacketSendEvent $event): void{
			foreach($event->getPackets() as $packet){
				if($packet instanceof UpdateClientInputLocksPacket){
					foreach($event->getTargets() as $target){
						$player = $target->getPlayer();
						$session = $this->getPlayer($player);
						$saved = $session->inputPermissions->__getLockComponentData();
						if($saved !== $packet->getFlags()){
							//set by another plugin
							foreach(InputPermissionCategory::cases() as $category){
								$bit = 1 << $category->value;
								$new = ($packet->getFlags() & $bit) !== $bit;
								if($session->inputPermissions->isPermissionCategoryEnabled($category) !== $new){
									$this->__onPermissionChange($player, $category, $new);
								}
							}
							$session->inputPermissions->__setLockComponentData($packet->getFlags());
						}
					}
				}
			}
		}, EventPriority::MONITOR, $plugin);
	}

	public function getPlayer(Player $player): PlayerSession{
		if($this->players->offsetExists($player)){
			return $this->players[$player];
		}
		return $this->players[$player] = new PlayerSession($player->getUniqueId()->getBytes());
	}

	public function __onPermissionChange(Player $player, InputPermissionCategory $category, bool $enabled): void{
		foreach($this->permissionChangeListeners as $permissionChangeListener){
			$permissionChangeListener($player, $category, $enabled);
		}
	}

	private function __onInputModeChange(Player $player, int $previousInputModeUsed, int $newInputModeUsed): void{
		foreach($this->inputModeChangeListeners as $inputModeChangeListener){
			$inputModeChangeListener($player, $previousInputModeUsed, $newInputModeUsed);
		}
	}

	private function __onButtonInput(Player $player, InputButton $button, ButtonState $newButtonState): void{
		foreach($this->buttonInputListeners as $buttonInputListener){
			$buttonInputListener($player, $button, $newButtonState);
		}
	}
}