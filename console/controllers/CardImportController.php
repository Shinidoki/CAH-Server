<?php
/**
 * Created by PhpStorm.
 * User: Tony
 * Date: 02.12.2016
 * Time: 09:40
 */

namespace console\controllers;


use yii\base\Exception;
use yii\console\Controller;
use backend\models\Card;
use backend\models\Category;
use yii\helpers\Console;
use yii\helpers\Json;
use yii\helpers\VarDumper;

class CardImportController extends Controller
{
    public function actionImportJson()
    {
        $url = 'http://www.crhallberg.com/cah/output.php';

        $myvars = 'decks[]=Base&type=JSON';
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );

        $JSON = Json::decode($response);

        $transaction = \Yii::$app->db->beginTransaction();
        try
        {
            Card::deleteAll();
            Category::deleteAll();

            $totalOrder = count($JSON['order']);
            Console::startProgress(0, $totalOrder);
            foreach ($JSON['order'] as $i => $category_short)
            {
                $category = $JSON[$category_short];
                $cat = new Category();
                $cat->name = $category['name'];
                $cat->save();

                foreach ($category['black'] as $black)
                {
                    $crd_black = $JSON['blackCards'][$black];
                    $crd = new Card();
                    $crd->is_black = 1;
                    $crd->text = $crd_black['text'];
                    $crd->blanks = $crd_black['pick'];
                    if ($crd->save()) {
                        $crd->setIsNewRecord(false);
                        $crd->link('cats', $cat);
                    }else{
                        VarDumper::dump($crd->errors, 10, true);
                        die;
                    }
                }

                foreach ($category['white'] as $white)
                {
                    $crd_white = $JSON['whiteCards'][$white];
                    $crd = new Card();
                    $crd->text = $crd_white;
                    $crd->is_black = 0;
                    if ($crd->save()) {
                        $crd->setIsNewRecord(false);
                        $crd->link('cats', $cat);
                    }else{
                        VarDumper::dump($crd->errors, 10, true);
                        die;
                    }
                }
                Console::updateProgress($i+1, $totalOrder);
            }
            $transaction->commit();
            Console::endProgress();
        }
        catch(Exception $e)
        {
            $transaction->rollBack();
            Console::output($e->getMessage());
        }
    }

    public function actionImportHangoutsCards()
    {
        $url = 'https://raw.githubusercontent.com/samurailink3/hangouts-against-humanity/master/source/data/cards.js';
        /** @var Category[] $categories */
        $categories = [];

        $response = file_get_contents($url);

        $response = str_replace('masterCards = ', '', $response);
        $response = str_replace('\\\'', '\'', $response);
        $response = str_replace('\”', '\"', $response);


        $JSON = Json::decode($response);

        $transaction = \Yii::$app->db->beginTransaction();

        try{
            Card::deleteAll();
            Category::deleteAll();

            $total = count($JSON);
            Console::startProgress(0, $total);
            foreach ($JSON as $i => $card)
            {
                if(!empty($categories[$card['expansion']])){
                    $cat = $categories[$card['expansion']];
                } else {
                    $cat = new Category();
                    $cat->name = $card['expansion'];
                    $cat->save();
                    $categories[$card['expansion']] = $cat;
                }

                switch($card['cardType']){
                    case 'Q':
                        $crd = new Card();
                        $crd->is_black = 1;
                        $crd->text = $card['text'];
                        $crd->blanks = $card['numAnswers'];
                        if ($crd->save()) {
                            $crd->setIsNewRecord(false);
                            $crd->link('cats', $cat);
                        }else{
                            VarDumper::dump($crd->errors, 10, true);
                        }
                        break;
                    case 'A':
                        $crd = new Card();
                        $crd->text = $card['text'];
                        $crd->blanks = $card['numAnswers'];
                        $crd->is_black = 0;
                        if ($crd->save()) {
                            $crd->setIsNewRecord(false);
                            $crd->link('cats', $cat);
                        }else{
                            VarDumper::dump($crd->errors, 10, true);
                        }
                }

                Console::updateProgress($i+1, $total);
            }
            $transaction->commit();
            Console::endProgress();
            Console::output("Import erfolgreich!");
        } catch (\Throwable $e){
            $transaction->rollBack();
            Console::output($e->getMessage());
            Console::output($e->getTraceAsString());
        }
    }
}