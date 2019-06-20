<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ListView;
use phpnt\chartJS\ChartJs;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;

/* @var $this yii\web\View */
/* @var $searchModel app\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Access Logs';
$this->params['breadcrumbs'][] = $this->title;

?>
<h1>Logs</h1>
<?php
$masCnt = array_column($masDate, 'cnt', 'date');
$masCntBrows = array_column($masDateTopBrowsers, 'cnt', 'date');
$start = new DateTime($masDate[0]['date']);
$interval = new DateInterval('P1D');
$end = new DateTime(end($masDate)['date']. "+1day");
$period = new DatePeriod($start, $interval, $end);

// При переборе экземпляра DatePeriod в цикле будут отображены все отобранные даты
// периода.
$dates=[];
$data=[];
$databr=[];
foreach ($period as $date) {
    $dates[]=$date->format('d.m.y');
    $zn=0;
    $znbr=0;
    //debmes($date->format('y-m-d'));
    $zn=$masCnt[$date->format('y-m-d')] ?? 0;
    $data[]=$zn;
    $znbr=$masCntBrows[$date->format('y-m-d')] ?? 0;
    if($zn!=0) $znbr=round($znbr/$zn,2)*100;
    $databr[]=$znbr;
}

$dataWeatherOne = [
    //'labels' => ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь", "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
    'labels' => $dates,
    'datasets' =>[
        [
            'data' => $data,
            'label' =>  "Общее число запросов",
            'fill' => false,
            'lineTension' => 0.1,
            'backgroundColor' => "rgba(75,192,192,0.4)",
            'borderColor' => "rgba(75,192,192,1)",
            'borderCapStyle' => 'butt',
            'borderDash' => [],
            'borderDashOffset' => 0.0,
            'borderJoinStyle' => 'miter',
            'pointBorderColor' => "rgba(75,192,192,1)",
            'pointBackgroundColor' => "#fff",
            'pointBorderWidth' => 1,
            'pointHoverRadius' => 5,
            'pointHoverBackgroundColor' => "rgba(75,192,192,1)",
            'pointHoverBorderColor' => "rgba(220,220,220,1)",
            'pointHoverBorderWidth' => 2,
            'pointRadius' => 1,
            'pointHitRadius' => 10,
            'spanGaps' => false,
            'yAxisID'=> 'y-axis-1'
        ],
        [
            'data' => $databr,
            'label' =>  "Доля (%) для трех самых популярных браузеров: ".implode(", ", $topBrowser),
            'fill' => false,
            'lineTension' => 0.1,
            'backgroundColor' => "rgba(255, 234, 0,0.4)",
            'borderColor' => "rgba(255, 234, 0,1)",
            'borderCapStyle' => 'butt',
            'borderDash' => [],
            'borderDashOffset' => 0.0,
            'borderJoinStyle' => 'miter',
            'pointBorderColor' => "rgba(255, 234, 0,1)",
            'pointBackgroundColor' => "#fff",
            'pointBorderWidth' => 1,
            'pointHoverRadius' => 5,
            'pointHoverBackgroundColor' => "rgba(255, 234, 0,1)",
            'pointHoverBorderColor' => "rgba(220,220,220,1)",
            'pointHoverBorderWidth' => 2,
            'pointRadius' => 1,
            'pointHitRadius' => 10,
            'spanGaps' => false,
            'yAxisID' => 'y-axis-2'
        ]
    ]
];
?>
<br>
<h3>Фильтр</h3>
<?
echo $this->render('_search',['model'=>$searchModel]);

// вывод графиков
?>
<br>
<h3>График</h3>
<?
echo ChartJs::widget([
    'type'  => ChartJs::TYPE_LINE,
    'data'  => $dataWeatherOne,
    'options'   => [
        'responsive'=> true,
        'hoverMode'=> 'index',
        'stacked' => false,
        'title' => [
            'display' => true,
            'text' => 'График количество запросов'
        ],
        'scales' => [
            'yAxes'=> [[
                'type' => 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                'display' => true,
                'position' => 'left',
                'id' => 'y-axis-1',
            ],
                [
                    'type' => 'linear', // only linear but allow scale type registration. This allows extensions to exist solely for log scale for instance
                    'display' => true,
                    'position' => 'right',
                    'id' => 'y-axis-2',
                    // grid line settings
                    'gridLines' => [
                        'drawOnChartArea' => false, // only want the grid lines for one axis to show up
                    ],
                ]]
        ],
    ]
]);

?>
<div class="log-index">
    <br>
    <h3>Таблица</h3>
    <?php
    $dataProvider = new ArrayDataProvider([
        'allModels' => $masDate,
        'sort' => [
            'attributes' => ['Дата', 'Число запросов', 'Самый популярный браузер','Самый популярный URL'],
        ],
        'pagination' => [
            'pageSize' => 10,
        ],
    ]);
    ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['attribute'=>'Дата','value'=>'date'],
            ['attribute'=>'Число запросов','value'=>'cnt'],
            ['attribute'=>'Самый популярный браузер','value'=>'browser'],
            ['attribute'=>'Самый популярный URL','value'=>'url'],
        ],
    ]); ?>
</div>
