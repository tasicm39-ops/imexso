<?php

return [
    'cars_xml_url' => env('IMEXSO_CARS_XML_URL', 'https://www.imexso.com/data/cars.xml'),
    'vendu_xml_url' => env('IMEXSO_VENDU_XML_URL', 'https://www.imexso.com/data/vendu.xml'),

    'vendu_create_missing_cars' => env('IMEXSO_VENDU_CREATE_MISSING', true),

    'schedule_imports' => env('IMEXSO_SCHEDULE_IMPORTS', true),
    'schedule_stock_at' => env('IMEXSO_SCHEDULE_STOCK_AT', '02:00'),
    'schedule_vendu_at' => env('IMEXSO_SCHEDULE_VENDU_AT', '02:30'),

    'archive_photo_url_template' => env(
        'IMEXSO_ARCHIVE_PHOTO_URL',
        'https://photos.imexso.com/photos_vehicules/{id_produit}-1.jpg',
    ),
];
