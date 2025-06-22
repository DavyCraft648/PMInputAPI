<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\types\InputMode;

/**
 * Contains the input information for a client instance.
 */
class InputInfo{

	private int $lastInputModeUsed;
	private bool $touchOnlyAffectsHotbar;
	private int $pressedState = 0;
	private Vector2 $movementVector;

	public function __construct(){
		$this->movementVector = new Vector2(0, 0);
	}

	/**
	 * The last input mode used by the player.
	 * @see InputMode
	 */
	public function getLastInputModeUsed(): int{
		return $this->lastInputModeUsed;
	}

	/**
	 * Whether the player touch input only affects the touchbar or not.
	 */
	public function isTouchOnlyAffectsHotbar(): bool{
		return $this->touchOnlyAffectsHotbar;
	}

	/**
	 * Read raw player button inputs
	 */
	public function getButtonState(InputButton $button): ButtonState{
		$bit = 1 << match ($button) {
				InputButton::Jump => 1,
				InputButton::Sneak => 2
			};
		return ($this->pressedState & $bit) === $bit ? ButtonState::Pressed : ButtonState::Released;
	}

	/**
	 * Read raw movement values
	 */
	public function getMovementVector(): Vector2{
		return $this->movementVector;
	}

	public function __setLastInputModeUsed(int $lastInputModeUsed): void{
		$this->lastInputModeUsed = $lastInputModeUsed;
	}

	public function __setTouchOnlyAffectsHotbar(bool $touchOnlyAffectsHotbar): void{
		$this->touchOnlyAffectsHotbar = $touchOnlyAffectsHotbar;
	}

	public function __setPressedState(InputButton $button, bool $state): void{
		$bit = 1 << match ($button) {
				InputButton::Jump => 1,
				InputButton::Sneak => 2
			};
		$this->pressedState = $state ? $this->pressedState | $bit : $this->pressedState & ~$bit;
	}

	public function __setMovementVector(Vector2 $movementVector): void{
		$this->movementVector = $movementVector;
	}

	public function __destruct(){
		unset($this->movementVector);
	}
}