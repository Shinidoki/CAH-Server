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
        return [
            'success' => true,
            'settings' => [
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
        return [
            'success' => true
        ];
    }

    public function actionImportJson()
    {
        \Yii::$app->response->format = Response::FORMAT_HTML;
        $JSON = '{"blackCards":[{"pick":1,"text":"Trump\'s great!  Trump\'s got _.  I love that."},{"pick":1,"text":"According to Arizona\'s stand-your-ground law, you\'re allowed to shoot someone if they\'re _."},{"pick":1,"text":"It\'s 3AM.  The red phone rings.  It\'s _.  Who do you want answering?"}],"whiteCards":["Actually voting for Donald Trump to be President of the actual United States.","A liberal bias.","Hating Hillary Clinton.","Growing up and becoming a Republican.","Courageously going ahead with that racist comment.","Dispelling this fiction that Barack Obama doesn\'t know what he\'s doing.","Jeb!","The good, hardworking people of Dubuque, Iowa.","Conservative talking points.","Shouting the loudest","Sound of fiscal policy.","Full-on socialism."],"trumpvote":{"name":"Vote for Trump Pack","black":[0,1,2],"white":[0,1,2,3,4,5,6,7,8,9,10,11],"icon":"bullhorn"},"order":["trumpvote"]}';

        $JSON = Json::decode($JSON);

        VarDumper::dump($JSON, 10, true);

        foreach ($JSON['order'] as $category_short) {
            $category = $JSON[$category_short];
            $cat = new Category();
            $cat->name = $category['name'];
            //$cat->save();

            foreach ($category['black'] as $black) {
                $crd_black = $JSON['blackCards'][$black];
                $crd = new Card();
                $crd->is_black = 1;
                $crd->text = $crd_black['text'];
                //TODO Pick-Zahl ergänzen
                $crd->save();
            }

            foreach ($category['white'] as $white) {
                $crd_white = $JSON['blackCards'][$white];
                $crd = new Card();
                $crd->is_black = 1;
                $crd->text = $crd_white['text'];
                //TODO Pick-Zahl ergänzen
                $crd->save();
            }
        }
//        foreach ($JSON['blackCards'] as $blackCard)
//        {
//            $crd = new Card();
//            $crd->text = $blackCard['text'];
//            $crd->is_black = 1;
//            $crd->save();
//        }
    }
}