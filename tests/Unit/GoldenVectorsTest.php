<?php

declare(strict_types=1);

it('hands each case of a fixture to the test as one array', function (array $case) {
    expect($case)->toHaveKeys(['in', 'out']);
})->with(goldenVectors('example'));
