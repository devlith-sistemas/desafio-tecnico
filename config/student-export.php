<?php

return [
    'chunk_size' => (int) env('STUDENT_EXPORT_CHUNK_SIZE', 1000),
    'disk' => env('STUDENT_EXPORT_DISK', 'local'),
    'directory' => env('STUDENT_EXPORT_DIRECTORY', 'exports/students'),
    'queue' => env('STUDENT_EXPORT_QUEUE', env('DB_QUEUE', 'default')),
];
