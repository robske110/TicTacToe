# TicTacToe
A PocketMine game plugin which provides a way to play TicTacToe with the help of item frames.

**Note: This plugin was initially written in 1.5hours (as a small challenge for myself), but since then it has evolved into a useful and working plugin.**

## Usage:
Anyone with the permission `TicTacToe.createArenas` can create an Arena with the "tictactoe arenacreate" command.
It will ask you to tap the lower left and then the upper right corner. Don't dare to get this wrong, I have no idea what will happen.

Now create a sign where the first line is [TTT] (you can create multiple signs and the content of the remaining 3 lines can be anything). Every player clicking on it will get put into the queue (2 players are required for one game).

## API:
**This plugin does provide quite useful API, which is mostly home in GameManager and PlayerManager.**

You can easily add and remove players from the queue, but you can also create custom games with specific Arenas, add Arenas for use by the queue and much more!

Please always check if you have the right API version before doing anything with TTT's API functions.

Example:
```php
/** @var robske_110\TTT\TicTacToe $ticTacToe */
if(!$ticTacToe->isCompatible("0.1.0")){
   	$this->getLogger()->critical("Your version of TicTacToe is not compatible with this plugin);
	$this->getServer()->getPluginManager()->disablePlugin($this);
	return;
}
```

I'm too lazy to write a proper API documentation for such a small plugin. There are a lot of phpdocs which will help you :) [And function names also give hints!]