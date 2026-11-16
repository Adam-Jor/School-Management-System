<?php

test('indonesian number and date helpers format values correctly', function () {
    expect(format_number(10000))->toBe('10.000')
        ->and(format_number(1042))->toBe('1.042')
        ->and(format_date('2024-02-01'))->toBe('1 Feb 2024');
});
