<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

final readonly class PlayerSession{

	public PlayerInputPermissions $inputPermissions;
	public inputInfo $inputInfo;

	public function __construct(string $rawUUID){
		$this->inputPermissions = new PlayerInputPermissions($rawUUID);
		$this->inputInfo = new InputInfo();
	}
}