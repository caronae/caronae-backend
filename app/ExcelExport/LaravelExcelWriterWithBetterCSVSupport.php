<?php

namespace App\ExcelExport;

use Maatwebsite\Excel\Writers\LaravelExcelWriter;

/**
 * Essa classe foi criada para obter acesso ao CSVWriter que o
 * LaravelExcelWriter usa para gerar o CSV.
 * Como ele só gera o writer imediatamente antes de usá-lo para exportar,
 * não é possível modificá-lo a não ser extendendo a classe.
 *
 * A importancia do uso do BOM no CSV é que o Excel só abre um arquivo
 * CSV como UTF-8 se ele tiver BOM (e mesmo assim só no Excel 2007 em diante).
 *
 * Ver uma explicação melhor aqui: http://stackoverflow.com/questions/155097/microsoft-excel-mangles-diacritics-in-csv-files/155176#155176
 */
class LaravelExcelWriterWithBetterCSVSupport extends LaravelExcelWriter
{
    protected function _setWriter()
    {
        $writer = parent::_setWriter();
        if($this->format == 'CSV')
            $writer->setUseBOM(true);
        return $writer;
    }

}