<?php

namespace App\Services;

use PHPJasper\PHPJasper;

class JasperService
{
    protected $jasper;

    public function __construct()
    {
        $this->jasper = new PHPJasper();
    }

    /**
     * Compila um arquivo .jrxml para um arquivo .jasper.
     */
    public function compileReport($input)
    {
        $this->jasper->compile($input)->execute();
    }

    /**
     * Gera o relatório em PDF.
     */
    public function generateReport($input, $output, $options)
    {
        $this->jasper->process($input, $output, $options)->execute();
    }
}

