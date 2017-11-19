<#1>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Answer/class.xaseAnswer.php');
xaseAnswer::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Rating/class.xaseRating.php');
xaseRating::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Comment/class.xaseComment.php');
xaseComment::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseHint.php');
xaseHint::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Question/class.xaseQuestion.php');
xaseQuestion::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseHintLevel.php');
xaseHintLevel::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Point/class.xasePoint.php');
xasePoint::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/SampleSolution/class.xaseSampleSolution.php');
xaseSampleSolution::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSetting.php');
xaseSetting::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingM1.php');
xaseSettingM1::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Setting/class.xaseSettingM2.php');
xaseSettingM2::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Voting/class.xaseVoting.php');
xaseVoting::updateDB();

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessment.php');
xaseAssessment::updateDB();
?>
<#2>
<?php
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseUsedHintLevel.php';
xaseUsedHintLevel::updateDB();
?>
<#3>
<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Assessment/class.xaseAssessment.php');
xaseAssessment::updateDB();
?>
<#4>
<?php
require_once './Customizing/global/plugins/Services/Repository/RepositoryObject/AssistedExercise/classes/Hint/class.xaseUsedHintLevel.php';
xaseUsedHintLevel::updateDB();
?>