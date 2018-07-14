<?php

class SpriteGenerator
{
    /** @var array */
    public $args;

    /** @var array */
    private $options = [];

    /** @var array */
    private $opts = [
        "short" => "r:i:s:",
        "long" => ["recursive:", "output-image::", "sort"]
    ];

    function __construct($argv)
    {
        $this->args = $argv;
        $this->options = getopt($this->opts["short"], $this->opts["long"]);
    }

    public function run(): void
    {
        echo '=================' . "\n";
        echo 'Sprite Generator' . "\n";
        echo '=================' . "\n";

        $spriteName = $this->getSpriteName();
        $pngs = $this->listPngs($this->getFolder());

        if (empty($pngs)) {
            exit("RESULT : no png files to generate\n");
        }

        $spriteSizes = $this->getSpriteSizes($pngs);

        if (substr($spriteName, -4) !== ".png") {
            $spriteName .= ".png";
        }

        echo "\nSPRITE NAME : $spriteName\n";

        $this->generateSprite($pngs, $spriteSizes, $spriteName);
    }

    private function getSpriteName(): string
    {
        $spriteName = "sprite.png";

        if (isset($this->options["i"])) {
            $spriteName = $this->options["i"];
        } else if (isset($this->options["output-image"])) {
            $spriteName = $this->options["output-image"];
        }

        if (file_exists($spriteName)) {
            exit("ERROR : sprite filename '$spriteName' already exists\n");
        }

        return $spriteName;
    }

    private function getFolder(): string
    {
        $folder = "";

        if (isset($this->options["r"]) || isset($this->options["recursive"])) {
            if (isset($this->options["r"])) {
                $folder = $this->options["r"];
            } else if (isset($this->options["recursive"])) {
                $folder = $this->options["recursive"];
            }

            return $folder;
        }

        foreach ($this->args as $arg) {
            if (is_dir($arg) && $folder === "") {
                $folder = $arg;
            }
        }

        if ($folder === "") {
            $folder = ".";
        }

        return $folder;
    }

    private function listPngs(string $dir): array
    {
        if (!is_dir($dir) || !($dopen = opendir($dir))) {
            exit("\nERROR : folder '$dir' not valid");
        }

        $pngs = [];

        while (($file = readdir($dopen)) !== false) {
            if ($file === "." || $file === "..") {
                continue;
            }

            $filePath = $dir . DIRECTORY_SEPARATOR . $file;

            if (isset($this->options["r"]) || isset($this->options["recursive"])) {
                if (is_dir($filePath)) {
                    $childPngs = $this->listPngs($filePath);

                    foreach ($childPngs as $value) {
                        $pngs[] = $value;
                    }
                } else {
                    if (strrpos($filePath, ".png")) {
                        $pngs[] = $filePath;
                    }
                }
            } elseif (strrpos($file, ".png") !== false) {
                $pngs[] = $filePath;
            }
        }

        closedir($dopen);

        return $pngs;
    }

    /*--------------------------------------
    * Sprite Functions
    ---------------------------------------*/
    private function getSpriteSizes(array $pngs): array
    {
        $pngsNb = count($pngs);

        if ($pngsNb > 15) {
            exit("\nERROR : number of png files too much, do not exceed 15 png files\n");
        }

        // calculate sprite sizes in horizontal
        $pngHeights = [];
        $pngWidth = 0;

        foreach ($pngs as $png) {
            if (exif_imagetype($png) !== IMAGETYPE_PNG) {
                echo "warning : file '$png' not valid to get size\n";
                unset($pngs[$png]);
                continue;
            }

            $pngSrc = imagecreatefrompng($png);
            $pngWidth += imagesx($pngSrc);
            $pngHeights[] += imagesy($pngSrc);

            imagedestroy($pngSrc);
        }

        $pngHeightMax = max($pngHeights);

        return [$pngWidth, $pngHeightMax];
    }

    private function generateSprite(array $pngs, array $spriteSizes, string $spriteName): void
    {
        // create empty image
        $spriteWidth = $spriteSizes[0];
        $spriteHeight = $spriteSizes[1];
        $sprite = imagecreatetruecolor($spriteWidth, $spriteHeight);
        $whiteBg = ImageColorAllocate($sprite, 255, 255, 255);

        // create and copy images into sprite
        $distance_x = 0;
        foreach ($pngs as $png) {
            if (exif_imagetype($png) != IMAGETYPE_PNG) {
                echo "warning: file '$png' not valid to concatenate\n";
                unset($pngs[$png]);
                continue;
            }

            $pngSrc = imagecreatefrompng($png);
            $pngWidthSrc = imagesx($pngSrc);
            $pngHeightSrc = imagesy($pngSrc);

            imagecopy($sprite, $pngSrc, $distance_x, 0, 0, 0, $pngWidthSrc, $pngHeightSrc);
            $distance_x += $pngWidthSrc;

            imagedestroy($pngSrc);
        }

        // save sprite
        imagepng($sprite, $spriteName);

        echo "\nSUCCESS: sprite '$spriteName' created !\n";
    }
}

$spriteGenerator = new SpriteGenerator($argv);
$spriteGenerator->run();
