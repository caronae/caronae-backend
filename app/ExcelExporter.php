<?php


namespace App;


use Maatwebsite\Excel\Facades\Excel;

class ExcelExporter
{
    public function export($name, $headers, $data, $type){
        Excel::create('caronae-'.$name, function($excel) use($data, $name, $headers) {
            $excel->sheet(str_limit($name, 27), function($sheet) use($data, $headers) {
                // resolve o problema de os elementos serem stdClass ao invés de array
                $data = array_map(function($el){ return (array)$el; }, $data);

                array_unshift($data, $headers);
                $sheet->fromArray($data);
            });
        })->export($type);
    }
}