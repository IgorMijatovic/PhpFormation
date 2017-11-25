<?php

use App\Contact\ContactAction;

return [
    'contact.to' => \DI\get('mail.to'),
    ContactAction::class => \DI\object()->constructorParameter('to', \DI\get('contact.to'))
];
