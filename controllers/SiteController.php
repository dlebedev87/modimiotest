<?php

namespace app\controllers;

use Yii;
use app\models\Log;
use app\models\LogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;
use yii\db;

/**
 * SiteController implements the CRUD actions for Log model.
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Log models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $query = Log::find();
        $topBrowser=[];
        $masTopBrowsers = $query->select("browser")
            ->where($dataProvider->query->where)
            ->groupBy("browser")
            ->orderBy('count(1) desc')
            ->limit(3)
            ->asArray()
            ->all();
        foreach($masTopBrowsers as $TopBrowser){
            $topBrowser[] = $TopBrowser['browser'];
        }

        $query = Log::find();
        $masDateTopBrowsers = $query->select(["DATE_FORMAT(date,'%y-%m-%d') date", "count(1) cnt"])
            ->where(
                [
                    'browser'=>$topBrowser,
                ]
            )
            ->where($dataProvider->query->where)
            ->groupBy(["DATE_FORMAT(date,'%y-%m-%d')"])
            ->asArray()
            ->all();

        $query = Log::find();
        $subQueryBrowser = (new db\Query())
            ->select('browser')
            ->where($dataProvider->query->where)
            ->from('Log')
            ->groupBy("browser")
            ->orderBy('count(1) desc')
            ->limit(1);
        $subQueryurl = (new db\Query())
            ->select('url')
            ->where($dataProvider->query->where)
            ->from('Log')
            ->groupBy("url")
            ->orderBy('count(1) desc')
            ->limit(1);
        $masDate = $query->select(["DATE_FORMAT(date,'%y-%m-%d') date", "count(1) cnt",'browser'=>$subQueryBrowser,'url'=>$subQueryurl])
            ->where($dataProvider->query->where)
            ->groupBy(["DATE_FORMAT(date,'%y-%m-%d')"])
            ->orderBy("date")
            ->asArray()
            ->all();

        return $this->render('index',compact(masDateTopBrowsers,masDate,topBrowser,searchModel,dataProvider));

    }

    /**
     * Displays a single Log model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Log model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Log();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Log model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Log model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Log the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Log::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
