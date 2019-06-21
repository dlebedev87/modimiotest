<?php
/**
 * Created by PhpStorm.
 * User: Денис
 * Date: 19.06.2019
 * Time: 21:32
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use UAParser;
use yii;


class LoadController extends Controller
{
    protected $safedir='../../userdata/temp';
    protected $re = '/((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))[, ].{1,}\[([0-3][0-9]\/\w{3}\/[0-9]{4}):([0-2][0-9]:[0-5][0-9]:[0-5][0-9]) ((?:\+|\-)[0-9]{4})\].{1,}"(GET|POST|HEAD|PUT|DELETE|TRACE|CONNECT) ([\/\w\.\-\?\_\=\*\$\%\:]+).{1,}" (\b\d{3}) (\d+|-) "((?:(?:http(?:s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,24}\b[-a-zA-Z0-9@:%_\+.~#?&\/\/=]*)|-)" "([^"]+)"$/';
    protected $x64=[
        'x86_64',
        'x86-64',
        'Win64',
        '64;',
        'amd64',
        'AMD64',
        'WOW64',
        'x64_64',
        'ia64',
        'sparc64',
        'ppc64',
        'IRIX64'
    ];
    public function actionIndex($message = 'hello world')
    {
        $filelist = glob("upload/*access.log*");
        $parser =  UAParser\Parser::create();
        foreach ($filelist as $filename){
            $start = microtime(true);
            echo 'Работаем с файлом: '.$filename.", его размер: ".filesize($filename)." байт \n";
            $fd = fopen($filename, 'r') or die("не удалось открыть файл");
            $create_data = array();
            while(!feof($fd)){
                $str = fgets($fd);
                //echo $str;
                if(preg_match($this->re, $str, $matches, PREG_OFFSET_CAPTURE, 0)) {
                    $brows="";
                    $os="";
                    $archi="";
                    if($matches[11][0]) {
                        $result = $parser->parse($matches[11][0]);
                        $brows=$result->ua->family;
                        $os=$result->os->toString();
                        $archi=($this::strpos_array($matches[11][0],$this->x64))?"x64":"x32";
                    }

                    $create_data[] = [
                        $matches[1][0], //ip
                        date("Y-m-d h:i:s", strtotime(str_replace("/", " ", $matches[2][0]) . " " . $matches[3][0] . " UTC" . $matches[4][0])), //date
                        substr($matches[6][0],0,253), //url
                        substr($matches[11][0],0,253), //useragent
                        $os, //os
                        $archi, //archi
                        $brows //browser
                    ];
                }
            }
            fclose($fd);

            $prom1 = microtime(true);
            $delta = $prom1 - $start;
            echo "Сформировали массив: ".$delta . " сек.\n";
            if($this::kama_create_csv_file( $create_data, "'.$this->safedir.'/tempfile.csv","$","\n")) {
                $prom2 = microtime(true);
                $delta = $prom2 - $prom1;
                echo "Сохранили файл: " . $delta . " сек.\n";

                Yii::$app->db->createCommand('
                LOAD DATA INFILE "' . $this->safedir . '/tempfile.csv" INTO TABLE log FIELDS TERMINATED BY "$" IGNORE 1 ROWS (ip, date, url, useragent, os, archi, browser);
            ')->execute();

                $prom3 = microtime(true);
                $delta = $prom3 - $prom2;
                echo "Записали в бд: " . $delta . " сек.\n";
            }
            else{
                echo "Не поддерживаемый формат данных в лог файле.\n";
            }
            unset($create_data);
            break;
        }
        $finish = microtime(true);
        $delta = $finish - $start;
        echo 'ИТОГО: '.$delta . ' сек.';

        return ExitCode::OK;
    }

    // поиск элементов массива в строке
    private function strpos_array($haystack, $needles) {
        if ( is_array($needles) ) {
            foreach ($needles as $str) {
                if ( is_array($str) ) {
                    $pos = strpos_array($haystack, $str);
                } else {
                    $pos = strpos($haystack, $str);
                }
                if ($pos !== FALSE) {
                    return $pos;
                }
            }
        } else {
            return strpos($haystack, $needles);
        }
    }

    ## Создает CSV файл из переданных в массиве данных.
    ## @param array  $create_data  Массив данных из которых нужно созать CSV файл.
    ## @param string $file         Путь до файла 'path/to/test.csv'. Если не указать, то просто вернет результат.
    ## @return string/false        CSV строку или false, если не удалось создать файл.
    ## ver 2
    private function kama_create_csv_file( $create_data, $file = null, $col_delimiter = ';', $row_delimiter = "\r\n" ){

        if( ! is_array($create_data) )
            return false;

        if( $file && ! is_dir( dirname($file) ) )
            return false;

        // строка, которая будет записана в csv файл
        $CSV_str = '';

        // перебираем все данные
        foreach( $create_data as $row ){
            $cols = array();

            foreach( $row as $col_val ){
                // строки должны быть в кавычках ""
                // кавычки " внутри строк нужно предварить такой же кавычкой "
                if( $col_val && preg_match('/[",;\r\n]/', $col_val) ){
                    // поправим перенос строки
                    if( $row_delimiter === "\r\n" ){
                        $col_val = str_replace( "\r\n", '\n', $col_val );
                        $col_val = str_replace( "\r", '', $col_val );
                    }
                    elseif( $row_delimiter === "\n" ){
                        $col_val = str_replace( "\n", '\r', $col_val );
                        $col_val = str_replace( "\r\r", '\r', $col_val );
                    }

                    $col_val = str_replace( '"', '""', $col_val ); // предваряем "
                    $col_val = '"'. $col_val .'"'; // обрамляем в "
                }

                $cols[] = $col_val; // добавляем колонку в данные
            }

            $CSV_str .= implode( $col_delimiter, $cols ) . $row_delimiter; // добавляем строку в данные
        }

        $CSV_str = rtrim( $CSV_str, $row_delimiter );

        // задаем кодировку windows-1251 для строки
        if( $file ){
            //$CSV_str = iconv( "UTF-8", "cp1251",  $CSV_str );

            // создаем csv файл и записываем в него строку
            $done = file_put_contents( $file, $CSV_str );

            return $done ? $CSV_str : false;
        }

        return $CSV_str;

    }
}
