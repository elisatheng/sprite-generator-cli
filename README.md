# Sprite Generator CLI
A sprite generator in command-line interface


## User commands

### Name
sprite_generator.php

### Synopsis
```
php sprite_generator.php [options] <folder>
```

### Description
Concatenate all PNG files inside a folder in one sprite.

### Options

* -r \<folder> | --recursive=\<folder>   
Look for png files into the assets_folder passed as argument and all of its subdirectories
  

* -i \<name> | --output-image=\<name>     
Name of the generated image. If blank, the default name is 'sprite.png'


## Examples

```
php sprite_generator.php images
php sprite_generator.php -r images
php sprite_generator.php --recursive=images/pokemon -i pokemon_sprite
```
