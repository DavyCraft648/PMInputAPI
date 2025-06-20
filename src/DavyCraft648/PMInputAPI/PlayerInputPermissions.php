<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

use pocketmine\network\mcpe\protocol\UpdateClientInputLocksPacket;
use pocketmine\Server;

/**
 * Contains APIs to enable/disable player input permissions.
 */
class PlayerInputPermissions{

	public function __construct(private readonly string $rawUUID, private int $lockComponentData = 0){}

	/**
	 * Returns true if an input permission is enabled.
	 */
	public function isPermissionCategoryEnabled(InputPermissionCategory $permissionCategory): bool{
		$bit = 1 << $permissionCategory->value;
		return ($this->lockComponentData & $bit) !== $bit;
	}

	/**
	 * Enable or disable an input permission. When enabled the input will work, when disabled will not work.
	 */
	public function setPermissionCategory(InputPermissionCategory $permissionCategory, bool $isEnabled): void{
		$bit = 1 << $permissionCategory->value;
		$this->lockComponentData = $isEnabled ? $this->lockComponentData & ~$bit : $this->lockComponentData | $bit;
		$player = Server::getInstance()->getPlayerByRawUUID($this->rawUUID);
		if($player === null || !$player->isConnected()){
			throw new \RuntimeException("Player offline");
		}

		$player->getNetworkSession()->sendDataPacket(UpdateClientInputLocksPacket::create($this->lockComponentData, $player->getPosition()->add(0, 1.62, 0)));
		PMInputAPI::getInputManager()->__onPermissionChange($player, $permissionCategory, $isEnabled);
	}

	public function __getLockComponentData(): int{
		return $this->lockComponentData;
	}

	public function __setLockComponentData(int $lockComponentData): void{
		$this->lockComponentData = $lockComponentData;
	}
}