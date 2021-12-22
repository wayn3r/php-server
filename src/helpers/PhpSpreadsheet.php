<?php

namespace Helpers;

require __DIR__ . '/../../vendor/autoload.php';

class SpreadsheetFile {
    public string $filename;
    public $file;
    public function __construct(string $filename, $file) {
        $this->filename = $filename;
        $this->file = $file;
    }
}
class PhpSpreadsheet {
    private \PhpOffice\PhpSpreadsheet\Spreadsheet $document;
    private \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet;
    private string $title;
    private string $date;
    private array $columns;
    private array $head;
    private array $body;
    private array $foot;

    private int $head_starting_cell_number;
    private int $foot_starting_cell_number;

    const decimal = '#,##0.00';
    const entero = '#,##0';
    const porcentaje = '0.00%';
    const left = 'left';
    const center = 'center';
    const right = 'right';

    public function __construct() {
        $this->document = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $this->sheet = $this->document->getActiveSheet();
        $this->font = [
            'bold'  => true,
            'size'  => 10,
            'name'  => 'Arial'
        ];
        $this->date = \Helpers\Tools::capitalize(\Helpers\Format::date(date('Y-m-d'), 'B d, G'));
    }
    private function setHeader() {
        $col = $this->columns[count($this->columns) - 1];
        $this->sheet->getStyle('B2:' . $col . '6')
            ->applyFromArray(['font' => $this->font]);

        $this->sheet->mergeCells('B2:D2');
        $this->sheet->mergeCells('B3:G3');
        $this->sheet->mergeCells('B4:D4');

        //Encabezado
        $this->sheet->setCellValue("B2", 'ALMACEN DE REPUESTOS, S. A.');
        $this->sheet->setCellValue('B3', $this->title);
        $this->sheet->setCellValue('B4', 'Fecha: ' . $this->date);
        $this->head_starting_cell_number = 6;
    }
    private function setColumns(int $number) {
        $columns = ['B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        array_splice($columns, $number,);
        $this->columns = $columns;
        foreach ($this->columns as $column) {
            $this->sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }

    private function setStylesFrom(array $styles, callable $cell_range) {
        $values = [];
        foreach ($styles as $rowKey => $row) {
            if (!is_array($row)) continue;
            foreach ($row as $colKey => $col) {
                if (!is_array($col)) {
                    $values[$rowKey][$colKey] = $col;
                    continue;
                }
                @[
                    'value' => $value,
                    'align' => $align,
                    'format' => $format,
                    'bold' => $bold
                ] = $col;

                $values[$rowKey][$colKey] = $value;

                $cell = $cell_range($this->columns[$colKey], $rowKey);
                $style = $this->sheet->getStyle($cell);

                $align && $style->getAlignment()->setHorizontal($align);
                $format && $style->getNumberFormat()->setFormatCode($format);
                $bold && $style->getFont()->setBold(true);
            }
        }
        return $values;
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }
    public function setHead(array $head) {
        $this->head = $head;
        $this->setColumns(count($this->head));

        $this->setHeader();
        //Cabecera de la tabla
        foreach ($this->head as $key => $value) {
            $value = is_array($value) ? @$value['value'] : $value;
            $cell = $this->columns[$key] . $this->head_starting_cell_number;
            $this->sheet->getStyle($cell)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $this->sheet->setCellValue($cell, $value);
        }
    }
    public function setBody(array $body) {
        $this->body = $body;
        $this->sheet->fromArray(
            $this->body,  // The data to set
            NULL,        // Array values with this value will not be set
            'B' . ($this->head_starting_cell_number + 1) // Top left coordinate of the worksheet range where
            //    we want to set these values (default is A1)
        );

        // estilos
        $this->foot_starting_cell_number = $this->head_starting_cell_number + count($this->body) + 2;
        $start = $this->head_starting_cell_number + 1;
        $end =  $this->foot_starting_cell_number - 2;
        $this->setStylesFrom(
            [$this->head],
            fn ($col) => $col . $start . ':' . $col .  $end
        );
    }
    public function setFoot(array $foot) {
        $this->foot = $foot;
        $cleanValues = $this->setStylesFrom(
            $this->foot,
            fn ($col, $row) => $col . ($this->foot_starting_cell_number + $row)
        );
        //Monto total de reparaciones
        $this->sheet->fromArray(
            $cleanValues,  // The data to set
            NULL,        // Array values with this value will not be set
            'B' .  $this->foot_starting_cell_number         // Top left coordinate of the worksheet range where
            //    we want to set these values (default is A1)
        );
    }

    public function create(): \Helpers\SpreadsheetFile {

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->document);
        $writer->setIncludeCharts(true);

        $file = tmpfile();
        $writer->save($file);
        fseek($file, 0);

        $excel = new \Helpers\SpreadsheetFile(
            $this->title . '.xlsx',
            stream_get_contents($file)
        );

        fclose($file);
        return $excel;
    }

}
