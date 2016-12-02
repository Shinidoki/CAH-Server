<?php
namespace frontend\controllers;


use backend\models\Card;
use backend\models\Category;
use backend\models\Game;
use backend\models\User;
use yii\helpers\Json;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;

class StaticController extends Controller
{
    public function beforeAction($action)
    {
//        if(!\Yii::$app->request->isAjax){
//            throw new ForbiddenHttpException("This Site is not available for web access!");
//        }
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionAuthenticate()
    {
        return [
            'success' => true,
            'clientToken' => md5(mt_rand())
        ];
    }

    public function actionGetLobbyState()
    {
        $user = new User();
        $user->user_name = "pWnH";
        $user->last_activity = date("Y-m-d h:i:s");
        $user->user_id = 0;

        $user2 = new User();
        $user2->user_name = "Kabanak";
        $user2->last_activity = date("Y-m-d h:i:s");
        $user2->user_id = 1;

        $user3 = new User();
        $user3->user_name = "Shinidoki";
        $user3->last_activity = date("Y-m-d h:i:s");
        $user3->user_id = 2;

        $user2 = new User();
        $user2->user_name = "Kabanak";
        $user2->last_activity = date("Y-m-d h:i:s");
        $user2->user_id = 1;

        $user3 = new User();
        $user3->user_name = "Shinidoki";
        $user3->last_activity = date("Y-m-d h:i:s");
        $user3->user_id = 2;

        return [
            'started' => 1,
            'players' => [
                [
                    $user,
                    $user2,
                    $user3
                ]
            ],
            'settings' => [
                'gameMode' => 1,
                'targetScore' => 10,
                'cardPacks' => [1,6,4],
                'language' => 'DE',
                'kickTimer' => 180
            ]
        ];
    }

    public function actionGetLobbies()
    {
        $game = new Game();
        $game->game_id = 42;
        $game->create_date = date("Y-m-d h:i:s");

        return [
            'success' => true,
            'lobbies' => [$game]
        ];
    }

    public function actionUsers()
    {
        $user = new User();
        $user->user_name = "pWnH";
        $user->last_activity = date("Y-m-d h:i:s");
        $user->user_id = 0;

        $user2 = new User();
        $user2->user_name = "Kabanak";
        $user2->last_activity = date("Y-m-d h:i:s");
        $user2->user_id = 1;

        $user3 = new User();
        $user3->user_name = "Shinidoki";
        $user3->last_activity = date("Y-m-d h:i:s");
        $user3->user_id = 2;

        return [
            $user,
            $user2,
            $user3
        ];
    }

    public function actionSetLobbySettings()
    {
        return[
            'success' => true,
            'settings' =>[
                'gamemode' => 1,
                'targetscore' => 5000,
                'cardpacks' => [
                    1,
                    2
                ],
                'kicktimer' => 0
            ]
        ];
    }

    public function actionRemoveFromLobby()
    {
        return[
            'success' => true
        ];
    }

    public function actionImportJson()
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;

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

        foreach ($JSON['order'] as $category_short)
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
        }
    }
}