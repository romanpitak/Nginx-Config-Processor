<?php

namespace A;

class Exception extends \Exception
{

}

class ConfigFile extends ConfigString
{

    /** @var string $inFilePath */
    private $inFilePath;

    /**
     * @param $filePath string Name of the conf file (or full path).
     * @throws Exception
     */
    public function __construct($filePath)
    {
        $this->inFilePath = $filePath;

        $contents = @file_get_contents($this->inFilePath);

        if (false === $contents) {
            throw new Exception('Cannot read file "' . $this->inFilePath . '".');
        }

        parent::__construct($contents);

    }

}

class ConfigString
{

    const CURRENT_POSITION = 'current position';

    /** @var string $data */
    private $data;

    /** @var int $position */
    private $position;

    public function __construct($data)
    {
        $this->position = 0;
        $this->data = $data;
    }

    public function eof()
    {
        return (!isset($this->data[$this->position]));
        //return (!(strlen($this->data) > $this->position));
    }

    public function inc($inc = 1)
    {
        $this->position += $inc;
    }

    public function gotoNextEol()
    {
        $nextEol = strpos($this->data, PHP_EOL, $this->position);

        if (false === $nextEol) {
            $nextEol = strlen($this->data) - 1;
        }

        $this->position = $nextEol;
    }

    public function getChar($position = self::CURRENT_POSITION)
    {
        if (self::CURRENT_POSITION === $position) {
            $position = $this->position;
        }

        if (!is_int($position)) {
            throw new Exception('Position is not int. ' . gettype($position));
        }

        if ($this->eof()) {
            throw new Exception('Index out of range. Position: ' . $position . '.');
        }

        return $this->data[$position];
    }

}


abstract class StringProcessor
{

    /** @var ConfigString $configString */
    private $configString;

    public function __construct(ConfigString $configString)
    {
        $this->configString = $configString;
        $this->run();
    }

    abstract protected function run();

    // common processing functions

    protected function skipComment()
    {
        if ('#' !== $this->configString->getChar()) {
            return false;
        }

        new Comment($this->configString);
        return true;
    }

    /**
     * @return ConfigString
     */
    protected function getConfigString()
    {
        return $this->configString;
    }

    // magic

    abstract public function prettyPrint($indentLevel, $spacesPerIndent = 4);

    public function __toString()
    {
        return $this->prettyPrint(-1);
    }

}

class Comment extends StringProcessor
{

    protected function run()
    {
        $this->getConfigString()->gotoNextEol();
    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        return "# comment was here";
    }

}

class Scope extends StringProcessor
{

    /** @var Directive[] $directives */
    private $directives;

    protected function run()
    {
        $configString = $this->getConfigString();
        while (false === $configString->eof()) {

            $this->skipComment();

            $c = $configString->getChar();

            if (('a' <= $c) && ('z' >= $c)) {
                $this->directives[] = new Directive($configString);
                continue; // this continue is important for some reason
            }

            if ('}' === $this->getConfigString()->getChar()) {
                break;
            }

            $this->getConfigString()->inc();
        }
    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $rs = "";
        foreach ($this->directives as $directive) {
            $rs .= $directive->prettyPrint($indentLevel + 1, $spacesPerIndent);
        }

        return $rs;
    }

}

class Directive extends StringProcessor
{

    /** @var Scope $scope */
    private $scope;

    /** @var string $text */
    private $text = '';

    private $name, $value;

    protected function run()
    {
        $configString = $this->getConfigString();
        while (false === $configString->eof()) {

            $this->skipComment();

            $c = $configString->getChar();

            if ('{' === $c) {
                $this->processText();
                $this->scope = new Scope($configString);
                $configString->inc();
                break; // this continue is not important
            }

            if (';' === $c) {
                $this->processText();
                break;
            }

            $this->text .= $c;

            $configString->inc();
        }
    }

    private function processText()
    {
        $found = false;

        $patternWithValue = '#^([a-z_]+) +([^;{]+)$#';
        if (1 === preg_match($patternWithValue, $this->text, $matches)) {
            $this->name = $matches[1];
            $this->value = rtrim($matches[2]);
            $found = true;
        }

        $patternWithoutValue = '#^([a-z_]+) *$#';
        if (1 === preg_match($patternWithoutValue, $this->text, $matches)) {
            $this->name = $matches[1];
            $this->value = null;
            $found = true;
        }

        if (false === $found) {
            throw new Exception('Did not match pattern.');
        }

    }

    public function prettyPrint($indentLevel, $spacesPerIndent = 4)
    {
        $indent = str_repeat(str_repeat(' ', $spacesPerIndent), $indentLevel);
        $rs = $indent . $this->name . " " . $this->value;
        $rs .= isset($this->scope) ? (" {\n" . $this->scope->prettyPrint($indentLevel, $spacesPerIndent) . $indent .  "}\n") : ";\n";
        return $rs;
    }

}

echo "START\n";

$f = new ConfigFile('m1.conf');

$s = new Scope($f);

echo $s;

echo "STOP\n";