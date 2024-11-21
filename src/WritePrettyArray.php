<?php

namespace SchenkeIo\PrettyPhpArray;

use Symfony\Component\VarExporter\Exception\ExceptionInterface;
use Symfony\Component\VarExporter\VarExporter;

class WritePrettyArray
{
    protected const TAB = '    '; // 4 spaces
    protected const COMMENT_PREFIX = self::TAB . "| ";
    protected const LONG_LINE =
        self::COMMENT_PREFIX .
        "--------------------------------------------------------------------------" .
        PHP_EOL;


    /**
     * @param string $filePath where should the file be written to
     * @param array $data key value pairs of data
     * @param array $comments key value pairs of comments assciated with the dta
     * @param string $header additional header on top
     * @return void
     * @throws ExceptionInterface
     */
    public static function toFile(
        string $filePath,
        array  $data,
        array  $comments = [],
        string $header = ''
    ): void
    {
        $content = "<?php #SchenkeIo\PrettyPhpArray " . PHP_EOL . PHP_EOL;
        if ($header) {
            $lines = explode("\n", $header);
            $content .= "/**" . PHP_EOL;
            $content .= " *" . PHP_EOL;
            foreach ($lines as $line) {
                $content .= " * " . $line . PHP_EOL;
            }
            $content .= " *" . PHP_EOL;
            $content .= " */" . PHP_EOL;
        }
        $content .= PHP_EOL . "return [" . PHP_EOL;
        foreach ($data as $key => $value) {
            if (isset($comments[$key])) {
                if (is_array($comments[$key])) {
                    if (sizeof($comments[$key]) == 1) {
                        [$header, $lines] = [$comments[$key][0], []];
                    } else {
                        [$header, $lines] = $comments[$key];
                    }
                } else {
                    [$header, $lines] = ['', $comments[$key]];
                }
                $content .= PHP_EOL . self::TAB . "/*" . PHP_EOL;
                if ($header) {
                    $content .= self::LONG_LINE;
                    $content .= self::COMMENT_PREFIX . $header . PHP_EOL;
                    $content .= self::LONG_LINE;
                    $content .= self::COMMENT_PREFIX . PHP_EOL;
                }
                if ($lines) {
                    if (!is_array($lines)) {
                        $lines = [$lines];
                    }
                    foreach ($lines as $line) {
                        $content .= self::COMMENT_PREFIX . $line . PHP_EOL;
                    }
                    $content .= self::COMMENT_PREFIX . PHP_EOL;
                }
                $content .= self::TAB . "*/" . PHP_EOL . PHP_EOL;
            }

            $valueLines = explode(PHP_EOL, VarExporter::export($value));
            // first put the first line behind the key
            $content .= self::TAB . "'$key' => " . $valueLines[0];
            $lastValueLinesIndex = count($valueLines) - 1;
            if ($lastValueLinesIndex == 0) {
                // we are done with one line
                $content .= "," . PHP_EOL;
            } else {
                $content .= PHP_EOL;
                // we loop over it
                foreach ($valueLines as $index => $line) {
                    if ($index == 0) continue;
                    if ($index == $lastValueLinesIndex) {
                        $content .= self::TAB . $line . "," . PHP_EOL;
                    } else {
                        $content .= self::TAB . $line . PHP_EOL;
                    }
                }
            }
        }
        $content .= "];" . PHP_EOL;
        file_put_contents($filePath, $content);
    }
}