<?php
/**
* Created by PhpStorm.
* User: kit
* Date: 10/19/2015
* Time: 10:21 PM
*/

?>

<!-- SPA Params from server -->
<script type="application/javascript">
var app = app || {};
app.singedIn = <?= (!Yii::$app->user->isGuest ? "true" : "false") ?>
</script>
