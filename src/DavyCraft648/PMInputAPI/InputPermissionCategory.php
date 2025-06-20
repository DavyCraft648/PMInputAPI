<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

enum InputPermissionCategory: int{
	/**
	 * Player input relating to camera movement.
	 */
	case Camera = 1;
	/**
	 * Player input relating to all player movement. Disabling this is equivalent to disabling jump, sneak, lateral movement, mount, and dismount.
	 */
	case Movement = 2;
	/**
	 * Player input for moving laterally in the world. This would be WASD on a keyboard or the movement joystick on gamepad or touch.
	 */
	case LateralMovement = 4;
	/**
	 * Player input relating to sneak. This also affects flying down.
	 */
	case Sneak = 5;
	/**
	 * Player input relating to jumping. This also affects flying up.
	 */
	case Jump = 6;
	/**
	 * Player input relating to mounting vehicles.
	 */
	case Mount = 7;
	/**
	 * Player input relating to dismounting. When disabled, the player can still dismount vehicles by other means, for example on horses players can still jump off and in boats players can go into another boat.
	 */
	case Dismount = 8;
	/**
	 * Player input relating to moving the player forward.
	 */
	case MoveForward = 9;
	/**
	 * Player input relating to moving the player backward.
	 */
	case MoveBackward = 10;
	/**
	 * Player input relating to moving the player left.
	 */
	case MoveLeft = 11;
	/**
	 * Player input relating to moving the player right.
	 */
	case MoveRight = 12;
}