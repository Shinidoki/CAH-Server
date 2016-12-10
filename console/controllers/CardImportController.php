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

        $myvars = 'decks%5B%5D=Base&decks%5B%5D=CAHe1&decks%5B%5D=CAHe2&decks%5B%5D=CAHe3&decks%5B%5D=CAHe4&decks%5B%5D=CAHe5&decks%5B%5D=CAHe6&decks%5B%5D=90s&decks%5B%5D=Box&decks%5B%5D=fantasy&decks%5B%5D=food&decks%5B%5D=science&decks%5B%5D=www&decks%5B%5D=hillary&decks%5B%5D=trumpvote&decks%5B%5D=xmas2012&decks%5B%5D=xmas2013&decks%5B%5D=PAXE2013&decks%5B%5D=PAXP2013&decks%5B%5D=PAXE2014&decks%5B%5D=PAXEP2014&decks%5B%5D=PAXPP2014&decks%5B%5D=PAX2015&decks%5B%5D=HOCAH&decks%5B%5D=reject&decks%5B%5D=reject2&decks%5B%5D=Canadian&decks%5B%5D=misprint&type=JSON';

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
}