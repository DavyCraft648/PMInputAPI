<?php
declare(strict_types=1);

namespace DavyCraft648\PMInputAPI;

use pocketmine\plugin\Plugin;

final class PMInputAPI{
	private function __construct(){}

	private static ?Plugin $plugin = null;
	private static \PrefixedLogger $logger;
	private static InputManager $inputManager;

	public static function register(Plugin $plugin): void{
		if(self::$plugin instanceof Plugin){
			throw new \InvalidArgumentException("{$plugin->getName()} tries to register PMInputAPI that has been registered by " . self::$plugin->getName());
		}

		self::$plugin = $plugin;
		self::$logger = new \PrefixedLogger($plugin->getLogger(), "PMInputAPI");
		self::$inputManager = new InputManager($plugin);

		if(__NAMESPACE__ === "DavyCraft648\\PMInputAPI"){
			self::getLogger()->notice("It is recommended to shade virions, go to https://poggit.pmmp.io/virion to see virion documentation.");
		}
	}

	public static function getPlugin(): Plugin{
		return self::$plugin ?? throw new \LogicException("PMInputAPI has not been registered");
	}

	public static function getLogger(): \PrefixedLogger{
		return self::$logger ?? throw new \LogicException("PMInputAPI has not been registered");
	}

	public static function getInputManager(): InputManager{
		return self::$inputManager ?? throw new \LogicException("PMInputAPI has not been registered");
	}
}