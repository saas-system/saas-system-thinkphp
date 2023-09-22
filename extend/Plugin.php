<?php


class Plugin
{
    public static function install($event)
    {
        static::findHepler();
        $operation = $event->getOperation();
        $autoload  = method_exists($operation, 'getPackage') ? $operation->getPackage()->getAutoload() : $operation->getTargetPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        $namespace        = key($autoload['psr-4']);
        $install_function = "\\{$namespace}Install::install";
        if (is_callable($install_function)) {
            $install_function();
        }
    }

    public static function update($event)
    {
        static::install($event);
    }

    public static function uninstall($event)
    {
        $autoload = $event->getOperation()->getPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        $namespace          = key($autoload['psr-4']);
        $uninstall_function = "\\{$namespace}Install::uninstall";
        if (is_callable($uninstall_function)) {
            $uninstall_function();
        }
    }

    protected static function findHepler()
    {
        $file = __DIR__ . '/../vendor/topthink/framework/src/helper.php';
        if (is_file($file)) {
            require_once $file;
        }
    }
}
