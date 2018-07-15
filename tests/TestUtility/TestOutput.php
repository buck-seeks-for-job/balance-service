<?php
declare(strict_types=1);

namespace Iqoption\Test\TestUtility;

use Symfony\Component\Console\Output\Output;

class TestOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message.($newline ? "\n" : '');
    }
}
