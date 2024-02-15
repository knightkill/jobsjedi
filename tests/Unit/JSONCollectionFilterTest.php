<?php

dataset('test_array', [
    collect([
        [
            "title" => "php",
            "age" => 10,
            "hobbies" => ["snooker", "carrom", "reading"]
        ],
        [
        "title" => "html",
        "age" => 20,
        "hobbies" => ["dreaming", "swimming", "trekking"]
        ],
        [
            "title" => "dfm",
            "age" => 14,
            "hobbies" => ["music", "reading", "trekking"]
        ]
    ])
]);

it('can filter like operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "field" : "title",
            "operator" : "like",
            "value" : "tm"
        }
    ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(1)
        ->and($result->first())
        ->toHaveKey('title', 'html');

})->with('test_array');


it('can filter equals operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "field" : "age",
            "operator" : "==",
            "value" : 14
        }
      ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(1)
        ->and($result->first())
        ->toHaveKey('age', 14);
    ;
})->with('test_array');

it('can filter less then operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "field" : "age",
            "operator" : "<",
            "value" : 14
        }
      ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(1)
        ->and($result->first())
        ->toHaveKey('age', 10);
    ;
})->with('test_array');

it('can filter and operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "operator" : "and",
            "value" : [
                {
                  "field" : "title",
                  "operator" : "like",
                  "value" : "m"
                },
                {
                  "field" : "age",
                  "operator" : "<",
                  "value":15
                }
            ]
        }
      ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(1)
        ->and($result->first())
        ->toHaveKey('title', 'dfm');
    ;
})->with('test_array');


it('can filter array as and operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "field" : "title",
            "operator" : "like",
            "value" : "m"
        },
        {
          "field" : "age",
          "operator" : "<",
          "value":15
        }
      ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(1)
        ->and($result->first())
        ->toHaveKey('title', 'dfm');
})->with('test_array');

it('can filter or operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "operator" : "or",
            "value" : [
                {
                    "field" : "title",
                    "operator" : "like",
                    "value" : "m"
                },
                {
                  "field" : "age",
                  "operator" : ">",
                  "value":15
                }
            ]
        }
      ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(2)
        ->and($result->first())
        ->toHaveKey('title', 'html')
        ->toHaveKey('age', 20)
        ->and($result->get(1))
        ->toHaveKey('title', 'dfm')
    ;
})->with('test_array');

it('can filter in_array operator', function ($test_array) {

    $result = (new \App\Libraries\JSONCollectionFilter('[
        {
            "field" : "hobbies",
            "operator" : "in_array",
            "value" : "trekking"
        }
      ]'))->filter($test_array);
    expect($result)
        ->toHaveCount(2)
        ->and($result->first()['hobbies'])
        ->toContain('trekking');
})->with('test_array');
