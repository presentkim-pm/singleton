<?php

/**
 * DevTools plugin for PocketMine-MP
 * Copyright (C) 2014 PocketMine <https://github.com/PocketMine/DevTools>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @name MakeOptimizedPlugin
 * @main        presentkim\singleton\MakeOptimizedPlugin
 * @version     1.0.1
 * @api         3.0.0-ALPHA11
 * @description Make optimized plugin with devtools
 * @author      PresentKim
 */


namespace presentkim\singleton {

    use pocketmine\{
      command\Command, command\CommandSender, command\PluginCommand, plugin\Plugin, plugin\PluginBase, Server, utils\TextFormat
    };
    use DevTools\DevTools;
    use FolderPluginLoader\FolderPluginLoader;

    class MakeOptimizedPlugin extends PluginBase{

        /** @var DevTools */
        private $devtools = null;

        public function onEnable() : void{
            $this->devtools = $this->getServer()->getPluginManager()->getPlugin("DevTools");
            if ($this->devtools === null) {
                $this->getLogger()->warning("I need DevTools plugin!");
                $this->getServer()->getPluginManager()->disablePlugin($this);
            }

            $command = new PluginCommand('makeoptimizedplugin', $this);
            $command->setExecutor($this);
            $command->setPermission('devtools.command.makeplugin');
            $command->setDescription('Creates a Optimized Phar plugin from one in source code form');
            $command->setUsage('/makeoptimizedplugin <pluginName> [minify=true]');
            $command->setAliases([
              'mop',
              'makeop',
            ]);
            $this->getServer()->getCommandMap()->register('makeoptimizedplugin', $command);
        }

        /**
         * @param CommandSender $sender
         * @param Command       $command
         * @param string        $label
         * @param string[]      $args
         *
         * @return bool
         */
        public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
            if (isset($args[0])) {
                $minify = isset($args[1]) ? (bool) $args[1] : false;
                unset($args[1]);
                if ($args[0] === "*") {
                    $plugins = $this->getServer()->getPluginManager()->getPlugins();
                    $succeeded = $failed = [];
                    $skipped = 0;
                    foreach ($plugins as $plugin) {
                        if (!$plugin->getPluginLoader() instanceof FolderPluginLoader) {
                            $skipped++;
                            continue;
                        }
                        if ($this->makePluginCommand($sender, $minify, [$plugin->getName()])) {
                            $succeeded[] = $plugin->getName();
                        } else {
                            $failed[] = $plugin->getName();
                        }
                    }
                    if (count($failed) > 0) {
                        $sender->sendMessage(TextFormat::RED . count($failed) . " plugin" . (count($failed) === 1 ? "" : "s") . " failed to build: " . implode(", ", $failed));
                    }
                    if (count($succeeded) > 0) {
                        $sender->sendMessage(TextFormat::GREEN . count($succeeded) . '/' . (count($plugins) - $skipped) . " plugin" . ((count($plugins) - $skipped) === 1 ? "" : "s") . " successfully built: " . implode(", ", $succeeded));
                    }
                } else {
                    $this->makePluginCommand($sender, $minify, $args);
                }
                return true;
            }
            return false;
        }

        private function makePluginCommand(CommandSender $sender, bool $minify, array $args) : bool{
            $pluginName = trim(implode(' ', $args));
            if ($pluginName === "" or !(($plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName)) instanceof Plugin)) {
                $sender->sendMessage(TextFormat::RED . 'Invalid plugin name, check the name case.');
                return false;
            }
            $description = $plugin->getDescription();

            if (!($plugin->getPluginLoader() instanceof FolderPluginLoader)) {
                $sender->sendMessage(TextFormat::RED . "Plugin {$description->getName()} is not in folder structure.");
                return false;
            }

            $pharPath = "{$this->getDataFolder()}{$description->getName()}_v{$description->getVersion()}.phar";

            if ($minify) {
                $metadata = [];
            } else {
                $metadata = [
                  'name'         => $description->getName(),
                  'version'      => $description->getVersion(),
                  'main'         => $description->getMain(),
                  'api'          => $description->getCompatibleApis(),
                  'depend'       => $description->getDepend(),
                  'description'  => $description->getDescription(),
                  'authors'      => $description->getAuthors(),
                  'website'      => $description->getWebsite(),
                  'creationDate' => time(),
                ];
            }

            if ($description->getName() === 'DevTools') {
                $stub = '<?php require("phar://". __FILE__ ."/src/DevTools/ConsoleScript.php"); __HALT_COMPILER();';
            } elseif ($minify) {
                $stub = '<?php __HALT_COMPILER();';
            } else {
                $stub = '<?php echo "PocketMine-MP plugin ' . $description->getName() . ' v' . $description->getVersion() . '\nThis file has been generated using DevTools v' . $this->getDescription()->getVersion() . ' at ' . date("r") . '\n----------------\n";if(extension_loaded("phar")){$phar = new \Phar(__FILE__);foreach($phar->getMetadata() as $key => $value){echo ucfirst($key).": ".(is_array($value) ? implode(", ", $value):$value)."\n";}} __HALT_COMPILER();';
            }

            $reflection = new \ReflectionClass("pocketmine\\plugin\\PluginBase");
            $file = $reflection->getProperty('file');
            $file->setAccessible(true);
            $filePath = rtrim(str_replace("\\", '/', $file->getValue($plugin)), '/') . '/';
            $newPath = $this->getDataFolder() . 'build/';
            if (!file_exists($newPath)) {
                mkdir($newPath, 0777, true);
            }
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filePath)) as $path => $fileInfo) {
                $inPath = substr($path, strlen($filePath));
                $fileName = $fileInfo->getFilename();
                if (strpos($inPath, '.') === 0 || strpos($fileName, '.') === 0 || $fileName === "." || $fileName === "..") {
                    continue;
                }
                if (!$minify || $inPath === 'plugin.yml' || strpos($inPath, 'src\\') === 0 || strpos($inPath, 'resources\\') === 0) {
                    $contents = \file_get_contents($path);
                    if (\substr($path, -4) == '.php') {
                        $tree = \token_get_all($contents);
                        optimize($tree, $minify);
                        $contents = recreateTree($tree);
                    }
                    $newFilePath = "$newPath$inPath";
                    $newFileDir = dirname($newFilePath);
                    if (!file_exists($newFileDir)) {
                        mkdir($newFileDir, 0777, true);
                    }
                    \file_put_contents($newFilePath, $contents);
                }
            }
            $this->buildPhar($sender, $pharPath, rtrim(str_replace("\\", '/', $newPath), '/') . '/', [], $metadata, $stub, \Phar::SHA1);

            if (file_exists($newPath)) {
                delTree($newPath);
            }
            $sender->sendMessage("Phar plugin {$description->getName()} v{$description->getVersion()} has been created on {$pharPath}" . ($minify ? ' (minify)' : ''));
            return true;
        }

        private function buildPhar(CommandSender $sender, string $pharPath, string $basePath, array $includedPaths, array $metadata, string $stub, int $signatureAlgo = \Phar::SHA1) : void{
            $dirname = dirname($pharPath);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }

            if (file_exists($pharPath)) {
                $sender->sendMessage('Phar file already exists, overwriting...');
                \Phar::unlinkArchive($pharPath);
            }

            $sender->sendMessage('[DevTools] Adding files...');

            $start = microtime(true);

            $phar = new \Phar($pharPath);
            $phar->setMetadata($metadata);
            $phar->setStub($stub);
            $phar->setSignatureAlgorithm($signatureAlgo);
            $phar->startBuffering();

            //If paths contain any of these, they will be excluded
            $excludedSubstrings = [
              '/.',
              //"Hidden" files, git information etc
              realpath($pharPath)
              //don't add the phar to itself
            ];

            $regex = sprintf('/^(?!.*(%s))^%s(%s).*/i', implode('|', preg_quote_array($excludedSubstrings, '/')), //String may not contain any of these substrings
              preg_quote($basePath, '/'), //String must start with this path...
              implode('|', preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
            );

            $count = count($phar->buildFromDirectory($basePath, $regex));
            $sender->sendMessage("[DevTools] Added {$count} files");

            $sender->sendMessage('[DevTools] Checking for compressible files...');
            foreach ($phar as $file => $finfo) {
                /** @var \PharFileInfo $finfo */
                if ($finfo->getSize() > (1024 * 512)) {
                    $sender->sendMessage("[DevTools] Compressing {$finfo->getFilename()}");
                    $finfo->compress(\Phar::GZ);
                }
            }
            $phar->stopBuffering();

            $sender->sendMessage('[DevTools] Done in ' . round(microtime(true) - $start, 3) . 's');
        }

    }

    function preg_quote_array(array $strings, string $delim = null) : array{
        return array_map(function (string $str) use ($delim) : string{
            return preg_quote($str, $delim);
        }, $strings);
    }

    function delTree($dir) : bool{
        $files = array_diff(scandir($dir), [
          '.',
          '..',
        ]);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function optimize(array &$tree, bool $minify) : void{
        $firstChars = \array_merge(\range('a', 'z'), \range('A', 'Z'));
        $otherChars = \array_merge(\range('0', '9'), $firstChars);
        \array_unshift($firstChars, '_');
        \array_unshift($otherChars, '_');
        $variables = [];

        $ignoreVartiableBefores = [
          'protected',
          'private',
          'public',
          'static',
          'final',
          '::',
        ];
        $lastSign = '';
        $openTag = \null;
        $inHeredoc = \false;
        foreach ($tree as $index => &$token) {
            if (\is_array($token)) {
                switch ($token[0]) {
                    case \T_STRING:
                        if (($before = getBefore($tree, $index)) !== '\\') {
                            if (\defined('\\' . $token[1]) and $before !== '::' and $before !== '->') {
                                $token[1] = '\\' . $token[1];
                            } elseif (\function_exists('\\' . $token[1]) and $before !== '::' and $before !== '->' and $before !== 'function' and getAfter($tree, $index) === '(') {
                                $token[1] = '\\' . $token[1];
                            }
                        }
                        break;
                    case \T_COMMENT:
                        if ($minify) {
                            $token[1] = '';
                        }
                        break;
                    case \T_DOC_COMMENT:
                        if ($minify) {
                            $annotations = [];
                            if (preg_match("/^[\t ]*\* @priority[\t ]{1,}([a-zA-Z]{1,})/m", $token[1], $matches) > 0) {
                                $annotations[] = "@priority $matches[1]";
                            }
                            if (preg_match("/^[\t ]*\* @ignoreCancelled[\t ]{1,}([a-zA-Z]{1,})/m", $token[1], $matches) > 0) {
                                $annotations[] = "@ignoreCancelled $matches[1]";
                            }
                            $token[1] = '';
                            if (!empty($annotations)) {
                                $token[1] .= '/** ' . PHP_EOL;
                                foreach ($annotations as $value) {
                                    $token[1] .= "* $value" . PHP_EOL;
                                }
                                $token[1] .= '*/' . PHP_EOL;
                            }
                        }
                        break;
                    case \T_WHITESPACE:
                        if ($minify) {
                            $token[1] = ' ';
                        }
                        break;
                    case \T_VARIABLE:
                        if ($token[1] != '$this' && !in_arrayi(getBefore($tree, $index), $ignoreVartiableBefores)) {
                            if (!isset($variables[$token[1]])) {
                                $variableName = '$' . $firstChars[\count($variables) % \count($firstChars)];
                                if (($sub = \floor((\count($variables)) / \count($firstChars)) - 1) > -1) {
                                    $variableName .= $firstChars[$sub];
                                }
                                if (isset($variables[$variableName])) {
                                    $variableName .= \dechex(\count($variables) + 10);
                                }
                                $variables[$token[1]] = $variableName;
                            }
                            $token[1] = $variables[$token[1]];
                        }
                        break;
                    case \T_OPEN_TAG:
                        if (\strpos($token[1], ' ') || \strpos($token[1], "\n") || \strpos($token[1], "\t") || \strpos($token[1], "\r")) {
                            $token[1] = \rtrim($token[1]);
                        }
                        $token[1] .= ' ';
                        break;
                    case \T_OPEN_TAG_WITH_ECHO:
                        $openTag = \T_OPEN_TAG_WITH_ECHO;
                        break;
                    case \T_CLOSE_TAG:
                        $openTag = \null;
                        if ($openTag == \T_OPEN_TAG_WITH_ECHO) {
                            $token[1] = \rtrim($token[1], '; ');
                        } else {
                            $token[1] = ' ' . $token[1];
                        }
                        break;
                    case \T_CONSTANT_ENCAPSED_STRING:
                    case \T_ENCAPSED_AND_WHITESPACE:
                        $token[1] = \addcslashes($token[1], "\n\t\r");
                        break;
                    case \T_START_HEREDOC:
                        $inHeredoc = \true;

                        $token[1] = "<<<S\n";
                        break;
                    case \T_END_HEREDOC:
                        $inHeredoc = \false;

                        $token[1] = 'S;';
                        for ($j = $index + 1, $count = \count($tree); $j < $count; $j++) {
                            if (\is_string($tree[$j]) && $tree[$j] == ';') {
                                $i = $j;
                                break;
                            } elseif ($tree[$j][0] == \T_CLOSE_TAG) {
                                break;
                            }
                        }
                        break;
                    case \T_LOGICAL_OR:
                        $token[1] = '||';
                        break;
                    case \T_LOGICAL_AND:
                        $token[1] = '&&';
                        break;
                    default:
                        if (!$inHeredoc) {
                            $token[1] = \strtolower($token[1]);
                        }
                        break;
                }
                $lastSign = '';
            } else {
                if (($token != ';' && $token != ':') || $lastSign != $token) {
                    $lastSign = $token;
                }
            }
        }
    }

    function getAfter(array $tree, $current){
        do {
            $token = $tree[++$current];
            if (\is_array($token)) {
                if ($token[0] === \T_WHITESPACE or $token[0] === \T_COMMENT or $token[0] === \T_DOC_COMMENT) {
                    continue;
                }
                return $token[1];
            } else {
                return $token;
            }
        } while (isset($tree[$current]));
        return \null;
    }

    function getBefore(array $tree, $current){
        do {
            $token = $tree[--$current];
            if (\is_array($token)) {
                if ($token[0] === \T_WHITESPACE or $token[0] === \T_COMMENT or $token[0] === \T_DOC_COMMENT) {
                    continue;
                }
                return $token[1];
            } else {
                return $token;
            }
        } while (isset($tree[$current]));
        return \null;
    }

    function recreateTree($tree) : string{
        $output = '';
        foreach ($tree as $token) {
            if (\is_array($token)) {
                $output .= $token[1];
            } else {
                $output .= $token;
            }
        }
        return $output;
    }

    function in_arrayi(string $str, array $strs) : bool{
        foreach ($strs as $key => $value) {
            if (\strcasecmp($str, $value) === 0) {
                return \true;
            }
        }
        return \false;
    }

    function array_merge($arr1, $arr2) : array{
        foreach ($arr2 as $i) {
            $arr1[] = $i;
        }
        return $arr1;
    }

    function array_rand($arr) : mixed{
        return $arr[\array_rand($arr)];
    }
}