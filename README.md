# TicTacToe
A PocketMine game plugin which provides a way to play TicTacToe with the help of item frames.

**A small warning: This plugin was initially written in 1.5hours, so don't expect much in terms of code sensfullness. It "works".**

## Usage:
Anyone with the permission `TicTacToe.createArenas` can create an Arena with the "tictactoe arenacreate" command.
It will ask you to tap the lower left and then the upper right corner. Don't dare to get this wrong, I have no idea what will happen.

## API:
**This plugin might accidently provide useful API. If, although I highly doubt it you think it is useful, please check if you have the right API version.**

Example:
```php
/** @var robske_110\TTT\TicTacToe $ticTacToe */
if(!$ticTacToe->isCompatible("0.1.0")){
   	$this->getLogger()->critical("Your version of TicTacToe is not compatible with this plugin);
	$this->getServer()->getPluginManager()->disablePlugin($this);
	return;
}
```

If you expected some overview of API functions here, read the secound, **bold**, sentence.