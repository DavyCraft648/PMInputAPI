# PMInputAPI Virion

PMInputAPI is a virion for [PocketMine-MP](https://github.com/pmmp/PocketMine-MP) that aims to bring a subset of the
Input API functionality, similar to what is found in Minecraft Bedrock Edition's Script API, to PocketMine plugins. This
virion is designed to provide plugin developers with structured ways to interact with player input, such as movement
controls, button states, and input modes, making it easier to create immersive and responsive experiences.

---

## Features

* **Player Input Permissions Control:** Enable or disable specific player input categories like `Camera`, `Movement`,
  `Jump`, `Sneak`, and more.
* **Player Input Information:** Access details about a player's current input, including their last used `InputMode`,
  whether touch input affects only the hotbar, and the state of `Jump` and `Sneak` buttons.
* **Movement Vector:** Retrieve the raw movement vector of the player.
* **Event Listeners:** Register custom listeners for changes in player input permissions and input mode changes.

---

## How to Use This Virion (For Developers)

As a [**virion**](https://poggit.pmmp.io/virion), PMInputAPI is not a plugin you install directly. Instead, it's a
library that other plugins *include* in their code.

### Including PMInputAPI in Your Plugin

- Using **Composer** (virion v3):

  Add this as a dependency to your Composer project. Open your terminal in your project's root directory and run the
  following command:

  ```bash
  composer require davycraft648/pminputapi
  ```

  After running the command, your `composer.json` file will be updated. You can inspect it to see the new dependency
  listed, similar to this:

  ```json5
  {
    "require": {
      // other dependencies ...
      "davycraft648/pminputapi": "^1.0"
    }
  }
  ```


- Using **Poggit CI** (virion v1):

  Add this to your `.poggit.yml` file:

   ```yaml
   projects:
     YourPlugin:
       libs:
         - src: DavyCraft648/PMInputAPI/PMInputAPI
           version: ^1.0.0
   ```

### Registering the Virion

Your plugin must register the `PMInputAPI` virion during its `onEnable()`. This ensures the necessary managers are
initialized.

```php
<?php
declare(strict_types=1);

namespace your\plugin\namespace;

use DavyCraft648\PMInputAPI\PMInputAPI;
use DavyCraft648\PMInputAPI\InputPermissionCategory;
use DavyCraft648\PMInputAPI\InputButton;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector2;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

class MyPlugin extends PluginBase{

    public function onEnable(): void{
        // Register the PMInputAPI virion
        PMInputAPI::register($this);

        // Example: Disable player movement after a player logs in
        PMInputAPI::getInstance()->getServer()->getPluginManager()->registerEvent(PlayerJoinEvent::class, function(PlayerJoinEvent $event): void{
            $player = $event->getPlayer();
            $playerSession = PMInputAPI::getInputManager()->getPlayer($player);
            $playerSession->inputPermissions->setPermissionCategory(InputPermissionCategory::Movement, false);
            $this->getLogger()->info("Movement disabled for " . $player->getName());
        }, EventPriority::MONITOR, $this);

        // Example: Listen for input mode changes
        PMInputAPI::getInputManager()->inputModeChangeListeners->add(function(Player $player, int $previousInputModeUsed, int $newInputModeUsed): void{
            $this->getLogger()->info($player->getName() . " changed input mode from " . $previousInputModeUsed . " to " . $newInputModeUsed);
        });

        // Example: Check jump button state and movement vector
        $this->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private MyPlugin $plugin;

            public function __construct(MyPlugin $plugin) {
                $this->plugin = $plugin;
            }

            public function onRun(): void {
                foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
                    $session = PMInputAPI::getInputManager()->getPlayer($player);
                    $inputInfo = $session->inputInfo;

                    if ($inputInfo->getButtonState(InputButton::Jump) === \DavyCraft648\PMInputAPI\ButtonState::Pressed) {
                        // $this->plugin->getLogger()->info($player->getName() . " is pressing jump!");
                    }

                    $movementVector = $inputInfo->getMovementVector();
                    if (!$movementVector->equals(new Vector2(0, 0))) {
                        // $this->plugin->getLogger()->info($player->getName() . " is moving: " . $movementVector->__toString());
                    }
                }
            }
        }, 20); // Run every second
    }
}
```

### Accessing Player Input Permissions

You can get a player's input permissions and modify them:

```php
use DavyCraft648\PMInputAPI\PMInputAPI;
use DavyCraft648\PMInputAPI\InputPermissionCategory;
use pocketmine\player\Player;

// Get the PlayerSession for a player
$playerSession = PMInputAPI::getInputManager()->getPlayer($player);

// Disable player movement
$playerSession->inputPermissions->setPermissionCategory(InputPermissionCategory::Movement, false);

// Check if jump permission is enabled
if ($playerSession->inputPermissions->isPermissionCategoryEnabled(InputPermissionCategory::Jump)) {
    // ...
}
```

### Accessing Player Input Information

You can retrieve real-time input information about a player:

```php
use DavyCraft648\PMInputAPI\PMInputAPI;
use DavyCraft648\PMInputAPI\InputButton;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\types\InputMode;

// Get the PlayerSession for a player
$playerSession = PMInputAPI::getInputManager()->getPlayer($player);
$inputInfo = $playerSession->inputInfo;

// Get the last input mode used
$lastInputMode = $inputInfo->getLastInputModeUsed();
if ($lastInputMode === InputMode::KEYBOARD_MOUSE) {
    // ...
}

// Check if the sneak button is pressed
if ($inputInfo->getButtonState(InputButton::Sneak) === \DavyCraft648\PMInputAPI\ButtonState::Pressed) {
    // ...
}

// Get the player's movement vector
$movementVector = $inputInfo->getMovementVector();
// $movementVector is a Vector2 representing raw movement input.
```

---

## License

This project is licensed under the MIT License - see the `LICENSE` file for details.

---