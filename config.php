<?php
/**
 * Конфигурация тестового сервиса сотрудников.
 * Учётные данные для чтения — только здесь.
 */
return [
    'auth' => [
        'username' => 'reader',
        'password' => 'ReadOnly2026!',
        // Альтернатива Basic Auth: заголовок Authorization: Bearer <token>
        'token' => 'emp-read-token-9f3a2c',
    ],
    'employees' => [
        [
            'id' => '001',
            'full_name' => 'Иванов Иван Иванович',
            'section' => 'Прессование',
            'position' => 'Прессовщик',
        ],
        [
            'id' => '002',
            'full_name' => 'Петров Пётр Петрович',
            'section' => 'Упаковка',
            'position' => 'Упаковщик',
        ],
        [
            'id' => '003',
            'full_name' => 'Сидоров Сергей Сергеевич',
            'section' => 'Мехобработка',
            'position' => 'Оператор',
        ],
    ],
];
