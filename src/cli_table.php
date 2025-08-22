<?php

namespace miggi;

/*
from:
https://gist.github.com/skwid138/2857a1733b969301d7ad939aa4726006

via:
https://stackoverflow.com/questions/7039010/how-to-make-alignment-on-console-in-php

*/

/**
 * The table Printer class has no dependencies and generates an array of strings
 * out of an array of objects or arrays and the child properties that should be printed given by an array of strings
 *
 * Should result in something like the following:
 * +------+---------------+----------------+---------------+
 * |  id  |     name      | col_name_three | col_name_four |
 * +------+---------------+----------------+---------------+
 * | 1138 | Bill Murray   | thing 1        | thing 2       |
 * | 2020 | Jeff Goldblum | thing 1        | thing 2       |
 * | 117  | John-117      | thing 1        | thing 2       |
 * +------+---------------+----------------+---------------+
 *
 * Credit: https://gist.github.com/redestructa/2a7691e7f3ae69ec5161220c99e2d1b3
 */
class cli_table {

    public array $rows = [];

    public function __construct($data, $headers, $opts = []) {
        list($data, $headers) = $this->normalize_data($data, $headers);
        $this->rows = $this->getTableRows($data, $headers);
    }

    /*
        sort and reduce columns by header keys
    */
    public function normalize_data($data, $headers): array {
        $sort = array_keys($headers);
        //    $newheaders = array_values($headers);
        $newdata = [];
        foreach ($data as $d) {
            $new = [];
            $obj = is_object($d);
            foreach ($sort as $key) {
                $new[$key] = $obj ? $d->$key : $d[$key];
            }
            $newdata[] = $new;
        }
        return [$newdata, $headers];
    }

    public function render() {
        return $this->echoTableRows($this->rows);
    }
    /**
     * Echo the rendered table rows
     *
     * @param array|string[] $renderedTableRows List of rendered table row strings
     */
    // https://stackoverflow.com/questions/4842424/list-of-ansi-color-escape-sequences
    public function echoTableRows(array $renderedTableRows): string {
        $eol = \PHP_EOL;
        $green = '[95;1m'; // TODO: Make this configurable
        $defaultColor = '[39;0m';

        $i = 0;
        $res = "";
        foreach ($renderedTableRows as $rowStr) {
            $isHeaderRow = $i === 1;
            $rowColor = $isHeaderRow ? $green : $defaultColor; // Make the header row green
            $res .= "\033{$rowColor}{$rowStr}{$eol}";
            $i++;
        }
        return $res;
    }

    /**
     * Get an array of rendered table rows
     *
     * @param array|array[]|object[] $tableData Array of arrays/objects representing the table rows and corresponding data
     * @param array|string[] $headers Array of table header strings
     * @return array|string[] List of rendered table row strings
     */
    public function getTableRows(array $tableData, array $headers): array {
        $lines = [];
        $columnWidths = $this->calculateColumnWidths($tableData);
        $rowSeparator = $this->renderRowSeparator($columnWidths);

        $lines[] = $rowSeparator; // Add the top border of the table
        $lines[] = $this->renderHeader($headers, $columnWidths); // Add the rendered table header
        $lines[] = $rowSeparator; // Add the separator under the header

        foreach ($tableData as $item) {
            $lines[] = $this->renderRow($item, $columnWidths); // Add rendered data rows
        }

        $lines[] = $rowSeparator; // Add bottom border of the table

        return $lines;
    }

    /**
     * Set the table column widths using the greatest width checking the header and data values
     *
     * @param array|array[]|object[] $tableData Array of table row data (row data may be an array or object)
     * @return array List of column widths uses column name as the key
     */
    public function calculateColumnWidths(array $tableData): array {
        $columnWidths = [];

        foreach ($tableData as $rowData) {
            foreach ($rowData as $column => $data) {
                // Set the column width using the largest length be that from the data or the column header name
                $columnWidths[$column] = max($columnWidths[$column] ?? strlen($column), strlen($data));
            }
        }

        return $columnWidths;
    }

    /**
     * Create horizontal row seperator string
     *
     * @param array $columnWidths List of column widths
     * @param string $rowColumnIntersect Text character to be used at the intersection of rows and columns, defaults to '+'
     * @return string Horizontal row separator
     */
    public function renderRowSeparator(array $columnWidths, string $rowColumnIntersect = '+'): string {
        $spacesAroundData = 2;
        $separatorStr = $rowColumnIntersect;

        foreach ($columnWidths as $width) {
            $separatorStr .= $this->strRepeat('-', $width + $spacesAroundData);
            $separatorStr .= $rowColumnIntersect;
        }

        return $separatorStr;
    }

    /**
     * Append a string to itself a given number of times
     *
     * @param string $str String to be repeated
     * @param int $count The number of times to repeat the string
     * @return string The repeated string
     */
    public function strRepeat(string $str, int $count): string {
        $str2 = '';
        for ($i = $count; $i > 0; $i--) {
            $str2 .= $str;
        }

        return $str2;
    }

    /**
     * Render the table header
     *
     * @param array $headers List of table headers
     * @param array $columnSize List of column widths
     * @return string Rendered table header
     */
    public function renderHeader(array $headers, array $columnSize): string {
        return $this->renderRow($headers, $columnSize, STR_PAD_BOTH);
    }

    /**
     * Create table row strings
     *
     * @param array|object $rowData Array or Object of key value row data
     * @param array $columnSize List of column widths
     * @return string Rendered table row
     */
    public function renderRow($rowData, array $columnSize, int $padType = STR_PAD_RIGHT): string {
        $rowString = '';
        if (is_object($rowData)) {
            $rowData = (array) $rowData;
        }
        foreach ($rowData as $column => $data) {
            // This accounts for headers not having the corresponding column keys as the data itself is the column key
            $rowWidth = $columnSize[$column] ?? $columnSize[$data];
            $rowString .= '| ' . str_pad($data, $rowWidth, ' ', $padType) . ' ';

            // Add the outer right side of the table if on last column
            $headers = array_keys($rowData);
            // If two columns have the same data, this prevents incorrectly adding the '|'
            $rowString .= end($headers) === $column ? '|' : '';
        }
        return $rowString;
    }

    /**
     * Add two numeric values
     *
     * @param int $a Value to be added
     * @param int $b Value to be added
     * @return int The summed value of the arguments
     */
    public function sum(?int $a, int $b): int {
        return ($a ?? 0) + $b;
    }
}
