<?php

namespace App\Gigamed\Lib;

class Psr4AutoloaderClass
{

    private bool $debug;

    public function __construct(bool $debug=false)
    {
        $this->debug = $debug;
    }

    private function affichageDebogage(string $message) : void
    {
        if ($this->debug)
            echo $message;
    }

    

    protected array $prefixes = array();

    

    public function register() : void
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    

    public function addNamespace(string $prefix, string $base_dir, bool $prepend = false) : void
    {
        
        $prefix = trim($prefix, '\\') . '\\';

        
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }

        
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }

    

    public function loadClass(string $class)
    {
        $this->affichageDebogage("<h3>Chargement automatique de classe $class </h3>");
        
        $prefix = $class;

        
        
        while (false !== $pos = strrpos($prefix, '\\')) {

            
            $prefix = substr($class, 0, $pos + 1);

            
            $relative_class = substr($class, $pos + 1);

            
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            
            
            $prefix = rtrim($prefix, '\\');
        }

        
        $this->affichageDebogage("<h3 style=\"color:red;font-weight: bold;\">Échec !</h3>");
        return false;
    }

    

    protected function loadMappedFile(string $prefix, string $relative_class)
    {
        
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        
        foreach ($this->prefixes[$prefix] as $base_dir) {

            $this->affichageDebogage("Remplacement du préfixe $prefix par $base_dir<br>");

            
            
            
            $file = $base_dir
                . str_replace('\\', '/', $relative_class)
                . '.php';

            
            if ($this->requireFile($file)) {
                
                return $file;
            }
        }

        
        return false;
    }

    

    protected function requireFile(string $file) : bool
    {
        if (file_exists($file)) {
            $this->affichageDebogage("<p><span style=\"color:green;font-weight: bold;\">Réussite</span> : fichier chargé <pre>$file</pre></p>");
            require $file;
            return true;
        }
        $this->affichageDebogage("<p><span style=\"color:red;font-weight: bold;\">Échec</span> : fichier introuvable <pre>$file</pre></p>");
        return false;
    }
}