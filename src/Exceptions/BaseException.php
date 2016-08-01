<?php
namespace MrCrankHank\IetParser\Exceptions;

use Exception;

class BaseException extends Exception {
    /**
     * @return string
     * @link http://php.net/manual/de/function.debug-backtrace.php
     */
    protected function generateCallTrace() {
        $e = new \Exception();
        $trace = explode("\n", $this->getExceptionTraceAsString($e));
        // reverse array to make steps line up chronologically
        $trace = array_reverse($trace);
        array_shift($trace); // remove {main}
        array_pop($trace); // remove call to this method
        array_pop($trace); // remove call to log_debug_result method
        $length = count($trace);
        $result = array();
        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1) . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }
        return "\t" . implode("\n\t", $result) . "\n\n";
    }

    /**
     * php's internal getTraceAsString function truncates the output
     *
     * @param $exception
     * @link http://stackoverflow.com/questions/1949345/how-can-i-get-the-full-string-of-php-s-gettraceasstring
     * @return string
     */
    protected function getExceptionTraceAsString($exception) {
        $rtn = "";
        $count = 0;
        foreach ($exception->getTrace() as $frame) {
            $args = "";
            if (isset($frame['args'])) {
                $args = array();
                foreach ($frame['args'] as $arg) {
                    if (is_string($arg)) {
                        $args[] = "'" . $arg . "'";
                    } elseif (is_array($arg)) {
                        $args[] = "Array";
                    } elseif (is_null($arg)) {
                        $args[] = 'NULL';
                    } elseif (is_bool($arg)) {
                        $args[] = ($arg) ? "true" : "false";
                    } elseif (is_object($arg)) {
                        $args[] = get_class($arg);
                    } elseif (is_resource($arg)) {
                        $args[] = get_resource_type($arg);
                    } else {
                        $args[] = $arg;
                    }
                }
                $args = join(", ", $args);
            }
            if (isset($frame['file'], $frame['line'])) {
                $rtn .= sprintf("#%s %s(%s): %s(%s)\n",
                    $count,
                    $frame['file'],
                    $frame['line'],
                    $frame['function'],
                    $args);
                $count++;
            } else {
                $rtn .= sprintf("#%s %s(%s)\n",
                    $count,
                    $frame['function'],
                    $args);
                $count++;
            }
        }
        return $rtn;
    }
}

