<?php


namespace Caronae\ExcelExport;

use Maatwebsite\Excel\Classes\PHPExcel;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;

class ExcelExporter
{
    public function exportBase($name, $headers, $data, $type, $sheetCallback){
        $excel = new Excel(
            app(PHPExcel::class),
            app(LaravelExcelReader::class),
            app(LaravelExcelWriterWithBetterCSVSupport::class)
        );

        $excel->create('caronae-'.$name, function($document) use($data, $name, $headers, $sheetCallback) {
            $document->sheet(str_limit($name, 27), $sheetCallback);
        })->export($type);
    }

    public function export($name, $headers, $data, $type){
        $this->exportBase($name, $headers, $data, $type, function ($sheet) use ($data, $headers) {
            // resolve o problema de os elementos serem stdClass ao invés de array
            $data = array_map(function ($el) {
                return (array)$el;
            }, $data);

            // acrescenta os headers no topo
            array_unshift($data, $headers);
            $sheet->fromArray($data);
        });
    }

    public function exportWithBlade($name, $view, $headers, $data, $type){
        $this->exportBase($name, $headers, $data, $type, function ($sheet) use ($data, $headers, $view) {

            $sheet->loadView($view, ['data' => $data]);

        });
    }
}