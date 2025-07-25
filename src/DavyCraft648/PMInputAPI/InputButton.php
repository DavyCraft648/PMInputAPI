<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

/**
 * All the different input buttons that are supported.
 */
enum InputButton{
	/**
	 * This is mapped to the 'Jump' button on controllers, keyboards, and touch interfaces.
	 */
	case Jump;
	/**
	 * This is mapped to the 'Sneak' button on controllers, keyboards, and touch interfaces. By default, this is shift on a keyboard or B on an Xbox controller. On touch interfaces this will only be pressed for 1 tick or less and then it will be released immediately even if the player holds their finger down. Dismounting a horse or exiting a boat will not send a Sneak button change event.
	 */
	case Sneak;
}