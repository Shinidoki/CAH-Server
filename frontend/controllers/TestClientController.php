<?php

namespace frontend\controllers;


use backend\models\Game;
use backend\models\User;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class TestClientController extends Controller
{

    public function beforeAction($action)
    {
        if ($action->id != 'index') {
            $user = \Yii::$app->session->get('cah-clientToken');

            if (empty($user)) {
                return $this->redirect('index');
            }
        }

        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $sessionToken = \Yii::$app->session->get('cah-clientToken');

        if (!empty($sessionToken)) {
            return $this->redirect(Url::toRoute('test-client/home'));
        }

        $dynModel = \Yii::$app->request->post('DynamicModel');
        $name = isset($dynModel['Name']) ? $dynModel['Name'] : '';
        $auth = new DynamicModel([
            'Name' => $name
        ]);
        $auth->addRule('Name', 'required');
        $auth->addRule('Name', 'string', ['max' => 40]);

        if (!empty($dynModel)) {
            if ($auth->validate()) {
                $result = \Yii::$app->runAction('lobby/authenticate', ['name' => $name]);
                if ($result['success']) {
                    \Yii::$app->session->set('cah-clientToken', $result['clientToken']);
                    \Yii::$app->session->setFlash('success', 'Login successful!');
                    return $this->redirect(Url::toRoute('test-client/home'));
                } else {
                    \Yii::$app->session->setFlash('error', $result['errors'][0]);
                }
            } else {
                \Yii::$app->session->setFlash('error', $auth->getFirstError('Name'));
            }
        }


        return $this->render('index', ['model' => $auth]);
    }

    public function actionHome()
    {
        /** @var User $user */
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $user = User::find()->where(['generated_id' => $clientToken])->one();

        $dataprovider = new ActiveDataProvider([
            'query' => Game::find()->with(['gameusers', 'hostUser']),
            'sort' => ['defaultOrder' => ['game_id' => SORT_DESC]],
            'pagination' => ['pageSize' => 10]
        ]);

        return $this->render('home', ['user' => $user, 'dataProvider' => $dataprovider]);
    }

    public function actionJoin($id)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $result = \Yii::$app->runAction('lobby/join-lobby', ['clientToken' => $clientToken, 'gameId' => $id]);

        if ($result['success']) {
            return $this->redirect(Url::toRoute('test-client/lobby'));
        }

        \Yii::$app->session->setFlash('error', $result['errors'][0]);
        return $this->redirect(Url::toRoute('test-client/home'));
    }

    public function actionLobby()
    {
        /** @var User $user */
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $user = User::find()->where(['generated_id' => $clientToken])->one();

        /** @var Game $game */
        $game = $user->getGames()->with(['categories', 'users'])->one();
        $result = \Yii::$app->runAction('game/get-current-chosen-cards', ['clientToken' => $clientToken, 'gameId' => $game->game_id]);
        $chosenCards = empty($result['cards']) ? [] : $result['cards'];

        \Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('lobby', ['user' => $user, 'game' => $game, 'chosenCards' => $chosenCards]);
    }

    public function actionDraw($id)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');

        $result = \Yii::$app->runAction('card/draw-card', ['clientToken' => $clientToken, 'gameId' => $id]);

        if ($result['success']) {
            \Yii::$app->session->setFlash('success', "Cards Drawn");
        } else {
            \Yii::$app->session->setFlash('error', $result['errors'][0]);
        }

        return $this->redirect(Url::toRoute('test-client/lobby'));
    }

    public function actionCreateGame($gameName = null)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $result = \Yii::$app->runAction('lobby/create-lobby', ['clientToken' => $clientToken, 'gameName' => $gameName]);

        if ($result['success']) {
            \Yii::$app->session->setFlash('success', "Game created");
        } else {
            \Yii::$app->session->setFlash('error', $result['errors'][0]);
        }
        
        return $this->redirect(Url::toRoute('test-client/lobby'));
    }

    public function actionStart($id)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $result = \Yii::$app->runAction('lobby/start-game', ['gameId' => $id, 'clientToken' => $clientToken]);

        if ($result['success']) {
            \Yii::$app->session->setFlash('success', "Game started");
        } else {
            \Yii::$app->session->setFlash('error', $result['errors'][0]);
        }
        
        return $this->redirect(Url::toRoute('test-client/lobby'));
    }

    public function actionPlayCard($game, $card)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $result = \Yii::$app->runAction('game/play-card', ['clientToken' => $clientToken, 'gameId' => $game, 'cardId' => $card]);

        if ($result['success']) {
            \Yii::$app->session->setFlash('success', "Card played");
        } else {
            \Yii::$app->session->setFlash('error', $result['errors'][0]);
        }

        return $this->redirect(Url::toRoute('test-client/lobby'));
    }

    public function actionSelectCard($game, $card)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $result = \Yii::$app->runAction('game/choose-winner', ['clientToken' => $clientToken, 'gameId' => $game, 'cardId' => $card]);

        if ($result['success']) {
            \Yii::$app->session->setFlash('success', "Winner chosen!");
            return $this->redirect(Url::toRoute('test-client/lobby'));
        } else {
            \Yii::$app->session->setFlash('error', $result['errors'][0]);
        }
        
        return $this->redirect(Url::toRoute('test-client/lobby'));
    }

    public function actionNextRound($id)
    {
        $clientToken = \Yii::$app->session->get('cah-clientToken');
        $result = \Yii::$app->runAction('game/next-round', ['clientToken' => $clientToken, 'gameId' => $id]);

        if ($result['success']) {
            \Yii::$app->session->setFlash('success', "Next Round started!");
        } else {
            \Yii::$app->session->setFlash('error', $result['errors'][0]);
        }

        return $this->redirect(Url::toRoute('test-client/lobby'));
    }
}