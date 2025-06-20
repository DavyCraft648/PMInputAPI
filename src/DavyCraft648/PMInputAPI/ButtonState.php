<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

/**
 * The state of a button on a keyboard, controller, or touch interface.
 */
enum ButtonState{
	case Pressed;
	case Released;
}