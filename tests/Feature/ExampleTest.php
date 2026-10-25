<?php

test('returns a successful response for the student login page', function () {
    $response = $this->get(route('student.login'));

    $response->assertOk();
});
