<?php

return new \Phalcon\Config(array(
    'name' => 'Frontend',
    'main-route' => "frontend",
    "FACEBOOK_URL" => "https://www.facebook.com/DEmodemo",

    //test dev
    "FACEBOOK_ID" => "647933852042274",
    "FACEBOOK_SECRECT" => "e6ecbe48f622328355d159d0a011a2df",
    //fb app real
//    "FACEBOOK_ID" => "646003998901926",
//    "FACEBOOK_SECRECT" => "194f9fb38ef9d5aa883da55127688282",

    "GOOGLE_NAME" => "VATC",
    "GOOGLE_ID" => "549111456694-vfqd3cjnssbo0aj1o3kuck235a9d3gul.apps.googleusercontent.com",
    "GOOGLE_API_KEY" => "ash1-wo-jI4Lt-ef9NOoyrel",

    //acc test send mail
//    "ACC_GMAIL_SEND_MAIL" => "namtrungprofile.net@gmail.com",
//    "PASS_GMAIL_SEND_MAIL" => "trannamtrung",
//    "NO_REPLY" => "no-reply@namtrung-profile.net",

    //acc real send mail
    "ACC_GMAIL_SEND_MAIL"=>"vatc.khoidongtuonglai@gmail.com",
    "PASS_GMAIL_SEND_MAIL"=>"2016vatckhoidongtuonglai",
    "NO_REPLY"=>"no-reply@vatc.khoidongtuonglai",
));
