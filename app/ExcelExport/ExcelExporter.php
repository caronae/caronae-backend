<?php


namespace App\ExcelExport;

use Maatwebsite\Excel\Classes\PHPExcel;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class ExcelExporter
{
    public function export($name, $headers, $data, $type){
        $excel = new Excel(
            app(PHPExcel::class),
            app(LaravelExcelReader::class),
            //app(\Maatwebsite\Excel\Writers\LaravelExcelWriter::class)
            app(LaravelExcelWriterWithBetterCSVSupport::class)
        );

        $excel->create('caronae-'.$name, function($document) use($data, $name, $headers) {
            $document->sheet(str_limit($name, 27), function ($sheet) use ($data, $headers) {
                // resolve o problema de os elementos serem stdClass ao invés de array
                $data = array_map(function ($el) {
                    return (array)$el;
                }, $data);

                array_unshift($data, $headers);
                $sheet->fromArray($data);
            });
        })->export($type);
    }
}