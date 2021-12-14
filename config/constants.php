<?php
define('DEFAULT_URL','https://api.fourthfrontier.com');
define('SUPER_ADMIN_PASSWORD','4fsuperadmin20!');
define('DEFAULT_DATE_INPUT_FORMAT',DateTime::ISO8601);
define('DEFAULT_DATERANGE_INPUT_FORMAT','Y-m-d');
//RESPONSE TYPE ENUMS
define('RESPONSE_CODE_SUCCESS_OK', 200);
define('RESPONSE_CODE_SUCCESS_CREATED', 201);
define('RESPONSE_CODE_SUCCESS_RECORD_NOT_FOUND', 250);
define('RESPONSE_CODE_ERROR_INSECURE', 505);
define('RESPONSE_CODE_ERROR_BAD', 400);
define('RESPONSE_CODE_ERROR_UNAUTHORIZED', 401);
define('RESPONSE_CODE_ERROR_NOTFOUND', 404);
define('RESPONSE_CODE_ERROR_TIMEOUT', 408);
define('RESPONSE_CODE_ERROR_MISSINGDATA', 428);
define('RESPONSE_CODE_ERROR_NOPERMISSION', 402);
define('RESPONSE_CODE_CONFLICT', 409);

//ENUM GROUPS
define('ENUM_STATUS_GROUP', 1);
define('ENUM_GENDER_GROUP', 2);
define('ENUM_USERLEVEL_GROUP', 3);
define('ENUM_MOBILE_PLATFORM_GROUP', 4);

//ENUMS
define('ENUM_STATUS_INACTIVE', 1);
define('ENUM_STATUS_ACTIVE', 2);
define('ENUM_STATUS_PENDING_VERIFICATION', 8);

define('ENUM_GENDER_FEMALE', 3);
define('ENUM_GENDER_MALE', 4);
/** Note: If a new user level is added, the following are needed:
 * 1. A new seeder to add to the enums table
 * 2. Update in validations Example: app/Http/Controllers/Admin/Users.php:41
 * 3. Group id should be the same
 * 4. If a userlevel has access to other users data, associate those users where needed
 */

define('ENUM_USERLEVEL_DEFAULT',10);
define('ENUM_USERLEVEL_PREMIUM',15);
define('ENUM_USERLEVEL_FHP_PREMIUM',16);
define('ENUM_USERLEVEL_DOCTOR',20);
define('ENUM_USERLEVEL_FHP_DOCTOR',25);
define('ENUM_USERLEVEL_ADMIN',30);

define('ENUM_PLATFORM_TYPE_WEB',5);
define('ENUM_PLATFORM_TYPE_ANDROID',6);
define('ENUM_PLATFORM_TYPE_IOS',7);

//DataSync Enums
define('DATASYNC_SOURCE_WEB',0);
define('DATASYNC_SOURCE_ANDROID',1);
define('DATASYNC_SOURCE_IOS',2);

define('DATASYNC_ACTION_DELETE',0);
define('DATASYNC_ACTION_EDIT',1);

define('NOTIFICATION_TYPE_WORKOUT_UPDATE','WORKOUT_UPDATE');
define('NOTIFICATION_TYPE_INSIGHT_UPDATE','INSIGHT_UPDATE');
define('NOTIFICATION_TYPE_TAG_UPDATE','TAG_UPDATE');

//Defined Constants
define('PAGINATE_CONSTANTS',20);
define('DEFAULT_COMMUNITY_POSTS_PER_PAGE',25);
define('DEFAULT_INSIGHTS_PER_PAGE',50);
define('DEFAULT_TRAINING_GOAL',750);
//PROFTPD
define('PROFTP_DB_USER','ftpd');
define('PROFTP_DB_PASS','rlmruNiszgWstFHw');
define('PROFTP_DB_HOST','data.heartos.org');
define('PROFTP_DB_PORT','3306');


//aws S3
define('AWS_KEY',env('AWS_ACCESS_KEY_ID'));
define('AWS_SECRET',env('AWS_SECRET_ACCESS_KEY'));
define('AWS_S3_BUCKET', env('AWS_BUCKET'));
define('AWS_S3_PREFIX_FIRMWARE', 'firmware/');
define('AWS_S3_APK_VERSION_FILE', 'APK_Version/AndroidBuildInfo.json');
define('AWS_S3_PREFIX_USERDATA', 'users');
define('AWS_S3_PREFIX_USERLOGS', 'userlogs');
define('AWS_S3_PREFIX_USERBIN', 'user_ble_binfiles');
define('AWS_S3_PREFIX_USERMEDICAL', 'user_medical_file');
define('AWS_S3_PREFIX_USERALLBINFILES', 'all_binfiles');

//derived data types
define('DERIVED_DTYPE_STRAIN',4);
define('DERIVED_DTYPE_BREATHRATE',9);
define('DERIVED_DTYPE_EFFORTLOAD',11);

//colors for distribution graphs
define('DISTRIBUTION_COLOR_BLUE1',"#238191");
define('DISTRIBUTION_COLOR_BLUE2',"#2E9392");
define('DISTRIBUTION_COLOR_GREEN1',"#5EA64B");
define('DISTRIBUTION_COLOR_GREEN2',"#B2BD31");
define('DISTRIBUTION_COLOR_YELLOW1',"#F3C22D");
define('DISTRIBUTION_COLOR_YELLOW2',"#FDB02A");
define('DISTRIBUTION_COLOR_ORANGE1',"#FD8227");
define('DISTRIBUTION_COLOR_ORANGE2',"#E36022");
define('DISTRIBUTION_COLOR_RED1',"#BE1E2D");
define('DISTRIBUTION_COLOR_RED2',"#83000E");

//community POsts
define('COMMUNITY_POST_DEFAULT_THUMBNAIL','https://4f-community-images.s3-us-west-2.amazonaws.com/default.jpg');


///Activation base URL
define('ACTIVATION_BASE_URL','/users/activate');

//training load dialogues (temp)
define('TRAINING_LOAD_DIALOGUE_1WEEK',array(
   'blue' => "You can achieve your Target Training Load with more workouts this week. Longer and harder workouts have a higher Training Load.",
   'green' => "Great job! You're close to reaching a Training Load of 750. Achieving a minimum Training load of 750 every week can help improve your cardiorespiratory and muscular fitness, bone health, reduce the risk of high blood pressure, type-2 diabetes as well as improve mental wellness!",
   'darkgreen' => "Great going, your training is keeping you healthy! Achieving a minimum Training Load of 750 every week can help improve your cardiorespiratory and muscular fitness, bone health, reduce the risk of high blood pressure, type-2 diabetes as well as improve mental wellness! Increasing your Training Load within a safe margin leads to greater performance gains.",
   'yellow' => "You're pushing your Weekly Training Load hard! With adequate recovery, your performance gains are maximal in this zone. This zone is best for experienced athletes.",
   'orange' => "You're raising your Weekly Training Load too quickly! Rapidly increasing your Training Load increases your chances of injury and overtraining.",
   'red' => "Your Training Load is significantly higher than your previous week, putting you in a zone with a risk of injury and overtraining. Gradually increase your Training Load to avoid going into the Red Training Load zone.",
));
define('TRAINING_LOAD_DIALOGUE_2WEEK',array(
   'blue' => "You can achieve your Target Training Load with more workouts this week. Longer and harder workouts have a higher Training Load.",
   'green' => "Staying in the Green Training Load Zone is great for maintaining fitness while maximizing recovery from hard training weeks.",
   'darkgreen' => "You've achieved more than last week’s Training Load goal! The size of the dark green zone shows you how much more you achieved compared to last week. Increasing your Training Load within a safe margin leads to greater performance gains.",
   'yellow' => "You're pushing your Weekly Training Load hard! With adequate recovery, your performance gains are maximal in this zone. This zone is best for experienced athletes.",
   'orange' => "You're raising your Weekly Training Load too quickly! Rapidly increasing your Training Load increases your chances of injury and overtraining.",
   'red' => "Your Training Load is significantly higher than your previous week, putting you in a zone with a risk of injury and overtraining. Gradually increase your Training Load to avoid going into the Red Training Load zone.",
));


define('NUMBER_OF_DECIMALS_ROUNDOFF_LOCATION',9);
