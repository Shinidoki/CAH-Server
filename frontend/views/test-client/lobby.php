<?php
/* @var $this yii\web\View */
/** @var $user \backend\models\User */
/** @var $game \backend\models\Game */
/** @var $chosenCards array */

$this->registerAssetBundle(\rmrevin\yii\fontawesome\AssetBundle::className());
$this->registerAssetBundle(\frontend\assets\CahAsset::className());
\yii\widgets\Pjax::begin([
        'id' => 'gameLobby'
]);

$this->registerJs(' 
    setTimeout(function() {
    $.pjax.reload({container:"#gameLobby"});
}, 2000);
    ', \yii\web\VIEW::POS_HEAD);
?>
<?= \common\widgets\Alert::widget() ?>
<div class="row">
    <div class="col-xs-3">
        <?php
        $back = \yii\bootstrap\Html::button(
            \rmrevin\yii\fontawesome\FA::icon('chevron-left') . ' Back to lobby screen',
            ['class' => 'btn btn-default']
        );
        echo \yii\bootstrap\Html::a($back, \yii\helpers\Url::toRoute('test-client/home'));
        ?>
    </div>
    <div class="col-xs-3">
        <?php
        $back = \yii\bootstrap\Html::button(
            \rmrevin\yii\fontawesome\FA::icon('hand-paper-o') . ' Draw Card(s)',
            ['class' => 'btn btn-primary']
        );
        echo \yii\bootstrap\Html::a($back, \yii\helpers\Url::toRoute('test-client/draw') . '?id=' . $game->game_id);
        ?>
    </div>
    <div class="col-xs-3">
        <?php
        if ($game->host_user_id == $user->user_id) {
            switch ($game->state) {
                case \backend\models\Game::STATE_INLOBBY:
                    $start = \yii\bootstrap\Html::button(
                        \rmrevin\yii\fontawesome\FA::icon('play') . ' Start Game',
                        ['class' => 'btn btn-success']
                    );
                    echo \yii\bootstrap\Html::a($start, \yii\helpers\Url::toRoute('test-client/start') . '?id=' . $game->game_id);
                    break;
            }
        }
        if ($user->is_judge && $game->state === \backend\models\Game::STATE_END_OF_ROUND) {
            $start = \yii\bootstrap\Html::button(
                \rmrevin\yii\fontawesome\FA::icon('forward') . ' Next Round',
                ['class' => 'btn btn-primary']
            );
            echo \yii\bootstrap\Html::a($start, \yii\helpers\Url::toRoute('test-client/next-round') . '?id=' . $game->game_id);
        }
        ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 col-xs-12">
        <h2><?= $game->game_name ?></h2>
        <table class="table table-hover">
            <tbody>
            <tr>
                <th>ID</th>
                <td><?= $game->game_id ?></td>
            </tr>
            <tr>
                <th>Created</th>
                <td><?= date('d.m.Y H:i:s', strtotime($game->create_date)) ?></td>
            </tr>
            <tr>
                <th>Last Activity</th>
                <td><?= date('d.m.Y H:i:s', strtotime($game->last_activity)) ?></td>
            </tr>
            <tr>
                <th>State</th>
                <td><?= $game->translateState($game->state) ?></td>
            </tr>
            <tr>
                <th>Host</th>
                <td><?= $game->hostUser->user_name ?></td>
            </tr>
            <tr>
                <th>Cardpacks</th>
                <td><?php foreach ($game->categories as $category) {
                        echo $category->name . '; ';
                    } ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-sm-6 col-xs-12">
        <h2><?= count($game->gameusers) ?> Players</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Score</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($game->users as $gUser) {
                ?>
                <tr>
                    <td><?= $gUser->user_id ?></td>
                    <td><?= $gUser->user_name . ($gUser->is_judge ? ' ' . \rmrevin\yii\fontawesome\FA::icon('star') : '') ?></td>
                    <td><?= $gUser->score ?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="cah-card cah-black col-xs-offset-4 col-xs-3">
        <div class="cah-textcontainer">
        <span class="cah-cardtext">
        <?php if (empty($game->getCurrentBlackCard())) { ?>
            NO CARD DRAWN
        <?php } else {
            echo $game->getCurrentBlackCard()->text;
        } ?>
        </span>
        </div>
    </div>
</div>

<?php if (!empty($chosenCards)) : ?>
    <h3>Chosen Cards:</h3>

    <?php
    foreach ($chosenCards as $chosenSet) {
        echo "<div class=\"row\">";
        foreach ($chosenSet as $chosenCard) {
            ?>

            <a href="<?= \yii\helpers\Url::toRoute('test-client/select-card') . '?game=' . $game->game_id . '&card=' . $chosenCard['card_id'] ?>">
                <div class="cah-card cah-white col-xs-3">
                    <div class="cah-textcontainer">
                        <span class="cah-cardtext">
                            <?= $chosenCard['text'] ?>
                        </span>
                    </div>
                </div>
            </a>
            <?php
        }
        echo "</div>";
    }
    ?>

<?php endif; ?>

<?php if ($user->is_judge !== 1) : ?>
    <h3>Your Hand:</h3>
    <div class="row">
        <?php $handCards = $user->getHandCards();
        if (empty($handCards)) { ?>
            <span class="text-center"> DRAW CARDS! </span>
        <?php } else {
            /** @var \backend\models\Gamecards[] $handCards */
            foreach ($handCards as $gamecard) { ?>
                <a href="<?= \yii\helpers\Url::toRoute('test-client/play-card') . '?game=' . $game->game_id . '&card=' . $gamecard->card_id ?>">
                    <div class="cah-card cah-white col-xs-3">
                        <div class="cah-textcontainer">
                            <span class="cah-cardtext">
                                <?= $gamecard->card->text ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php }
        } ?>
    </div>
<?php endif; ?>

<?php
\yii\widgets\Pjax::end();
